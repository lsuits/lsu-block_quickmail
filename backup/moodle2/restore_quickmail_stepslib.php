<?php

class restore_quickmail_log_structure_step extends restore_activity_structure_step {
    protected function define_structure() {
        $paths = array();

        $paths[] = new restore_path_element('block', '/block', true);
        $paths[] = new restore_path_element('log', '/block/emaillogs/log');

        return $paths;
    }

    protected function process_block($data) {
        $data = (object) $data;

        if (isset($data->emaillogs['log'])) {
            foreach ($data->emaillogs['log'] as $log) {
                $this->process_log($log);
            }
        }
    }

    protected function process_log($log) {
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
    }
}
