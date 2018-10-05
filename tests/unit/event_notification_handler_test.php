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

use block_quickmail\notifier\event_notification_handler;

// use block_quickmail\persistents\event_notification;
// use block_quickmail\persistents\notification;
// use block_quickmail\notifier\models\event_notification_model;

class block_quickmail_event_notification_handler_testcase extends advanced_testcase {
    
    use has_general_helpers,
        sets_up_courses,
        sets_up_notifications;

    public function test_handles_course_entered_events()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
        
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();
        
        $notif = $this->create_event_notification_for_course_user('course-entered', $course, $user_students[0], null, [
            'is_enabled' => 1
        ]);

        event_notification_handler::course_entered($user_students[0]->id, $course->id);

        var_dump('woohoo!');die;
    }

}