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

require_once $CFG->dirroot . '/blocks/quickmail/backup/moodle2/restore_quickmail_stepslib.php';

class restore_quickmail_block_task extends restore_block_task {
    public function history_exists() {
        // Weird... folder doesn't exists
        $fullpath = $this->get_taskbasepath();
        if (empty($fullpath)) {
            return false;
        }

        // Issue #45: trying to restore from a non-existent logfile
        $fullpath = rtrim($fullpath, '/') . '/emaillogs_and_block_configuration.xml';
        if (!file_exists($fullpath)) {
            return false;
        }

        return true;
    }

    protected function define_my_settings() {
        // Nothing to do
        if (!$this->history_exists()) {
            return;
        }

        $rootsettings = $this->get_info()->root_settings;

        $defaultvalue = false;
        $changeable = false;

        $is_blocks = isset($rootsettings['blocks']) && $rootsettings['blocks'];
        $is_users = isset($rootsettings['users']) && $rootsettings['users'];

        if ($is_blocks and $is_users) {
            $defaultvalue = true;
            $changeable = true;
        }

        $restore_history = new restore_generic_setting('restore_quickmail_history',
            base_setting::IS_BOOLEAN, $defaultvalue);
        $restore_history->set_ui(new backup_setting_ui_select(
            $restore_history, get_string('restore_history', 'block_quickmail'),
            array(1 => get_string('yes'), 0 => get_string('no'))
        ));

        if (!$changeable) {
            $restore_history->set_value($defaultvalue);
            $restore_history->set_status(backup_setting::LOCKED_BY_CONFIG);
            $restore_history->set_visibility(backup_setting::HIDDEN);
        }

        $this->add_setting($restore_history);
        $this->get_setting('users')->add_dependency($restore_history);
        $this->get_setting('blocks')->add_dependency($restore_history);

        $overwrite_history = new restore_course_generic_setting('overwrite_quickmail_history', base_setting::IS_BOOLEAN, false);
        $overwrite_history->set_ui(new backup_setting_ui_select(
            $overwrite_history,
            get_string('overwrite_history', 'block_quickmail'),
            array(1 => get_string('yes'), 0 => get_string('no'))
        ));

        if ($this->get_target() != backup::TARGET_CURRENT_DELETING and $this->get_target() != backup::TARGET_EXISTING_DELETING) {

            $overwrite_history->set_value(false);
            $overwrite_history->set_status(backup_setting::LOCKED_BY_CONFIG);
        }

        $this->add_setting($overwrite_history);
        $restore_history->add_dependency($overwrite_history);
    }

    protected function define_my_steps() {
        if ($this->history_exists()) {
            $this->add_step(new restore_quickmail_log_structure_step(
                'quickmail_structure', 'emaillogs_and_block_configuration.xml'
            ));
        }
    }

    public function get_fileareas() {
        return array();
    }

    public function get_configdata_encoded_attributes() {
        return array();
    }

    static public function define_decode_contents() {
        // TODO: perhaps needing this when moving away from email zip attaches
        return array();
    }

    static public function define_decode_rules() {
        return array();
    }
}
