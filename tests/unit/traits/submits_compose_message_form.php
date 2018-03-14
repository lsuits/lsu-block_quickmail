<?php

////////////////////////////////////////////////////
///
///  COMPOSE FORM SUBMISSION HELPERS
///  
///  needs:
///   # has_general_helpers
/// 
////////////////////////////////////////////////////

trait submits_compose_message_form {

    // @TODO : convert additional_emails override to an array of emails
    public function get_compose_message_form_submission(array $recipients = [], $message_type = 'email', array $override_params = [])
    {
        $params = $this->get_compose_message_form_submission_params($override_params);

        list($included_ids, $excluded_ids) = $this->get_recipients_array($recipients);

        $form_data = (object)[];

        $form_data->from_email_id = $params['from_email_id']; // default: '0' (user email), '-1' (system no reply), else alt id
        $form_data->included_ids = $included_ids;
        $form_data->excluded_ids = $excluded_ids;
        $form_data->subject = $params['subject']; // default: 'this is the subject'
        $form_data->additional_emails = $params['additional_emails']; // default: ''
        $form_data->message_editor = [
            'text' => $params['body'], // default: 'this is a very important message body'
            'format' => '1',
            'itemid' => 881830772
        ];
        $form_data->attachments = 0;
        $form_data->signature_id = $params['signature_id']; // default: '0'
        $form_data->message_type = $message_type;
        $form_data->to_send_at = $params['to_send_at']; // default: 0
        $form_data->receipt = $params['receipt']; // default: '0'
        $form_data->send = 'Send Message';

        return $form_data;
    }
    
    // recipients
        // included
            // roles
            // groups
            // users
        // excluded
            // roles
            // groups
            // users

    // @TODO : convert additional_emails override to an array of emails
    private function get_recipients_array($recipients)
    {
        $included_ids = [];
        $excluded_ids = [];

        foreach (['included', 'excluded'] as $inclusion_type) {
            if (array_key_exists($inclusion_type, $recipients)) {
                foreach (['role', 'group', 'user'] as $recipient_type) {
                    if (array_key_exists($recipient_type, $recipients[$inclusion_type])) {
                        foreach ($recipients[$inclusion_type][$recipient_type] as $id) {
                            $container_name = $inclusion_type . '_ids';

                            $$container_name[] = $recipient_type . '_' . $id;
                        }
                    }
                }
            }
        }

        return [$included_ids, $excluded_ids];
    }

    public function get_compose_message_form_submission_params(array $override_params)
    {
        $params = [];

        $params['from_email_id'] = array_key_exists('from_email_id', $override_params) ? $override_params['from_email_id'] : '0';
        $params['additional_emails'] = array_key_exists('additional_emails', $override_params) ? $override_params['additional_emails'] : '';
        $params['subject'] = array_key_exists('subject', $override_params) ? $override_params['subject'] : 'this is the subject';
        $params['body'] = array_key_exists('body', $override_params) ? $override_params['body'] : 'this is a very important message body';
        $params['signature_id'] = array_key_exists('signature_id', $override_params) ? $override_params['signature_id'] : '0';
        $params['to_send_at'] = array_key_exists('to_send_at', $override_params) ? $override_params['to_send_at'] : 0;
        $params['receipt'] = array_key_exists('receipt', $override_params) ? $override_params['receipt'] : '0';

        return $params;
    }

}