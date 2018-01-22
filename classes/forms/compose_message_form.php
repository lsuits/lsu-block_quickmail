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
 * @copyright  2008-2017 Louisiana State University
 * @copyright  2008-2017 Adam Zapletal, Chad Mazilly, Philip Cali, Robert Russo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_quickmail\forms;

require_once $CFG->libdir . '/formslib.php';

use block_quickmail_plugin;

class compose_message_form extends \moodleform {

    public $context;
    public $user;
    public $course;
    public $user_alternate_email_array;
    public $user_signature_array;
    public $course_config_array;
    public $draft_message;

    /*
     * Moodle form definition
     */
    public function definition() {

        $mform =& $this->_form;

        $this->context = $this->_customdata['context'];
        $this->user = $this->_customdata['user'];
        $this->course = $this->_customdata['course'];
        $this->user_alternate_email_array = $this->_customdata['user_alternate_email_array'];
        $this->user_signature_array = $this->_customdata['user_signature_array'];
        $this->course_config_array = $this->_customdata['course_config_array'];
        $this->draft_message = $this->_customdata['draft_message'];

        ////////////////////////////////////////////////////////////
        ///  from / alternate email (select)
        ////////////////////////////////////////////////////////////
        if ($this->should_show_alternate_email_selection()) {
            $mform->addElement(
                'select', 
                'alternate_email_id', 
                block_quickmail_plugin::_s('from'), 
                $this->user_alternate_email_array
            );
            $mform->addHelpButton(
                'alternate_email_id', 
                'from', 
                'block_quickmail'
            );

            // inject default if draft mesage
            if ($this->is_draft_message()) {
                $mform->setDefault(
                    'alternate_email_id', 
                    $this->draft_message->get('alternate_email_id')
                );
            }
        } else {
            $mform->addElement(
                'static', 
                'from_email_text', 
                block_quickmail_plugin::_s('from'), 
                $this->user_alternate_email_array[0]
            );
            $mform->addHelpButton(
                'from_email_text', 
                'from', 
                'block_quickmail'
            );
            
            $mform->addElement(
                'hidden', 
                'alternate_email_id', 
                0
            );
            $mform->setType(
                'alternate_email_id', 
                PARAM_INT
            );
        }

        ////////////////////////////////////////////////////////////
        ///  mailto_ids (text)
        ////////////////////////////////////////////////////////////
        $mform->addElement(
            'hidden', 
            'mailto_ids', 
            ''
        );
        $mform->setType(
            'mailto_ids', 
            PARAM_TEXT
        );

        // inject default if draft mesage
        $mform->setDefault(
            'mailto_ids', 
            $this->is_draft_message() 
                ? implode(',', $this->draft_message->get_message_recipients(true))
                : '123,684,116,677,264,744,'   // <--------------- this is for testing!!!!
        );

        ////////////////////////////////////////////////////////////
        ///  subject (text)
        ////////////////////////////////////////////////////////////
        $mform->addElement(
            'text', 
            'subject', 
            block_quickmail_plugin::_s('subject')
        );
        $mform->setType(
            'subject', 
            PARAM_TEXT
        );
        
        // inject default if draft mesage
        if ($this->is_draft_message()) {
            $mform->setDefault(
                'subject', 
                $this->draft_message->get('subject')
            );
        }
        
        ////////////////////////////////////////////////////////////
        ///  additional_emails (text)
        ////////////////////////////////////////////////////////////
        if ($this->should_show_additional_email_input()) {
            $mform->addElement(
                'text', 
                'additional_emails', 
                block_quickmail_plugin::_s('additional_emails')
            );
            $mform->setType(
                'additional_emails', 
                PARAM_TEXT
            );
            // $mform->addRule('additional_emails', 'One or more email addresses is invalid', 'callback', 'block_quickmail_mycallback', 'client');
            $mform->addHelpButton(
                'additional_emails', 
                'additional_emails', 
                'block_quickmail'
            );

            // inject default if draft mesage
            if ($this->is_draft_message()) {
                $mform->setDefault(
                    'additional_emails', 
                    implode(', ', $this->draft_message->get_additional_emails(true))
                );
            }
        }

        ////////////////////////////////////////////////////////////
        ///  message_editor (textarea)
        ////////////////////////////////////////////////////////////
        
        // inject default if draft mesage
        $default_text = $this->is_draft_message() 
            ? $this->draft_message->get('body') 
            : '';
        
        $mform->addElement(
            'editor', 
            'message_editor',  
            block_quickmail_plugin::_s('body'), 
            'bdfsdgsdg', 
            $this->get_editor_options()
        )->setValue([
            'text' => $default_text
        ]);
        $mform->setType(
            'message_editor', 
            PARAM_RAW
        );

        ////////////////////////////////////////////////////////////
        ///  attachments (filemanager)
        ////////////////////////////////////////////////////////////

        $mform->addElement(
            'filemanager', 
            'attachments', 
            block_quickmail_plugin::_s('attachment'), 
            null,
            block_quickmail_config::get_filemanager_options()
        );
        // $mform->setDefault('attachments', file_get_submitted_draft_itemid('attachments'));

        ////////////////////////////////////////////////////////////
        ///  signatures (select)
        ////////////////////////////////////////////////////////////
        if ($this->should_show_signature_selection()) {
            $mform->addElement(
                'select', 
                'signature_id', 
                block_quickmail_plugin::_s('signature'), 
                $this->get_user_signature_options()
            );

            // inject default if draft mesage
            if ($this->is_draft_message()) {
                $mform->setDefault(
                    'signature_id', 
                    $this->draft_message->get('signature_id')
                );
            }
        } else {
            $mform->addElement(
                'static', 
                'add_signature_text', 
                block_quickmail_plugin::_s('sig'), 
                block_quickmail_plugin::_s('no_signatures_create', '<a href="' . $this->get_create_signature_url() . '" id="create-signature-btn">' . block_quickmail_plugin::_s('create_one_now') . '</a>')
            );
            $mform->addElement(
                'hidden', 
                'signature_id', 
                0
            );
            $mform->setType(
                'signature_id', 
                PARAM_INT
            );
        }

        ////////////////////////////////////////////////////////////
        ///  output_channel (select)
        ////////////////////////////////////////////////////////////
        if ($this->should_show_output_channel_selection()) {
            $mform->addElement(
                'select', 
                'output_channel', 
                block_quickmail_plugin::_s('select_output_channel'), 
                $this->get_output_channel_options()
            );
            
            // inject default if draft mesage
            $mform->setDefault(
                'output_channel', 
                $this->is_draft_message()
                    ? $this->draft_message->get('output_channel')
                    : $this->course_config_array['default_output_channel']
            );
        } else {
            $mform->addElement(
                'hidden', 
                'output_channel'
            );
            $mform->setDefault(
                'output_channel', 
                $this->course_config_array['default_output_channel']
            );
        }

        ////////////////////////////////////////////////////////////
        ///  to_send_at (date/time)
        ////////////////////////////////////////////////////////////
        $mform->addElement(
            'date_time_selector', 
            'to_send_at', 
            block_quickmail_plugin::_s('send_at'),
            $this->get_to_send_at_options()
        );

        // inject default if draft mesage AND time to send is in the future
        if ($this->should_set_default_time()) {
            $mform->setDefault(
                'to_send_at',
                $this->get_draft_default_send_time()
            );
        }

        ////////////////////////////////////////////////////////////
        ///  receipt (radio) - receive a copy or not?
        ////////////////////////////////////////////////////////////
        $receipt_options = [
            $mform->createElement('radio', 'receipt', '', get_string('yes'), 1),
            $mform->createElement('radio', 'receipt', '', get_string('no'), 0)
        ];

        $mform->addGroup(
            $receipt_options, 
            'receipt_action', 
            block_quickmail_plugin::_s('receipt'), 
            [' '], 
            false
        );
        $mform->addHelpButton(
            'receipt_action', 
            'receipt', 
            'block_quickmail'
        );

        $mform->setDefault(
            'receipt', 
            $this->is_draft_message() 
            ? $this->draft_message->get('send_receipt') // inject default if draft mesage
            : ! empty($this->course_config_array['receipt']) // otherwise, go with this course's config
        );

        ////////////////////////////////////////////////////////////
        ///  buttons
        ////////////////////////////////////////////////////////////
        $buttons = [
            $mform->createElement('submit', 'send', block_quickmail_plugin::_s('send_message')),
            $mform->createElement('submit', 'save', block_quickmail_plugin::_s('save_draft')),
            $mform->createElement('cancel', 'cancel', block_quickmail_plugin::_s('cancel'))
        ];
        
        $mform->addGroup($buttons, 'actions', '&nbsp;', [' '], false);
    }

    /*
     * Moodle form validation
     */
    public function validation($data, $files) {
        $errors = [];

        // additional_emails - make sure each is valid
        $cleansed_additional_emails = preg_replace('/\s+/', '', $data['additional_emails']);
        
        if ( ! empty($cleansed_additional_emails) && count(array_filter(explode(',', $cleansed_additional_emails), function($email) {
            return ! filter_var($email, FILTER_VALIDATE_EMAIL);
        }))) {
            $errors['additional_emails'] = 'Some of the additional emails you entered were invalid.';
        }

        return $errors;
    }

    /**
     * Reports whether or not this is a draft message
     * 
     * @return bool
     */
    private function is_draft_message() {
        return ! empty($this->draft_message);
    }

    /**
     * Returns an array of text editor master options
     * 
     * @return array
     */
    private function get_editor_options() {
        return block_quickmail_config::get_editor_options($this->context);
    }

    /**
     * Reports whether or not this form should display the "additional emails" input
     * 
     * @return bool
     */
    private function should_show_additional_email_input() {
        return (bool) $this->course_config_array['additionalemail'];
    }

    /**
     * Reports whether or not this form should display the alternate email selection input
     * 
     * @return bool
     */
    private function should_show_alternate_email_selection() {
        return count($this->user_alternate_email_array) > 1;
    }

    /**
     * Reports whether or not this form should display the signature selection input
     * 
     * @return bool
     */
    private function should_show_signature_selection() {
        return count($this->user_signature_array);
    }

    /**
     * Reports whether or not this form should display the output channel selection input
     * 
     * @return bool
     */
    private function should_show_output_channel_selection() {
        return (bool) $this->course_config_array['output_channels_available'] == 'all';
    }

    /**
     * Returns the current user's signatures for selection, plus a "none" option
     * 
     * @return array
     */
    private function get_user_signature_options() {
        return [0 => 'None'] + $this->user_signature_array;
    }

    /**
     * Returns the options for output channel selection
     * 
     * @return array
     */
    private function get_output_channel_options() {
        return [
            'message' => block_quickmail_plugin::_s('output_channel_message'),
            'email' => block_quickmail_plugin::_s('output_channel_email')
        ];
    }

    /**
     * Returns the options for the "send at" time selection
     * 
     * @return array
     */
    private function get_to_send_at_options() {
        $current_year = date("Y");

        if ( ! $this->is_draft_message()) {
            $is_optional = true;
        } else {
            $is_optional = ! $this->draft_message->get_to_send_in_future();
        }

        return [
            'startyear' => $current_year,
            'stopyear' => $current_year + 1,
            'timezone' => 99,
            'step' => 15,
            'optional' => $is_optional
        ];
    }

    /**
     * Returns a URL for signature creation
     * @return [type] [description]
     */
    private function get_create_signature_url() {
        return '/blocks/quickmail/signature.php?courseid=' . $this->course->id;
    }

    /**
     * Report whether or not a default time should be set
     * 
     * @return bool
     */
    private function should_set_default_time() {
        if ( ! $this->is_draft_message()) {
            return false;
        }

        return $this->draft_message->get_to_send_in_future();
    }

    /**
     * Returns the default timestamp for this message
     * 
     * @return int
     */
    private function get_draft_default_send_time() {
        $to_send_at = $this->draft_message->get('to_send_at');

        return make_timestamp(
            date("Y", $to_send_at), 
            date("n", $to_send_at), 
            date("j", $to_send_at), 
            date("G", $to_send_at), 
            date("i", $to_send_at), 
            date("s", $to_send_at)
        );
    }

}
