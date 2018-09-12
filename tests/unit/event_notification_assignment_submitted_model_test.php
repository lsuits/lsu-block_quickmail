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

use block_quickmail\persistents\notification;
use block_quickmail\notifier\models\notification_model_helper;
use block_quickmail\notifier\models\event\assignment_submitted_model;

class block_quickmail_event_notification_assignment_submitted_model_testcase extends advanced_testcase {
    
    use has_general_helpers,
        sets_up_courses,
        sets_up_notifications,
        sets_up_notification_models;

    public function test_model_key_is_available()
    {
        $types = notification_model_helper::get_available_model_keys_by_type('event');

        $this->assertContains('assignment_submitted', $types);
    }

    public function test_gets_model_class_name_from_key()
    {
        $model_class_name = notification_model_helper::get_model_class_name('assignment_submitted');
        
        $this->assertEquals('assignment_submitted_model', $model_class_name);
    }

    public function test_gets_full_model_class_name_from_type_and_key()
    {
        $full_model_class_name = notification_model_helper::get_full_model_class_name('event', 'assignment_submitted');
        
        $this->assertEquals(assignment_submitted_model::class, $full_model_class_name);
    }

    public function test_gets_object_type_for_model_type_and_key()
    {
        $type = notification_model_helper::get_object_type_for_model('event', 'assignment_submitted');
        
        $this->assertEquals('assignment', $type);
    }

    public function test_reports_whether_model_requires_object_or_not()
    {
        $result = notification_model_helper::model_requires_object('event', 'assignment_submitted');
        
        $this->assertTrue($result);
    }

    public function test_gets_condition_keys_for_model_type_and_key()
    {
        $keys = notification_model_helper::get_condition_keys_for_model('event', 'assignment_submitted');
        
        $this->assertInternalType('array', $keys);
        $this->assertCount(0, $keys);
    }

    public function test_reports_whether_model_requires_conditions_or_not()
    {
        $result = notification_model_helper::model_requires_conditions('event', 'assignment_submitted');
        
        $this->assertFalse($result);
    }

    // public function test_gets_required_conditions_for_type()
    // {
    //     $condition_keys = notification::get_required_conditions_for_type('event', 'assignment_submitted');

    //     $this->assertCount(2, $condition_keys);
    //     $this->assertContains('time_amount', $condition_keys);
    //     $this->assertContains('time_unit', $condition_keys);
    // }

}