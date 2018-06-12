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

class select_model_form extends controller_form {

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
        ///  current input summary
        ////////////////////////////////////////////////////////////
        
        // notification_type
        $mform->addElement('static', 'notification_type', block_quickmail_string::get('notification_type'), block_quickmail_string::get('notification_type_' . $this->get_session_input('notification_type')));

        // notification_name
        $mform->addElement('static', 'notification_name', block_quickmail_string::get('notification_name'), $this->get_session_input('notification_name'));

        ////////////////////////////////////////////////////////////
        ///  notification_model (select)
        ////////////////////////////////////////////////////////////
        $mform->addElement(
            'select', 
            'notification_model', 
            block_quickmail_string::get('notification_model'), 
            $this->get_notification_model_options()
        );

        $mform->addRule('notification_model', block_quickmail_string::get('invalid_notification_model'), 'required', '', 'server');
        // get keys for validation below
        $valid_values = $this->get_custom_data('available_model_keys');
        $mform->addRule('notification_model', block_quickmail_string::get('invalid_notification_model'), 'callback', function($value) use ($valid_values) { return in_array($value, $valid_values);}, 'server');

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

    /**
     * Returns the options for notification model selection for this notification type
     * 
     * @return array
     */
    private function get_notification_model_options()
    {
        return array_merge(
            ['' => get_string('select')], 
            $this->get_custom_data('available_model_selection')
        );
    }

}
