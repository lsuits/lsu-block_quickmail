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
use block_quickmail\notifier\models\reminder\non_participation_model;
use block_quickmail\notifier\models\event\assignment_submitted_model;

class block_quickmail_notification_model_helper_testcase extends advanced_testcase {
    
    use has_general_helpers;

    public function test_gets_available_model_keys_by_type()
    {
        $reminder_types = notification_model_helper::get_available_model_keys_by_type('reminder');

        $this->assertInternalType('array', $reminder_types);
        $this->assertCount(1, $reminder_types);
        $this->assertContains('non_participation', $reminder_types);

        $event_types = notification_model_helper::get_available_model_keys_by_type('event');

        $this->assertInternalType('array', $event_types);
        $this->assertCount(1, $event_types);
        $this->assertContains('assignment_submitted', $event_types);
    }

    public function test_gets_model_class_name_from_key()
    {
        $non_participation_model_class_name = notification_model_helper::get_model_class_name('non_participation');
        $this->assertEquals('non_participation_model', $non_participation_model_class_name);

        $assignment_submitted_model_class_name = notification_model_helper::get_model_class_name('assignment_submitted');
        $this->assertEquals('assignment_submitted_model', $assignment_submitted_model_class_name);
    }

    public function test_gets_full_model_class_name_from_type_and_key()
    {
        $non_participation_full_model_class_name = notification_model_helper::get_full_model_class_name('reminder', 'non_participation');
        $this->assertEquals(non_participation_model::class, $non_participation_full_model_class_name);

        $assignment_submitted_full_model_class_name = notification_model_helper::get_full_model_class_name('event', 'assignment_submitted');
        $this->assertEquals(assignment_submitted_model::class, $assignment_submitted_full_model_class_name);
    }

    public function test_gets_object_type_for_model_type_and_key()
    {
        $type = notification_model_helper::get_object_type_for_model('reminder', 'non_participation');
        $this->assertEquals('course', $type);

        $type = notification_model_helper::get_object_type_for_model('event', 'assignment_submitted');
        $this->assertEquals('assignment', $type);
    }

    public function test_reports_whether_model_requires_object_or_not()
    {
        $result = notification_model_helper::model_requires_object('reminder', 'non_participation');
        $this->assertFalse($result);

        $result = notification_model_helper::model_requires_object('event', 'assignment_submitted');
        $this->assertTrue($result);
    }

    public function test_gets_condition_keys_for_model_type_and_key()
    {
        $keys = notification_model_helper::get_condition_keys_for_model('reminder', 'non_participation');
        $this->assertInternalType('array', $keys);
        $this->assertCount(2, $keys);

        $keys = notification_model_helper::get_condition_keys_for_model('event', 'assignment_submitted');
        $this->assertInternalType('array', $keys);
        $this->assertCount(0, $keys);
    }

    public function test_reports_whether_model_requires_conditions_or_not()
    {
        $result = notification_model_helper::model_requires_conditions('reminder', 'non_participation');
        $this->assertTrue($result);

        $result = notification_model_helper::model_requires_conditions('event', 'assignment_submitted');
        $this->assertFalse($result);
    }

    ///////////////////////////////////////////////
    ///
    /// HELPERS
    /// 
    //////////////////////////////////////////////
    
    // 

}