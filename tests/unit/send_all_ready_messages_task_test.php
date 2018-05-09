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
use block_quickmail\tasks\send_all_ready_messages_task;

class block_quickmail_send_all_ready_messages_task_testcase extends advanced_testcase {
    
    use has_general_helpers, 
        sets_up_courses, 
        submits_compose_message_form, 
        sends_emails, 
        sends_messages;
    
    public function test_send_all_ready_messages_task_sends_messages()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
        
        $sink = $this->open_email_sink();
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $messages = $this->create_messages($course, $user_teacher, $user_students);

        \phpunit_util::run_all_adhoc_tasks();

        // should be no tasks fire yet, so no emails
        $this->assertEquals(0, $this->email_sink_email_count($sink));

        $task = new send_all_ready_messages_task();

        $task->execute(time());

        \phpunit_util::run_all_adhoc_tasks();

        // should have executed the task, so 4 * 4 emails = 16
        $this->assertEquals(16, $this->email_sink_email_count($sink));

        $this->close_email_sink($sink);
    }

    /////////////////////////////////
    ///
    /// HELPERS
    /// 
    /////////////////////////////////
    
    /*
     * Returns an array of 8 messages each with 4 recipients, 4 should be sent now, 4 should be sent in the future
     */
    private function create_messages($course, $user_teacher, $user_students)
    {
        $messages = [];

        foreach (range(1, 8) as $i) {
            // specify recipients
            $recipients['included']['user'] = $this->get_user_ids_from_user_array($user_students);

            // every other message to be sent later
            if ( ! in_array($i, [2, 4, 6, 8])) {
                $time_to_send = time() + 100000;
            } else {
                $time_to_send = time();
            }

            // get a compose form submission
            $compose_form_data = $this->get_compose_message_form_submission($recipients, 'email', [
                'to_send_at' => $time_to_send
            ]);

            // schedule an email from the teacher to the students (as queued adhoc tasks)
            $message = messenger::compose($user_teacher, $course, $compose_form_data, null, true);

            $messages[] = $message;
        }

        return $messages;
    }

}