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

require_once $CFG->libdir . '/formslib.php';
// describe the form created for admin_emial.php
class admin_email_form extends moodleform {

    function definition() {

        $mform =& $this->_form;

        $mform->addElement('text', 'subject', get_string('subject', 'block_quickmail'));
        $mform->setType('subject', PARAM_TEXT);
        
        $mform->addElement('text', 'noreply', get_string('noreply', 'block_quickmail'));
        $mform->setType('noreply', PARAM_EMAIL);

        $mform->addElement('editor', 'message_editor',  get_string('body', 'block_quickmail'), null, $this->_customdata['editor_options']);
        $mform->setType('message', PARAM_RAW);

        $buttons = array(
            $mform->createElement('submit', 'send', get_string('send_email', 'block_quickmail')),
            $mform->createElement('cancel', 'cancel', get_string('cancel'))
        );
        $mform->addGroup($buttons, 'actions', '&nbsp;', array(' '), false);

        $mform->addRule('subject', null, 'required', 'client');
        $mform->addRule('noreply', null, 'required', 'client');
        $mform->addRule('message_editor', null, 'required');
    }

    function validation($data, $files) {
        $errors = array();
        foreach(array('subject', 'message_editor') as $field) {
            if(empty($data[$field]))
                $errors[$field] = get_string('email_error_field', 'block_quickmail', $field);
        }
        return $errors;
    }
}
