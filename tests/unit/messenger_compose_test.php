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
use block_quickmail\persistents\signature;
use block_quickmail\exceptions\validation_exception;

class block_quickmail_messenger_compose_testcase extends advanced_testcase {
    
    use has_general_helpers, 
        sets_up_courses, 
        submits_compose_message_form, 
        sends_emails, 
        sends_messages,
        assigns_mentors;
    
    // public function test_message_with_alternate_id_posted_is_sent_from_that_alternate_email()
    
    public function test_messenger_sends_composed_email_now()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
        
        $sink = $this->open_email_sink();
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // specify recipients
        $recipients['included']['user'] = $this->get_user_ids_from_user_array($user_students);

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($recipients, 'email', [
            'subject' => 'Hello world',
            'body' => 'This is one fine body.',
        ]);

        // send an email from the teacher to the students now (not as queued adhoc tasks)
        messenger::compose($user_teacher, $course, $compose_form_data, null, false);

        $this->assertEquals(4, $this->email_sink_email_count($sink));
        $this->assertEquals('Hello world', $this->email_in_sink_attr($sink, 1, 'subject'));
        $this->assertTrue($this->email_in_sink_body_contains($sink, 1, 'This is one fine body.'));
        // $this->assertEquals($user_teacher->email, $this->email_in_sink_attr($sink, 1, 'from'));  <--- this would be nice
        $this->assertEquals(get_config('moodle', 'noreplyaddress'), $this->email_in_sink_attr($sink, 1, 'from'));
        // $this->assertEquals($user_students[0]->email, $this->email_in_sink_attr($sink, 1, 'to'));  <--- turning this off as the order of emails in sink seems to be random

        $this->close_email_sink($sink);
    }

    public function test_messenger_sends_composed_email_including_mentors_now()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
        
        $sink = $this->open_email_sink();
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // assign a mentor to the first student
        $mentor_user = $this->create_mentor_for_user($user_students[0]);

        // specify recipients
        $recipients['included']['user'] = $this->get_user_ids_from_user_array($user_students);

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($recipients, 'email', [
            'subject' => 'Hello world',
            'body' => 'This is one fine body.',
            'mentor_copy' => 1,
        ]);

        // send an email from the teacher to the students now (not as queued adhoc tasks)
        messenger::compose($user_teacher, $course, $compose_form_data, null, false);

        // should have been sent to 4 users + 1 mentor
        $this->assertEquals(5, $this->email_sink_email_count($sink));
        $this->assertEquals('Hello world', $this->email_in_sink_attr($sink, 1, 'subject'));
        $this->assertTrue($this->email_in_sink_body_contains($sink, 1, 'This is one fine body.'));
        // $this->assertEquals($user_teacher->email, $this->email_in_sink_attr($sink, 1, 'from'));  <--- this would be nice
        $this->assertEquals(get_config('moodle', 'noreplyaddress'), $this->email_in_sink_attr($sink, 1, 'from'));
        // $this->assertEquals($user_students[0]->email, $this->email_in_sink_attr($sink, 1, 'to'));  <--- turning this off as the order of emails in sink seems to be random

        $this->close_email_sink($sink);
    }

    public function test_messenger_does_not_send_compose_message_with_invalid_params()
    {
        $this->expectException(validation_exception::class);

        // reset all changes automatically after this test
        $this->resetAfterTest(true);
        
        $sink = $this->open_email_sink();
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($user_students, 'email', [
            'subject' => '',
            'body' => 'This is one fine body.',
        ]);

        // send an email from the teacher to the students now (not as queued adhoc tasks)
        messenger::compose($user_teacher, $course, $compose_form_data, null, false);

        $this->assertEquals(0, $this->email_sink_email_count($sink));

        $this->close_email_sink($sink);
    }

    public function test_messenger_sends_composed_message_now()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
        
        $sink = $this->open_message_sink();
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // specify recipients
        $recipients['included']['user'] = $this->get_user_ids_from_user_array($user_students);

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($recipients, 'message', []);

        // send a moodle message from the teacher to the students now (not as queued adhoc tasks)
        messenger::compose($user_teacher, $course, $compose_form_data, null, false);

        $this->assertEquals(4, $this->message_sink_message_count($sink));
        $this->close_message_sink($sink);
    }

    public function test_skips_invalid_user_ids_when_sending()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
        
        $sink = $this->open_email_sink();
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // specify recipients, some invalid
        $recipients['included']['user'] = ['12', '24', '36', '48', $user_students[0]->id];

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($recipients, 'email', [
            'subject' => 'Hello world',
            'body' => 'This is one fine body.',
        ]);

        // send an email from the teacher to the students as queued adhoc tasks)
        messenger::compose($user_teacher, $course, $compose_form_data, null, false);

        // \phpunit_util::run_all_adhoc_tasks();

        $this->assertEquals(1, $this->email_sink_email_count($sink));

        $this->close_email_sink($sink);
    }

    public function test_messenger_does_not_send_scheduled_composed_email_now()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
        
        $sink = $this->open_email_sink();
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // specify recipients
        $recipients['included']['user'] = $this->get_user_ids_from_user_array($user_students);

        $now = time();
        $nextWeek = $now + (7 * 24 * 60 * 60);

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($recipients, 'email', [
            'to_send_at' => $nextWeek
        ]);

        // schedule an email from the teacher to the students (as queued adhoc tasks)
        messenger::compose($user_teacher, $course, $compose_form_data);

        \phpunit_util::run_all_adhoc_tasks();

        $this->assertEquals(0, $this->email_sink_email_count($sink));
        $this->close_email_sink($sink);
    }

    public function test_messenger_sends_to_additional_emails()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
        
        $sink = $this->open_email_sink();
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // specify recipients
        $recipients['included']['user'] = $this->get_user_ids_from_user_array($user_students);

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($recipients, 'email', [
            'subject' => 'Hello world',
            'body' => 'This is one fine body.',
            'additional_emails' => 'additional@one.com,additional@two.com,additional@three.com'
        ]);

        // send an email from the teacher to the students now (not as queued adhoc tasks)
        messenger::compose($user_teacher, $course, $compose_form_data, null, false);

        $this->assertEquals(7, $this->email_sink_email_count($sink));
        $this->assertEquals('Hello world', $this->email_in_sink_attr($sink, 7, 'subject'));
        $this->assertTrue($this->email_in_sink_body_contains($sink, 7, 'This is one fine body.'));
        $this->assertEquals(get_config('moodle', 'noreplyaddress'), $this->email_in_sink_attr($sink, 7, 'from'));
        $this->assertEquals('additional@three.com', $this->email_in_sink_attr($sink, 7, 'to'));

        $this->close_email_sink($sink);
    }

    public function test_messenger_sends_a_receipt_if_asked()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
        
        $sink = $this->open_email_sink();
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // specify recipients
        $recipients['included']['user'] = $this->get_user_ids_from_user_array($user_students);

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($recipients, 'email', [
            'subject' => 'Hello world',
            'body' => 'This is one fine body.',
            'receipt' => '1'
        ]);

        // send an email from the teacher to the students now (not as queued adhoc tasks)
        messenger::compose($user_teacher, $course, $compose_form_data, null, false);

        $this->assertEquals(5, $this->email_sink_email_count($sink));
        $this->assertEquals(block_quickmail_string::get('send_receipt_subject_addendage') . ': Hello world', $this->email_in_sink_attr($sink, 5, 'subject'));
        $this->assertTrue($this->email_in_sink_body_contains($sink, 5, 'This message is to inform you that your message was sent.'));
        $this->assertEquals(get_config('moodle', 'noreplyaddress'), $this->email_in_sink_attr($sink, 5, 'from'));
        $this->assertEquals($user_teacher->email, $this->email_in_sink_attr($sink, 5, 'to'));

        $this->close_email_sink($sink);
    }

    public function test_messenger_sends_with_signature_appended()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
        
        $sink = $this->open_email_sink();
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // create a signature for the teacher
        $signature = signature::create_new([
            'user_id' => $user_teacher->id,
            'title' => 'mine',
            'signature' => '<p>This is my signature! Signed, The Teacher!</p>',
        ]);

        // specify recipients
        $recipients['included']['user'] = $this->get_user_ids_from_user_array($user_students);

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($recipients, 'email', [
            'subject' => 'Hello world',
            'body' => 'This is one fine body.',
            'signature_id' => $signature->get('id')
        ]);

        // send an email from the teacher to the students now (not as queued adhoc tasks)
        messenger::compose($user_teacher, $course, $compose_form_data, null, false);

        $this->assertTrue($this->email_in_sink_body_contains($sink, 1, 'This is one fine body.'));
        $this->assertTrue($this->email_in_sink_body_contains($sink, 1, 'This is my signature! Signed, The Teacher!'));

        $this->close_email_sink($sink);
    }

}