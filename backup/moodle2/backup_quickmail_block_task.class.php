<?php

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
