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
 * @copyright  2008 onwards Louisiana State University
 * @copyright  2008 onwards Chad Mazilly, Robert Russo, Jason Peak, Dave Elliott, Adam Zapletal, Philip Cali
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

////////////////////////////////////////////////////
///
///  COMPOSE FORM SUBMISSION HELPERS
///  
///  needs:
///   # has_general_helpers
/// 
////////////////////////////////////////////////////

trait submits_compose_message_form {

    public function get_compose_message_form_submission(array $recipients = [], $message_type = 'email', array $override_params = [])
    {
        $params = $this->get_compose_message_form_submission_params($override_params);

        list($included_entity_ids, $excluded_entity_ids) = $this->get_recipients_array($recipients);

        $form_data = (object)[];

        $form_data->from_email_id = $params['from_email_id']; // default: '0' (user email), '-1' (system no reply), else alt id
        $form_data->included_entity_ids = $included_entity_ids;
        $form_data->excluded_entity_ids = $excluded_entity_ids;
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
        $form_data->mentor_copy = $params['mentor_copy']; // default: '0'
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

    private function get_recipients_array($recipients)
    {
        $included_entity_ids = [];
        $excluded_entity_ids = [];

        foreach (['included', 'excluded'] as $inclusion_type) {
            if (array_key_exists($inclusion_type, $recipients)) {
                foreach (['role', 'group', 'user'] as $recipient_type) {
                    if (array_key_exists($recipient_type, $recipients[$inclusion_type])) {
                        foreach ($recipients[$inclusion_type][$recipient_type] as $id) {
                            $container_name = $inclusion_type . '_entity_ids';

                            $$container_name[] = $recipient_type . '_' . $id;
                        }
                    }
                }
            }
        }

        return [$included_entity_ids, $excluded_entity_ids];
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
        $params['mentor_copy'] = array_key_exists('mentor_copy', $override_params) ? $override_params['mentor_copy'] : '0';

        return $params;
    }

}