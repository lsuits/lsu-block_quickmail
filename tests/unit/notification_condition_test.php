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

use block_quickmail\notifier\notification_condition;

class block_quickmail_notification_condition_testcase extends advanced_testcase {
    
    use has_general_helpers;

    public function test_formats_condition_for_storage()
    {
        $formatted_value = notification_condition::format_for_storage([
            'time_amount' => 2,
            'time_unit' => 'week'
        ]);

        $this->assertEquals('time_amount:2,time_unit:week', $formatted_value);
    }

    public function test_gets_values_from_set_condition()
    {
        $condition = new notification_condition([
            'time_amount' => 2,
            'time_unit' => 'week'
        ]);

        $this->assertEquals(2, $condition->get_value('time_amount'));
        $this->assertEquals('week', $condition->get_value('time_unit'));
    }

    public function test_gets_offset_time_from_conditions()
    {
        $condition = new notification_condition([
            'time_amount' => 2,
            'time_unit' => 'week'
        ]);

        $offset_before = $condition->get_offset_timestamp_from_now('before');

        $date = \DateTime::createFromFormat('U', time(), \core_date::get_server_timezone_object());
        $date->modify('-2 week');
        $expected_offset_before = $date->getTimestamp();

        $this->assertEquals($expected_offset_before, $offset_before);

        $offset_after = $condition->get_offset_timestamp_from_now('after');

        $date = \DateTime::createFromFormat('U', time(), \core_date::get_server_timezone_object());
        $date->modify('+2 week');
        $expected_offset_after = $date->getTimestamp();

        $this->assertEquals($expected_offset_after, $offset_after);
    }

    public function test_gets_required_conditions_for_a_type_of_notification()
    {
        $required_condition_keys = notification_condition::get_required_condition_keys('reminder', 'course-non-participation');

        $this->assertCount(2, $required_condition_keys);
        $this->assertContains('time_amount', $required_condition_keys);
        $this->assertContains('time_unit', $required_condition_keys);
    }

    public function test_gets_required_conditions_for_a_type_of_notification_with_prepend()
    {
        $required_condition_keys = notification_condition::get_required_condition_keys('reminder', 'course-non-participation', 'condition');

        $this->assertCount(2, $required_condition_keys);
        $this->assertContains('condition_time_amount', $required_condition_keys);
        $this->assertContains('condition_time_unit', $required_condition_keys);
    }

    public function test_creates_notification_condition_object_from_condition_string()
    {
        $condition_string = 'time_amount:4,time_unit:day';

        $condition = notification_condition::from_condition_string($condition_string);

        $this->assertInstanceOf(notification_condition::class, $condition);

        $this->assertCount(2, $condition->conditions);
        $this->assertEquals(4, $condition->conditions['time_amount']);
        $this->assertEquals('day', $condition->conditions['time_unit']);
    }

    ///////////////////////////////////////////////
    ///
    /// HELPERS
    /// 
    //////////////////////////////////////////////
    
    // 

}