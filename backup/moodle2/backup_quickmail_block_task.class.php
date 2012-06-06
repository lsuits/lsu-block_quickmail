<?php

require_once $CFG->dirroot . '/blocks/quickmail/backup/moodle2/backup_quickmail_stepslib.php';

class backup_quickmail_block_task extends backup_block_task {
    protected function define_my_settings() {
    }

    protected function define_my_steps() {
        $this->add_step(new backup_quickmail_block_structure_step('quickmail_structure', 'emaillogs.xml'));
    }

    public function get_fileareas() {
        return array('message');
    }

    public function get_configdata_encoded_attributes() {
        return array();
    }

    static public function encode_content_links($content) {
        return $content;
    }
}
