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

    public function definition() {

        $mform =& $this->_form;

        // set the user
        $this->user = $this->_customdata['user'];

        // set the course
        $this->course = $this->_customdata['course'];
        
        // set the context
        $this->context = $this->_customdata['context'];

        // set this user's alternate emails
        $this->user_alternate_email_array = $this->_customdata['user_alternate_email_array'];

        // set this user's signatures
        $this->user_signature_array = $this->_customdata['user_signature_array'];

        // set this course's config
        $this->course_config_array = $this->_customdata['course_config_array'];

        // set the draft_message
        $this->draft_message = $this->_customdata['draft_message'];

        ////////////////////////////////////////////////////////////
        ///  from / alternate email (select)
        ////////////////////////////////////////////////////////////
        if ($this->should_show_alternate_email_selection()) {
            $mform->addElement('select', 'alternate_email_id', $this->get_plugin_string('from'), $this->user_alternate_email_array);
            $mform->addHelpButton('alternate_email_id', 'from', 'block_quickmail');

            // inject default if draft mesage
            if ($this->is_draft_message()) {
                $mform->setDefault('alternate_email_id', $this->draft_message->get('alternate_email_id'));
            }
        } else {
            $mform->addElement('static', 'from_email_text', $this->get_plugin_string('from'), $this->user_alternate_email_array[0]);
            $mform->addHelpButton('from_email_text', 'from', 'block_quickmail');
            
            $mform->addElement('hidden', 'alternate_email_id', 0);
            $mform->setType('alternate_email_id', PARAM_INT);
        }

        ////////////////////////////////////////////////////////////
        ///  mailto_ids (text)
        ////////////////////////////////////////////////////////////
        $mform->addElement('hidden', 'mailto_ids', '');
        $mform->setType('mailto_ids', PARAM_TEXT);

        // inject default if draft mesage
        if ($this->is_draft_message()) {
            $mform->setDefault('mailto_ids', implode(',', $this->draft_message->get_message_recipients(true)));
        } else {
            $mform->setDefault('mailto_ids', '123,684,116,677,264,744,');  // <--------------- this is for testing!!!!
        }

        ////////////////////////////////////////////////////////////
        ///  subject (text)
        ////////////////////////////////////////////////////////////
        $mform->addElement('text', 'subject', $this->get_plugin_string('subject'));
        $mform->setType('subject', PARAM_TEXT);
        
        // inject default if draft mesage
        if ($this->is_draft_message()) {
            $mform->setDefault('subject', $this->draft_message->get('subject'));
        }
        
        ////////////////////////////////////////////////////////////
        ///  additional_emails (text)
        ////////////////////////////////////////////////////////////
        if ($this->should_show_additional_email_input()) {
            $mform->addElement('text', 'additional_emails', $this->get_plugin_string('additional_emails'));
            $mform->setType('additional_emails', PARAM_TEXT);
            // $mform->addRule('additional_emails', 'One or more email addresses is invalid', 'callback', 'block_quickmail_mycallback', 'client');
            $mform->addHelpButton('additional_emails', 'additional_emails', 'block_quickmail');

            // inject default if draft mesage
            if ($this->is_draft_message()) {
                $mform->setDefault('additional_emails', implode(', ', $this->draft_message->get_additional_emails(true)));
            }
        }

        ////////////////////////////////////////////////////////////
        ///  message_editor (textarea)
        ////////////////////////////////////////////////////////////
        
        // inject default if draft mesage
        $default_text = $this->is_draft_message() ? $this->draft_message->get('body') : '';
        
        $mform->addElement('editor', 'message_editor',  $this->get_plugin_string('body'), 'bdfsdgsdg', $this->get_editor_options())
            ->setValue(['text' => $default_text]);
        $mform->setType('message_editor', PARAM_RAW);

        ////////////////////////////////////////////////////////////
        ///  signatures (select)
        ////////////////////////////////////////////////////////////
        if ($this->should_show_signature_selection()) {
            $mform->addElement('select', 'signature_id', $this->get_plugin_string('signature'), $this->get_user_signature_options());

            // inject default if draft mesage
            if ($this->is_draft_message()) {
                $mform->setDefault('signature_id', $this->draft_message->get('signature_id'));
            }
        } else {
            $mform->addElement('static', 'add_signature_text', $this->get_plugin_string('sig'), $this->get_plugin_string('no_signatures_create', '<a href="' . $this->get_create_signature_url() . '" id="create-signature-btn">' . $this->get_plugin_string('create_one_now') . '</a>'));
            $mform->addElement('hidden', 'signature_id', 0);
            $mform->setType('signature_id', PARAM_INT);
        }

        ////////////////////////////////////////////////////////////
        ///  output_channel (select)
        ////////////////////////////////////////////////////////////
        if ($this->should_show_output_channel_selection()) {
            $mform->addElement('select', 'output_channel', $this->get_plugin_string('select_output_channel'), $this->get_output_channel_options());
            
            // inject default if draft mesage
            if ($this->is_draft_message()) {
                $mform->setDefault('output_channel', $this->draft_message->get('output_channel'));
            } else {
                $mform->setDefault('output_channel', $this->course_config_array['default_output_channel']);
            }
        } else {
            $mform->addElement('hidden', 'output_channel');
            $mform->setDefault('output_channel', $this->course_config_array['default_output_channel']);
        }

        ////////////////////////////////////////////////////////////
        ///  receipt (radio) - receive a copy or not?
        ////////////////////////////////////////////////////////////
        $receipt_options = array(
            $mform->createElement('radio', 'receipt', '', get_string('yes'), 1),
            $mform->createElement('radio', 'receipt', '', get_string('no'), 0)
        );

        $mform->addGroup($receipt_options, 'receipt_action', $this->get_plugin_string('receipt'), array(' '), false);
        $mform->addHelpButton('receipt_action', 'receipt', 'block_quickmail');

        if ($this->is_draft_message()) {
            // inject default if draft mesage
            $mform->setDefault('receipt', $this->draft_message->get('send_receipt'));
        } else {
            // otherwise, go with this course's config
            $mform->setDefault('receipt', ! empty($this->course_config_array['receipt']));
        }

        ////////////////////////////////////////////////////////////
        ///  buttons
        ////////////////////////////////////////////////////////////
        $buttons = [
            $mform->createElement('submit', 'send', $this->get_plugin_string('send_message')),
            $mform->createElement('submit', 'save', $this->get_plugin_string('save_draft')),
            $mform->createElement('cancel', 'cancel', $this->get_plugin_string('cancel'))
        ];
        
        $mform->addGroup($buttons, 'actions', '&nbsp;', array(' '), false);
    }

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
        return block_quickmail_plugin::get_editor_options($this->context);
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

    private function get_create_signature_url() {
        return '/blocks/quickmail/signature.php?courseid=' . $this->course->id;
    }

    public function get_plugin_string($key, $a = null) {
        return block_quickmail_plugin::_s($key, $a);
    }

    private function get_plugin_config($key) {
        return block_quickmail_plugin::_c($key, $this->course->id);
    }

}
