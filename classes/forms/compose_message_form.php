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

class compose_message_form extends \moodleform {

    public $context;
    
    public $user;

    public $course;

    public $user_signature_array;

    public function definition() {

        $mform =& $this->_form;

        // set the user
        $this->user = $this->_customdata['user'];

        // set the course
        $this->course = $this->_customdata['course'];
        
        // set the context
        $this->context = $this->_customdata['context'];

        // set this user's signatures
        $this->user_signature_array = $this->_customdata['user_signature_array'];

        ////////////////////////////////////////////////////////////
        ///  subject (text)
        ////////////////////////////////////////////////////////////
        $mform->addElement('text', 'subject', $this->get_plugin_string('subject'));
        $mform->setType('subject', PARAM_TEXT);
        // $mform->addRule('subject', null, 'required', 'client'); // disabling because of draft-saving
        
        ////////////////////////////////////////////////////////////
        ///  noreply (text)
        ////////////////////////////////////////////////////////////
        $mform->addElement('static', 'noreply', $this->get_plugin_string('noreply'));
        $mform->setType('noreply', PARAM_EMAIL);
        $mform->setDefault('noreply', $this->get_noreply_email_address());
        // $mform->addRule('noreply', null, 'required', 'client');

        ////////////////////////////////////////////////////////////
        ///  additional_emails (text)
        ////////////////////////////////////////////////////////////
        if ($this->should_show_additional_email_input()) {
            $mform->addElement('text', 'additional_emails', $this->get_plugin_string('additional_emails'));
            $mform->setType('additional_emails', PARAM_TEXT);
            // $mform->addRule('additional_emails', 'One or more email addresses is invalid', 'callback', 'block_quickmail_mycallback', 'client');
            $mform->addHelpButton('additional_emails', 'additional_emails', 'block_quickmail');
        }

        ////////////////////////////////////////////////////////////
        ///  message_editor (textarea)
        ////////////////////////////////////////////////////////////
        $mform->addElement('editor', 'message_editor',  $this->get_plugin_string('body'), null, $this->get_editor_options());
        $mform->setType('message_editor', PARAM_RAW);
        // $mform->addRule('message_editor', null, 'required'); // disabling because of draft-saving

        ////////////////////////////////////////////////////////////
        ///  signatures (select)
        ////////////////////////////////////////////////////////////
        if ($this->should_show_signature_selection()) {
            $mform->addElement('select', 'signature_id', $this->get_plugin_string('signature'), $this->get_user_signature_options());
        } else {
            $mform->addElement('static', 'add_signature_text', $this->get_plugin_string('sig'), $this->get_plugin_string('no_signatures_create', '<a href="' . $this->get_create_signature_url() . '" id="create-signature-btn">' . $this->get_plugin_string('create_one_now') . '</a>'));
        }

        ////////////////////////////////////////////////////////////
        ///  receipt (radio) - receive a copy or not?
        ////////////////////////////////////////////////////////////
        if ($this->should_show_receipt_option()) {
            $receipt_options = array(
                $mform->createElement('radio', 'receipt', '', get_string('yes'), 1),
                $mform->createElement('radio', 'receipt', '', get_string('no'), 0)
            );

            $mform->addGroup($receipt_options, 'receipt_action', $this->get_plugin_string('receipt'), array(' '), false);
            $mform->addHelpButton('receipt_action', 'receipt', 'block_quickmail');
            $mform->setDefault('receipt', ! empty($this->get_plugin_config('receipt')));
        }
        
        ////////////////////////////////////////////////////////////
        ///  buttons
        ////////////////////////////////////////////////////////////
        $buttons = [
            $mform->createElement('submit', 'send', $this->get_send_button_text()),
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
     * Returns an array of text editor master options
     * 
     * @return array
     */
    private function get_editor_options() {
        return \block_quickmail_plugin::get_editor_options($this->context);
    }

    private function get_send_button_text() {
        $output_channel = block_quickmail_plugin::get_output_channel();

        return $this->get_plugin_string('send_message', ucfirst($output_channel));
    }

    private function get_noreply_email_address() {
        global $CFG;

        return $CFG->noreplyaddress;
    }

    /**
     * Reports whether or not this form should display the "receive a copy" input
     * 
     * @return bool
     */
    private function should_show_receipt_option() {
        return $this->get_plugin_config('receipt') != -1;
    }

    /**
     * Reports whether or not this form should display the "additional emails" input
     * 
     * @return bool
     */
    private function should_show_additional_email_input() {
        return $this->get_plugin_config('allow_external_emails');
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
     * Returns the current user's signatures for selection, plus a "none" option
     * 
     * @return array
     */
    private function get_user_signature_options() {
        return [0 => 'None'] + $this->user_signature_array;
    }

    private function get_create_signature_url() {
        return '/blocks/quickmail/signature.php?courseid=' . $this->course->id;
    }

    public function get_plugin_string($key, $a = null) {
        return \block_quickmail_plugin::_s($key, $a);
    }

    private function get_plugin_config($key) {
        return block_quickmail_plugin::_c($key, $this->course->id);
    }

}
