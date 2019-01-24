<?php

class backup_quickmail_block_structure_step extends backup_block_structure_step {
    protected function define_structure() {
        global $DB;

        $params = array('course_id' => $this->get_courseid());
        $context = context_course::instance( $params['course_id']);
        //LOGS
        $quickmail_logs = $DB->get_records('block_quickmail_messages', $params);
        $include_history = $this->get_setting_value('include_quickmail_log');

        // QM BLOCK CONFIG BACKUP
        // attempt to create block settings step for quickmail, so people can restore their quickmail settings 

        $paramsTwo = array('coursesid' => $this->get_courseid());
        $quickmail_block_level_settings = $DB->get_records('block_quickmail_config', $paramsTwo);
        $include_config = $this->get_setting_value('include_quickmail_config');

        //LOGS
        $backup_logs_and_settings = new backup_nested_element('emaillogs', array('course_id'), null);

        $log = new backup_nested_element('log', array('id'), array(
            'course_id', 'user_id', 'message_type', 'notification_id', 'alternate_email_id',
            'signature_id', 'subject', 'body', 'editor_format', 'sent_at', 'to_send_at',
            'is_draft', 'send_reciept', 'send_to_mentors', 'is_sending', 'no_reply',
            'usermodified', 'timecreated', 'timemodified', 'timedeleted'
        ));

        // courseid name value
        $quickmail_settings = new backup_nested_element('block_level_setting', array('id'), array(
            'coursesid', 'name', 'value'
        ));


        $backup_logs_and_settings->add_child($log);

        $backup_logs_and_settings->add_child($quickmail_settings);

        $backup_logs_and_settings->set_source_array(array((object)$params));

        if (!empty($quickmail_logs) and $include_history) {
            $log->set_source_sql(
                'SELECT * FROM {block_quickmail_messages}
                WHERE course_id = ?', array(array('sqlparam' => $this->get_courseid()))
            );
        }

        if (!empty($quickmail_block_level_settings) and $include_config) {
            $quickmail_settings->set_source_sql(
                'SELECT * FROM {block_quickmail_config}
                WHERE coursesid = ?', array(array('sqlparam' => $this->get_courseid()))
            );
        }

        $log->annotate_ids('user', 'user_id');
        // $log->annotate_files('block_quickmail', 'log', 'id', $context->id);
        // $log->annotate_files('block_quickmail', 'attachment_log', 'id', $context->id);
        // $quickmail_settings->annotate_files('block_quickmail', 'settings', 'courseid', $context->id);

        return $this->prepare_block_structure($backup_logs_and_settings);
    }
}
