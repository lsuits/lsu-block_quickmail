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

namespace block_quickmail\forms;

require_once $CFG->libdir . '/formslib.php';

use block_quickmail\forms\concerns\is_quickmail_form;
use block_quickmail_string;
use block_quickmail_config;
use block_quickmail\persistents\signature;

class manage_signatures_form extends \moodleform {

    use is_quickmail_form;

    public $errors;
    public $context;
    public $user;
    public $course;
    public $signature;
    public $user_signature_array;

    /**
     * Instantiates and returns a signature management form
     * 
     * @param  object        $context
     * @param  object        $user                   auth user
     * @param  persistent    $signature              optional, defaults to null
     * @param  int           $course_id              optional, course id
     * @return \block_quickmail\forms\manage_signatures_form
     */
    public static function make($context, $user, $signature = null, $course_id = 0)
    {
        $target_url = self::generate_target_url([
            'id' => ! empty($signature) ? $signature->get('id') : 0,
            'courseid' => $course_id,
        ]);

        // attempt to fetch the course, return null if not valid
        try {
            $course = get_course($course_id);
        } catch (dml_exception $e) {
            $course = null;
        }

        // get the auth user's current signatures as array (id => title)
        $user_signature_array = signature::get_flat_array_for_user($user->id);

        return new self($target_url, [
            'context' => $context,
            'user' => $user,
            'signature' => $signature,
            'user_signature_array' => $user_signature_array,
            'course' => $course,
        ], 'post', '', ['id' => 'mform-manage-signatures']);
    }

    /*
     * Moodle form definition
     */
    public function definition() {

        $mform =& $this->_form;

        $this->context = $this->_customdata['context'];
        $this->user = $this->_customdata['user'];
        $this->signature = $this->_customdata['signature'];
        $this->user_signature_array = $this->_customdata['user_signature_array'];
        $this->course = $this->_customdata['course'];

        ////////////////////////////////////////////////////////////
        ///  delete flag (hidden)
        ////////////////////////////////////////////////////////////
        $mform->addElement('hidden', 'delete_signature_flag');
        $mform->setType('delete_signature_flag', PARAM_INT);
        $mform->setDefault('delete_signature_flag', 0);

        ////////////////////////////////////////////////////////////
        ///  select_signature_id (select)
        ////////////////////////////////////////////////////////////
        $mform->addElement(
            'select', 
            'select_signature_id', 
            $this->get_plugin_string('select_signature_for_edit') . '<img class="transparent spinner-img" src="assets/frspinner.svg">', 
            $this->get_user_signature_options()
        );
        $mform->setType(
            'select_signature_id', 
            PARAM_INT
        );
        $mform->setDefault(
            'select_signature_id', 
            $this->signature ? $this->signature->get('id') : 0
        );

        $mform->addElement('html', '<hr>');
        
        ////////////////////////////////////////////////////////////
        ///  title (text)
        ////////////////////////////////////////////////////////////
        $mform->addElement(
            'text', 
            'title', 
            $this->get_plugin_string('title')
        );
        $mform->setType(
            'title', 
            PARAM_TEXT
        );
        
        ////////////////////////////////////////////////////////////
        ///  signature_editor (editor)
        ////////////////////////////////////////////////////////////
        $mform->addElement(
            'editor', 
            'signature_editor', 
            $this->get_plugin_string('signature'), 
            null, 
            $this->get_editor_options()
        );
        $mform->setType(
            'signature_editor', 
            PARAM_RAW
        );

        ////////////////////////////////////////////////////////////
        ///  default_flag (checkbox)
        ////////////////////////////////////////////////////////////
        $mform->addElement(
            'checkbox', 
            'default_flag', 
            get_string('default')
        );
        $mform->setType(
            'default_flag', 
            PARAM_BOOL
        );
     
        ////////////////////////////////////////////////////////////
        ///  buttons
        ////////////////////////////////////////////////////////////
        $buttons[] = $mform->createElement('submit', 'save', $this->get_plugin_string('save_signature'));
            
        if ($this->signature) {
            $buttons[] = $mform->createElement('button', 'delete', $this->get_plugin_string('delete_signature'));
        }
            
        $buttons[] = $mform->createElement('cancel', 'cancel', $this->get_cancel_button_text());
        
        $mform->addGroup($buttons, 'actions', '&nbsp;', array(' '), false);
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
     * Returns the current user's signatures for selection with a prepended "new signature" option
     * 
     * @return array
     */
    private function get_user_signature_options() {
        return [0 => 'Create New'] + $this->user_signature_array;
    }

    public function get_cancel_button_text() {
        return ! empty($this->course) ? $this->get_plugin_string('back_to_course') : get_string('cancel');
    }

    public function get_plugin_string($key, $a = null) {
        return block_quickmail_string::get($key, $a);
    }

}
