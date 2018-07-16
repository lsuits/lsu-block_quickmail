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

class course_config_form extends \moodleform {

    use is_quickmail_form;

    public $errors;
    public $context;
    public $user;
    public $course;
    public $course_config;

    /**
     * Instantiates and returns a course configuration form
     * 
     * @param  object        $context
     * @param  object        $user                   auth user
     * @param  object        $course
     * @return \block_quickmail\forms\course_config_form
     */
    public static function make($context, $user, $course)
    {
        $target_url = self::generate_target_url([
            'courseid' => $course->id,
        ]);

        return new self($target_url, [
            'context' => $context,
            'user' => $user,
            'course' => $course,
        ], 'post', '', ['id' => 'mform-course-config']);
    }

    /*
     * Moodle form definition
     */
    public function definition() {

        $mform =& $this->_form;

        $this->context = $this->_customdata['context'];
        $this->user = $this->_customdata['user'];
        $this->course = $this->_customdata['course'];
        $this->course_config = block_quickmail_config::get('', $this->course);

        ////////////////////////////////////////////////////////////
        ///  restore flag
        ////////////////////////////////////////////////////////////
        $mform->addElement('hidden', 'restore_flag');
        $mform->setType('restore_flag', PARAM_INT);
        $mform->setDefault('restore_flag', 0);

        ////////////////////////////////////////////////////////////
        ///  allow students (select, based on global setting)
        ////////////////////////////////////////////////////////////
        if ($this->should_show_allow_students()) {
            $mform->addElement(
                'select', 
                'allowstudents', 
                block_quickmail_string::get('allowstudents'), 
                $this->get_yes_or_no_options());
            $mform->setDefault(
                'allowstudents', 
                $this->course_config['allowstudents']
            );
        } else {
            $mform->addElement('hidden', 'allowstudents');
            $mform->setType('allowstudents', PARAM_INT);
            $mform->setDefault('allowstudents', 0);
        }

        ////////////////////////////////////////////////////////////
        ///  role selection (select)
        ////////////////////////////////////////////////////////////
        $mform->addElement(
            'select', 
            'roleselection', 
            block_quickmail_string::get('selectable_roles'), 
            $this->get_all_available_roles()
        )->setMultiple(true);
        $mform->getElement('roleselection')->setSelected($this->get_selected_role_ids_array());
        $mform->addRule(
            'roleselection', 
            null, 
            'required'
        );
        $mform->addHelpButton(
            'roleselection', 
            'selectable_roles_configuration', 
            'block_quickmail'
        );

        ////////////////////////////////////////////////////////////
        ///  prepend class (select)
        ////////////////////////////////////////////////////////////
        $mform->addElement(
            'select', 
            'prepend_class', 
            block_quickmail_string::get('prepend_class'), 
            $this->get_prepend_class_options()
        );
        $mform->setDefault(
            'prepend_class', 
            $this->course_config['prepend_class']
        );
        $mform->addHelpButton(
            'prepend_class', 
            'prepend_class_configuration', 
            'block_quickmail'
        );

        ////////////////////////////////////////////////////////////
        ///  receipt (select)
        ////////////////////////////////////////////////////////////
        $mform->addElement(
            'select', 
            'receipt', 
            block_quickmail_string::get('receipt'), 
            $this->get_yes_or_no_options()
        );
        $mform->setDefault(
            'receipt', 
            $this->course_config['receipt']
        );
        $mform->addHelpButton(
            'receipt', 
            'receipt_configuration', 
            'block_quickmail'
        );

        ////////////////////////////////////////////////////////////
        ///  default message type (based on global setting)
        ////////////////////////////////////////////////////////////
        if ($this->should_show_default_message_type()) {
            $mform->addElement(
                'select', 
                'default_message_type', 
                block_quickmail_string::get('default_message_type'), 
                $this->get_default_message_type_options()
            );
            $mform->setDefault(
                'default_message_type', 
                $this->course_config['default_message_type']
            );
            $mform->addHelpButton(
                'default_message_type', 
                'default_message_type_configuration', 
                'block_quickmail'
            );
        } else {
            $mform->addElement(
                'static', 
                'default_message_type', 
                block_quickmail_string::get('default_message_type'), 
                $this->display_default_message_type()
            );
        }

        ////////////////////////////////////////////////////////////
        ///  buttons
        ////////////////////////////////////////////////////////////
        $buttons = [
            $mform->createElement('cancel', 'cancel', get_string('cancel'))
            $mform->createElement('submit', 'reset', get_string('resettodefaults')),
            $mform->createElement('submit', 'save', get_string('savepreferences')),
        ];
        
        $mform->addGroup($buttons, 'actions', '&nbsp;', array(' '), false);
    }

    /**
     * Reports whether or not the course configuration form should display "allow students" option (based on global configuration)
     * 
     * @return bool
     */
    private function should_show_allow_students()
    {
        return block_quickmail_config::get('allowstudents') !== -1;
    }

    /**
     * Returns a yes/no option selection array
     * 
     * @return array
     */
    private function get_yes_or_no_options()
    {
        return [
            0 => get_string('no'), 
            1 => get_string('yes')
        ];
    }

    /**
     * Returns all available roles for configuration options
     * 
     * @return array
     */
    private function get_all_available_roles()
    {
        return role_fix_names(get_all_roles($this->context), $this->context, ROLENAME_ALIAS, true);
    }

    /**
     * Returns the currently selected role ids as array
     * 
     * @return array
     */
    private function get_selected_role_ids_array()
    {
        return explode(',', $this->course_config['roleselection']);
    }

    /**
     * Returns the options for "prepend class" setting
     * 
     * @return array
     */
    private function get_prepend_class_options()
    {
        return [
            0 => get_string('none'),
            'idnumber' => get_string('idnumber'),
            'shortname' => get_string('shortnamecourse'),
            'fullname' => get_string('fullname')
        ];
    }

    /**
     * Reports whether or not the course configuration form should display "default message type" option (based on global configuration)
     * 
     * @return bool
     */
    private function should_show_default_message_type()
    {
        return block_quickmail_config::get('message_types_available') == 'all';
    }

    /**
     * Returns the options for "default message type" setting
     * 
     * @return array
     */
    private function get_default_message_type_options()
    {
        global $CFG;

        $options = [
            'email' => block_quickmail_string::get('message_type_email')
        ];

        if ( ! empty($CFG->messaging)) {
            $options['message'] = block_quickmail_string::get('message_type_message');
        }

        return $options;
    }
    
    /**
     * Returns the string for current forced message type
     * 
     * @return string
     */
    private function display_default_message_type()
    {
        $key = block_quickmail_config::get('message_types_available');

        return block_quickmail_string::get('message_type_' . $key);
    }

}
