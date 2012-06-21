<?php

class backup_quickmail_block_structure_step extends backup_block_structure_step {
    protected function define_structure() {
        global $DB;

        $params = array('courseid' => $this->get_courseid());
        $context = get_context_instance(CONTEXT_COURSE, $params['courseid']);

        $quickmail_logs = $DB->get_records('block_quickmail_log', $params);
        $include_history = $this->get_setting_value('include_quickmail_log');

        $backup_logs = new backup_nested_element('emaillogs', array('courseid'), null);

        $log = new backup_nested_element('log', array('id'), array(
            'userid', 'courseid', 'alternateid', 'mailto', 'subject',
            'message', 'attachment', 'format', 'time'
        ));

        $backup_logs->add_child($log);

        $backup_logs->set_source_array(array((object)$params));

        if (!empty($quickmail_logs) and $include_history) {
            $log->set_source_sql(
                'SELECT * FROM {block_quickmail_log}
                WHERE courseid = ?', array(array('sqlparam' => $this->get_courseid()))
            );
        }

        $log->annotate_ids('user', 'userid');

        $log->annotate_files('block_quickmail', 'log', 'id', $context->id);
        $log->annotate_files('block_quickmail', 'attachment_log', 'id', $context->id);

        return $this->prepare_block_structure($backup_logs);
    }
}
