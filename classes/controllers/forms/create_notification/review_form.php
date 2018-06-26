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

class review_form extends controller_form {

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
        ///  edit select type
        ////////////////////////////////////////////////////////////
        
        $mform->addElement(
            'static', 
            'type_description', 
            block_quickmail_string::get('notification_type'),
            block_quickmail_string::get('notification_model_' . $this->get_session_stored('notification_type') . '_' . $this->get_session_stored('notification_model')) . ' ' . block_quickmail_string::get('notification_type_' . $this->get_session_stored('notification_type'))
        );
        
        $mform->addElement(
            'static', 
            'title', 
            block_quickmail_string::get('notification_name'),
            $this->get_session_stored('notification_name')
        );

        $mform->addGroup([
            $mform->createElement('submit', 'edit_select_type', 'Edit Notification')
        ], 'actions', '&nbsp;', array(' '), false);

        $mform->addElement('html', '<hr>');

        ////////////////////////////////////////////////////////////
        ///  edit select object
        ////////////////////////////////////////////////////////////

        if ($this->get_session_stored('notification_object_id')) {
            // show object details here...
        }

        ////////////////////////////////////////////////////////////
        ///  edit conditions
        ////////////////////////////////////////////////////////////

        if ($this->get_custom_data('has_conditions')) {
            // show condition summary here...

            $mform->addGroup([
                $mform->createElement('submit', 'edit_set_conditions', 'Edit Conditions')
            ], 'actions', '&nbsp;', array(' '), false);

            $mform->addElement('html', '<hr>');
        }


    }

    private function has_set_condition_details() {        
        // 'condition_time_unit',
        // 'condition_time_relation',
        // 'condition_time_amount',
        // 'condition_grade_greater_than',
        // 'condition_grade_less_than',
    }

}
