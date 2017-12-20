<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    block_quickmail
 * @copyright  2008-2017 Louisiana State University
 * @copyright  2008-2017 Adam Zapletal, Chad Mazilly, Philip Cali, Robert Russo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class restore_quickmail_log_structure_step extends restore_structure_step {
    protected function define_structure() {
        $paths = array();

        $paths[] = new restore_path_element('block', '/block', true);
        $paths[] = new restore_path_element('log', '/block/emaillogs/log');

        $paths[] = new restore_path_element('block_level_setting', '/block/emaillogs/block_level_setting');
        return $paths;
    }

    protected function process_block($data) {
        global $DB;

        $data = (object) $data;

        $restore = $this->get_setting_value('restore_quickmail_history');
        $overwrite = $this->get_setting_value('overwrite_quickmail_history');

        // Delete current history, if any
        if ($overwrite) {
            $params = array('courseid' => $this->get_courseid());
            $DB->delete_records('block_quickmail_log', $params);
        }

        if ($restore and isset($data->emaillogs['log'])) {
            global $DB;

            $current_context = context_course::instance($this->get_courseid());

            $params = array(
                'backupid' => $this->get_restoreid(),
                'itemname' => 'context',
                'newitemid' => $current_context->id
            );

            $id = $DB->get_record('backup_ids_temp', $params)->itemid;

            foreach ($data->emaillogs['log'] as $log) {
                $this->process_log($log, $id, $current_context);
            }
        }

        if(isset($data->emaillogs['block_level_setting'])){
            foreach ($data->emaillogs['block_level_setting'] as $block_level_setting) {
                $this->process_block_level_setting($block_level_setting, $this->get_courseid());
            }
        }
    }


    protected function process_block_level_setting($block_level_setting, $courseid) {
        global $DB;
        if($block_level_setting['name']){
                //quickmail::default_config($courseid);
                $config = new stdClass;
                $config->coursesid = $courseid;
                $config->name = $block_level_setting['name'];
                $config->value = $block_level_setting['value'];
                $DB->insert_record('block_quickmail_config', $config);
        }
    }

    protected function process_log($log, $oldctx, $context) {
        global $DB;

        $log = (object) $log;
        $oldid = $log->id;

        $mailedusers = explode(',', $log->mailto);
        $validusers = array();

        foreach ($mailedusers as $userid) {
            $validusers[] = $this->get_mappingid('user', $userid);
        }

        $log->courseid = $this->get_courseid();
        $log->userid = $this->get_mappingid('user', $log->userid);
        $log->mailto = implode(',', $validusers);
        $log->time = $this->apply_date_offset($log->time);

        // TODO: correctly convert alternate ids
        $log->alternateid = null;

        $newid = $DB->insert_record('block_quickmail_log', $log);

        $this->set_mapping('log', $oldid, $newid);

        foreach (array('log', 'attachment_log') as $filearea) {
            restore_dbops::send_files_to_pool(
                $this->get_basepath(), $this->get_restoreid(),
                'block_quickmail', $filearea, $oldctx, $log->userid
            );

            $sql = 'UPDATE {files} SET
                itemid = :newid WHERE contextid = :ctxt AND itemid = :oldid';

            $params = array(
                'newid' => $newid, 'oldid' => $oldid, 'ctxt' => $context->id
            );

            $DB->execute($sql, $params);
        }
    }
}
