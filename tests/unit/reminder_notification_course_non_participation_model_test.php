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
use block_quickmail\notifier\models\reminder\course_non_participation_model;

class block_quickmail_reminder_notification_course_non_participation_model_testcase extends advanced_testcase {
    
    use has_general_helpers,
        sets_up_courses,
        sets_up_notifications,
        sets_up_notification_models;

    public function test_notification_model_helper_supports_model()
    {
        // model key is available
        $types = notification_model_helper::get_available_model_keys_by_type('reminder');
        $this->assertContains('course_non_participation', $types);
        
        // gets short model class name from key
        $short_model_class_name = notification_model_helper::get_model_class_name('course_non_participation');
        $this->assertEquals('course_non_participation_model', $short_model_class_name);
        
        // gets full model class name from type and key
        $full_model_class_name = notification_model_helper::get_full_model_class_name('reminder', 'course_non_participation');
        $this->assertEquals(course_non_participation_model::class, $full_model_class_name);
        
        // gets object type from type and key
        $type = notification_model_helper::get_object_type_for_model('reminder', 'course_non_participation');
        $this->assertEquals('course', $type);
        
        // reports if object is required
        $result = notification_model_helper::model_requires_object('reminder', 'course_non_participation');
        $this->assertFalse($result);
        
        // reports if conditions are required
        $result = notification_model_helper::model_requires_conditions('reminder', 'course_non_participation');
        $this->assertTrue($result);
        
        // test gets available condition keys
        $keys = notification_model_helper::get_condition_keys_for_model('reminder', 'course_non_participation');
        $this->assertInternalType('array', $keys);
        $this->assertCount(2, $keys);
        
        // test gets required condition keys
        $condition_keys = notification::get_required_conditions_for_type('reminder', 'course-non-participation');
        $this->assertCount(2, $condition_keys);
        $this->assertContains('time_amount', $condition_keys);
        $this->assertContains('time_unit', $condition_keys);
    }

    // CANNOT GET THIS TO WORK :(
    
    // public function test_gets_correct_user_ids_to_notify()
    // {
    //     // reset all changes automatically after this test
    //     $this->resetAfterTest(true);

    //     // set up a course with a teacher and 4 students
    //     list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

    //     $model = $this->create_reminder_notification_model('course-non-participation', $course, $user_teacher, $course, [
    //         'name' => 'My Non Participation Notification',
    //         'condition_time_amount' => 6,
    //         'condition_time_unit' => 'day',
    //     ]);

    //     $this->dd($model);

    //     $ids_to_notify = $model->get_user_ids_to_notify();

    //     // four students in course which have never logged in
    //     $this->assertCount(4, $ids_to_notify);
    //     $this->assertContains($user_students[0]->id, $ids_to_notify);
    //     $this->assertContains($user_students[1]->id, $ids_to_notify);
    //     $this->assertContains($user_students[2]->id, $ids_to_notify);
    //     $this->assertContains($user_students[3]->id, $ids_to_notify);
    //     $this->assertNotContains($user_teacher->id, $ids_to_notify);

    //     // update access record for one of the students
    //     $goodstudent = $user_students[0];
    //     global $DB;
    //     $DB->insert_record('user_lastaccess', (object) [
    //         'userid' => $goodstudent->id,
    //         'courseid' => $course->id,
    //         'timeaccess' => time(),
    //     ]);

    //     $ids_to_notify = $model->get_user_ids_to_notify();

    //     // four students in course which have never logged in
    //     $this->assertCount(3, $ids_to_notify);
    //     $this->assertNotContains($goodstudent->id, $ids_to_notify);
    //     $this->assertContains($user_students[1]->id, $ids_to_notify);
    //     $this->assertContains($user_students[2]->id, $ids_to_notify);
    //     $this->assertContains($user_students[3]->id, $ids_to_notify);
    //     $this->assertNotContains($user_teacher->id, $ids_to_notify);
    // }

}