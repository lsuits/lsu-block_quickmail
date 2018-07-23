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

namespace block_quickmail\controllers\forms\alternate_index;

require_once $CFG->libdir . '/formslib.php';

use block_quickmail\controllers\support\controller_form;
use block_quickmail_string;

class create_alternate_form extends controller_form {

    /*
     * Moodle form definition
     */
    public function definition() {

        $mform =& $this->_form;

        ////////////////////////////////////////////////////////////
        ///  view_form_name directive: TO BE INCLUDED ON ALL FORMS :/
        ////////////////////////////////////////////////////////////
        $mform->addElement('hidden', 'view_form_name');
        $mform->setType('view_form_name', PARAM_TEXT);
        $mform->setDefault('view_form_name', $this->get_view_form_name());

        ////////////////////////////////////////////////////////////
        ///  email (text)
        ////////////////////////////////////////////////////////////
        $mform->addElement(
            'text', 
            'email', 
            get_string('email')
        );
        $mform->setType(
            'email', 
            PARAM_TEXT
        );
        $mform->addRule('email', get_string('required'), 'required', '', 'server');

        ////////////////////////////////////////////////////////////
        ///  firstname (text)
        ////////////////////////////////////////////////////////////
        $mform->addElement(
            'text', 
            'firstname', 
            get_string('firstname')
        );
        $mform->setType(
            'firstname', 
            PARAM_TEXT
        );
        $mform->addRule('firstname', get_string('required'), 'required', '', 'server');

        ////////////////////////////////////////////////////////////
        ///  lastname (text)
        ////////////////////////////////////////////////////////////
        $mform->addElement(
            'text', 
            'lastname', 
            get_string('lastname')
        );
        $mform->setType(
            'lastname', 
            PARAM_TEXT
        );
        $mform->addRule('lastname', get_string('required'), 'required', '', 'server');

        ////////////////////////////////////////////////////////////
        ///  availability (select)
        ////////////////////////////////////////////////////////////
        $mform->addElement(
            'select', 
            'availability', 
            block_quickmail_string::get('alternate_availability'), 
            $this->get_availability_options()
        );
        $mform->setType(
            'availability', 
            PARAM_TEXT
        );

        ////////////////////////////////////////////////////////////
        ///  buttons
        ////////////////////////////////////////////////////////////
        $buttons = [
            $mform->createElement('cancel', 'cancel', get_string('back')),
            $mform->createElement('submit', 'save', get_string('save')),
        ];
        
        $mform->addGroup($buttons, 'actions', '&nbsp;', array(' '), false);
    }

    /**
     * Returns the current user's signatures for selection with a prepended "new signature" option
     * 
     * @return array
     */
    private function get_availability_options()
    {
        return [
            'only' => block_quickmail_string::get('alternate_availability_only'),
            'user' => block_quickmail_string::get('alternate_availability_user'),
            'course' => block_quickmail_string::get('alternate_availability_course'),
        ];
    }

}
