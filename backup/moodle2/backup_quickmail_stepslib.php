<?php

class backup_quickmail_block_structure_step extends backup_block_structure_step {
    protected function define_structure() {
        global $DB;

        $params = array('courseid' => $this->get_courseid());
        $context = context_course::instance( $params['courseid']);
        //LOGS
        $quickmail_logs = $DB->get_records('block_quickmail_log', $params);
        $include_history = $this->get_setting_value('include_quickmail_log');

        // QM BLOCK CONFIG BACKUP
        // attempt to create block settings step for quickmail, so people can restore their quickmail settings 

        // WHY IS CONFIGS TABLE SET TO COURSES WITH AN S ID????????
        $paramsTwo = array('coursesid' => $this->get_courseid());
        $quickmail_block_level_settings = $DB->get_records('block_quickmail_config', $paramsTwo);
        $include_config = $this->get_setting_value('include_quickmail_config');

        //LOGS
        $backup_logs_and_settings = new backup_nested_element('emaillogs', array('courseid'), null);

        $log = new backup_nested_element('log', array('id'), array(
            'userid', 'courseid', 'alternateid', 'mailto', 'subject',
            'message', 'attachment', 'format', 'time','failuserids','additional_emails'
        ));

        // courseid name value
        $quickmail_settings = new backup_nested_element('block_level_setting', array('id'), array(
            'courseid', 'name', 'value'
        ));


        $backup_logs_and_settings->add_child($log);

        $backup_logs_and_settings->add_child($quickmail_settings);

        $backup_logs_and_settings->set_source_array(array((object)$params));

        if (!empty($quickmail_logs) and $include_history) {
            $log->set_source_sql(
                'SELECT * FROM {block_quickmail_log}
                WHERE courseid = ?', array(array('sqlparam' => $this->get_courseid()))
            );
        }

        if (!empty($quickmail_block_level_settings) and $include_config) {
            $quickmail_settings->set_source_sql(
                'SELECT * FROM {block_quickmail_config}
                WHERE coursesid = ?', array(array('sqlparam' => $this->get_courseid()))
            );
        }

        $log->annotate_ids('user', 'userid');
        //$quickmail_settings->annotate_ids('setting');

        $log->annotate_files('block_quickmail', 'log', 'id', $context->id);
        $log->annotate_files('block_quickmail', 'attachment_log', 'id', $context->id);
        // $quickmail_settings->annotate_files('block_quickmail', 'settings', 'courseid', $context->id);

        return $this->prepare_block_structure($backup_logs_and_settings);
    }
}
