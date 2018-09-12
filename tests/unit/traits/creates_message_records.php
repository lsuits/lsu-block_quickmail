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
///  MESSAGE RECORD CREATION HELPERS
/// 
////////////////////////////////////////////////////

trait creates_message_records {

    // additional_data (recipient_users)
    public function create_compose_message($course, $sending_user, array $additional_data = [], array $override_params = [])
    {
        $params = $this->get_create_course_message_params($override_params);

        $data = new stdClass();
        $data->course_id = $course->id;
        $data->user_id = $sending_user->id;
        $data->message_type = $params['message_type'];
        $data->alternate_email_id = $params['alternate_email_id'];
        $data->signature_id = $params['signature_id'];
        $data->subject = $params['subject'];
        $data->body = $params['body'];
        $data->editor_format = $params['editor_format'];
        $data->sent_at = $params['sent_at'];
        $data->to_send_at = $params['to_send_at'];
        $data->is_draft = $params['is_draft'];
        $data->send_receipt = $params['send_receipt'];
        $data->is_sending = $params['is_sending'];
        $data->no_reply = $params['no_reply'];

        $message = new block_quickmail\persistents\message(0, $data);
        $message->create();

        // recipient creation
        if (array_key_exists('recipient_users', $additional_data)) {
            // make each of these user a recipient
            foreach ($additional_data['recipient_users'] as $user) {
                $recipient = $this->create_message_recipient_from_user($message, $user);
            }
        } else {
            // create 10 fake user recipients
        }

        // alt_emails?
        // additional_emails?
        // signatures?
        
        return $message;
    }

    public function get_create_course_message_params(array $override_params)
    {
        $params = [];

        $params['message_type'] = array_key_exists('message_type', $override_params) ? $override_params['message_type'] : 'email';
        $params['alternate_email_id'] = array_key_exists('alternate_email_id', $override_params) ? $override_params['alternate_email_id'] : '0';
        $params['signature_id'] = array_key_exists('signature_id', $override_params) ? $override_params['signature_id'] : '0';
        $params['subject'] = array_key_exists('subject', $override_params) ? $override_params['subject'] : 'this is the subject';
        $params['body'] = array_key_exists('body', $override_params) ? $override_params['body'] : 'this is a very important message body';
        $params['editor_format'] = array_key_exists('editor_format', $override_params) ? $override_params['editor_format'] : 1;
        $params['sent_at'] = array_key_exists('sent_at', $override_params) ? $override_params['sent_at'] : 0;
        $params['to_send_at'] = array_key_exists('to_send_at', $override_params) ? $override_params['to_send_at'] : 0;
        $params['is_draft'] = array_key_exists('is_draft', $override_params) ? $override_params['is_draft'] : false;
        $params['send_receipt'] = array_key_exists('send_receipt', $override_params) ? $override_params['send_receipt'] : '0';
        $params['is_sending'] = array_key_exists('is_sending', $override_params) ? $override_params['is_sending'] : false;
        $params['no_reply'] = array_key_exists('no_reply', $override_params) ? $override_params['no_reply'] : 0;

        return $params;
    }

    public function create_message_recipient_from_user($message, $user)
    {
        $recipient = block_quickmail\persistents\message_recipient::create_new([
            'message_id' => $message->get('id'),
            'user_id' => $user->id,
        ]);

        return $recipient;
    }
    
}