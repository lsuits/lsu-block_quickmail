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

require_once $CFG->dirroot . '/blocks/quickmail/backup/moodle2/backup_quickmail_stepslib.php';

class backup_quickmail_block_task extends backup_block_task {
    protected function define_my_settings() {
        $include_history = new backup_generic_setting('include_quickmail_log', base_setting::IS_BOOLEAN, FALSE);
        $include_history->get_ui()->set_label(get_string('backup_history', 'block_quickmail'));
        $this->add_setting($include_history);

        $this->plan->get_setting('users')->add_dependency($include_history);
        $this->plan->get_setting('blocks')->add_dependency($include_history);

        $include_config_settings = new backup_generic_setting('include_quickmail_config', base_setting::IS_BOOLEAN, true);
        $include_config_settings->get_ui()->set_label(get_string('backup_block_configuration', 'block_quickmail'));
        $this->add_setting($include_config_settings);

        $this->plan->get_setting('blocks')->add_dependency($include_config_settings);
    }

    protected function define_my_steps() {
        // TODO: additional steps for drafts and alternate emails
        $this->add_step(new backup_quickmail_block_structure_step('quickmail_structure', 'emaillogs_and_block_configuration.xml'));
    }

    public function get_fileareas() {
        return array();
    }

    public function get_configdata_encoded_attributes() {
        return array();
    }

    static public function encode_content_links($content) {
        // TODO: perhaps needing this when moving away from email zip attaches
        return $content;
    }
}
