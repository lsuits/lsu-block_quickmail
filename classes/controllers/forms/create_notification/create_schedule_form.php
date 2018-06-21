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

class create_schedule_form extends controller_form {

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
        ///  descriptive text
        ////////////////////////////////////////////////////////////
        
        $mform->addElement('html', '<div style="margin-bottom: 20px;">' . block_quickmail_string::get('set_notification_schedule_description') . '</div>');

        // @TODO: condition summary ???

        ////////////////////////////////////////////////////////////
        ///  schedule_time_amount (text)
        ////////////////////////////////////////////////////////////
        $mform->addElement(
            'text', 
            'schedule_time_amount', 
            block_quickmail_string::get('time_amount'), 
            ['size' => 4]
        );
        
        $mform->setType(
            'schedule_time_amount', 
            PARAM_TEXT
        );

        $mform->setDefault(
            'schedule_time_amount', 
            ''
        );

        $mform->addRule('schedule_time_amount', block_quickmail_string::get('invalid_time_amount'), 'required', '', 'server');
        $mform->addRule('schedule_time_amount', block_quickmail_string::get('invalid_time_amount'), 'numeric', '', 'server');
        $mform->addRule('schedule_time_amount', block_quickmail_string::get('invalid_time_amount'), 'nonzero', '', 'server');

        ////////////////////////////////////////////////////////////
        ///  schedule_time_unit (select)
        ////////////////////////////////////////////////////////////
        $mform->addElement(
            'select', 
            'schedule_time_unit', 
            block_quickmail_string::get('time_unit'), 
            $this->get_time_unit_options()
        );

        $mform->addRule('schedule_time_unit', block_quickmail_string::get('invalid_time_unit'), 'required', '', 'server');

        ////////////////////////////////////////////////////////////
        ///  schedule_begin_at (date/time)
        ////////////////////////////////////////////////////////////
        $mform->addElement(
            'date_time_selector', 
            'schedule_begin_at', 
            block_quickmail_string::get('schedule_begin_at'),
            $this->get_schedule_time_options(false)
        );

        // $mform->setDefault(
        //     'schedule_begin_at',
        //     $this->get_draft_default_send_time()
        // );
        
        ////////////////////////////////////////////////////////////
        ///  schedule_end_at (date/time)
        ////////////////////////////////////////////////////////////
        $mform->addElement(
            'date_time_selector', 
            'schedule_end_at', 
            block_quickmail_string::get('schedule_end_at'),
            $this->get_schedule_time_options()
        );

        // $mform->setDefault(
        //     'schedule_end_at',
        //     $this->get_draft_default_send_time()
        // );



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
     * Returns the options schedule_time_unit selection
     * 
     * @return array
     */
    private function get_time_unit_options()
    {
        return [
            '' => get_string('select'),
            'day' => ucfirst(get_string('days')),
            'week' => ucfirst(get_string('weeks')),
            'month' => ucfirst(get_string('months')),
        ];
    }

    /**
     * Returns the options for schedule time selection
     * 
     * @param  bool  $optional  whether or not this field is optional
     * @return array
     */
    private function get_schedule_time_options($optional = true) {
        $current_year = date("Y");

        return [
            'startyear' => $current_year,
            'stopyear' => $current_year + 1,
            'timezone' => 99,
            'step' => 15,
            'optional' => $optional
        ];
    }

}
