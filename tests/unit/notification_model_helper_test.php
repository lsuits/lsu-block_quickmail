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
 
require_once(dirname(__FILE__) . '/traits/unit_testcase_traits.php');

use block_quickmail\notifier\models\notification_model_helper;

class block_quickmail_notification_model_helper_testcase extends advanced_testcase {
    
    use has_general_helpers;

    public function test_gets_available_model_keys_by_type()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);

        $reminder_types = notification_model_helper::get_available_model_keys_by_type('reminder');

        $this->assertInternalType('array', $reminder_types);
        $this->assertCount(1, $reminder_types);
        $this->assertContains('non_participation', $reminder_types);

        $event_types = notification_model_helper::get_available_model_keys_by_type('event');

        $this->assertInternalType('array', $event_types);
        $this->assertCount(1, $event_types);
        $this->assertContains('assignment_submitted', $event_types);
    }

    public function test_gets_available_model_selection_by_type()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);

        $reminder_selection_array = notification_model_helper::get_available_model_selection_by_type('reminder');

        $this->assertInternalType('array', $reminder_selection_array);
        $this->assertCount(1, $reminder_selection_array);
        $this->assertArrayHasKey('non_participation', $reminder_selection_array);

        $event_selection_array = notification_model_helper::get_available_model_selection_by_type('event');

        $this->assertInternalType('array', $event_selection_array);
        $this->assertCount(1, $event_selection_array);
        $this->assertArrayHasKey('assignment_submitted', $event_selection_array);
    }

    ///////////////////////////////////////////////
    ///
    /// HELPERS
    /// 
    //////////////////////////////////////////////
    
    // 

}