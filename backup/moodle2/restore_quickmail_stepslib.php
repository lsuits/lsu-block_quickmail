<?php

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

            $current = context_course::instance($this->get_courseid());

            $params = array(
                'backupid' => $this->get_restoreid(),
                'itemname' => 'context',
                'newitemid' => $current->id
            );

            $id = $DB->get_record('backup_ids_temp', $params)->itemid;

            foreach ($data->emaillogs['log'] as $log) {
                $this->process_log($log, $id, $current);
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
        $log->course_id = $this->get_courseid();
        $log->user_id = $this->get_mappingid('user', $log->user_id);
        $log->time = $this->apply_date_offset($log->time);

        // TODO: correctly convert alternate ids
        $log->alternateid = null;

        $newid = $DB->insert_record('block_quickmail_messages', $log);

        $this->set_mapping('log', $oldid, $newid);

    }
}
