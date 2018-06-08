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

namespace block_quickmail\controllers\forms\create_notification;

require_once $CFG->libdir . '/formslib.php';

use block_quickmail\controllers\support\controller_form;
use block_quickmail_string;
use block_quickmail_config;

class select_size_form extends controller_form {

    /*
     * Moodle form definition
     */
    public function definition() {

        $mform =& $this->_form;

        ////////////////////////////////////////////////////////////
        ///  view_form_name directive to be included on all forms
        ////////////////////////////////////////////////////////////
        $mform->addElement('hidden', 'view_form_name');
        $mform->setType('view_form_name', PARAM_TEXT);
        $mform->setDefault('view_form_name', $this->_customdata['view_form_name']);

        ////////////////////////////////////////////////////////////
        ///  name (text)
        ////////////////////////////////////////////////////////////
        $mform->addElement(
            'text', 
            'size', 
            'SIZE'
        );

        $mform->setType(
            'size', 
            PARAM_TEXT
        );

        $mform->setDefault(
            'size', 
            ''
        );

        $mform->addRule('size', get_string('error'), 'required', '', 'server');

        ////////////////////////////////////////////////////////////
        ///  buttons
        ////////////////////////////////////////////////////////////
        $buttons = [
            $mform->createElement('cancel', 'cancel', get_string('cancel')),
            $mform->createElement('submit', 'back', 'Back'),
            $mform->createElement('submit', 'next', 'Next'),
        ];
        
        $mform->addGroup($buttons, 'actions', '&nbsp;', array(' '), false);
    }

}
