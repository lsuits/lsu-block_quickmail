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

use block_quickmail\messenger\messenger;
use block_quickmail\persistents\message;
use block_quickmail\persistents\notification;
use block_quickmail\exceptions\validation_exception;

class block_quickmail_messenger_notification_testcase extends advanced_testcase {
    
    use has_general_helpers, 
        sets_up_courses, 
        sets_up_notifications, 
        sends_emails, 
        sends_messages;
    
    public function test_messenger_sends_reminder_notification_message()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);

        $sink = $this->open_email_sink();
        
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // create a reminder notification
        $reminder_notification = $this->create_reminder_notification_for_course_user('non-participation', $course, $user_teacher, null, [
            'name' => 'My first attempt'
        ]);

        // get the parent notification instance
        $notification = $reminder_notification->get_notification();

        // get a flat array of user ids to send to
        $recipient_user_ids = array_map(function($user) {
            return $user->id;
        }, $user_students);

        // create the message from the notification, sync recipient users, and send
        $message = messenger::via_notification($notification, $recipient_user_ids);

        $this->assertInstanceOf(message::class, $message);
        
        // run any tasks that may have been triggered (should be message sends)
        \phpunit_util::run_all_adhoc_tasks();

        $this->assertEquals(4, $this->email_sink_email_count($sink));
        $this->assertEquals('This is the subject', $this->email_in_sink_attr($sink, 1, 'subject'));
        $this->assertTrue($this->email_in_sink_body_contains($sink, 1, 'This is the body'));
        $this->assertEquals(get_config('moodle', 'noreplyaddress'), $this->email_in_sink_attr($sink, 1, 'from'));
        $this->assertEquals($user_students[0]->email, $this->email_in_sink_attr($sink, 1, 'to'));

        $this->close_email_sink($sink);
    }

}