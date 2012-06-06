<?php

require_once $CFG->dirroot . '/blocks/quickmail/backup/moodle2/restore_quickmail_stepslib.php';

class restore_quickmail_block_task extends restore_block_task {
    protected function define_my_settings() {
    }

    protected function define_my_steps() {
        $this->add_step(new restore_quickmail_log_structure_step('quickmail_structure', 'emaillogs.xml'));
    }

    public function get_fileareas() {
        return array('message');
    }

    public function get_configdata_encoded_attributes() {
        return array();
    }

    static public function define_decode_contents() {
        return array();
    }

    static public function define_decode_rules() {
        return array();
    }
}
