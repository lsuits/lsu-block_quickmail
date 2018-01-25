<?php
 
require_once(dirname(__FILE__) . '/traits/unit_testcase_traits.php');

use block_quickmail\messenger\messenger;

class block_quickmail_messenger_testcase extends advanced_testcase {
    
    use has_general_helpers, 
        sets_up_courses, 
        submits_compose_message_form, 
        sends_emails, 
        sends_messages;
    
    // public function test_message_with_alternate_id_posted_is_sent_from_that_alternate_email()
    
    // public function test_sync_message_recipients()
    
    // public function sync_message_additional_emails()

    public function test_sends_composed_course_email_message_now()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
        
        $sink = $this->open_email_sink();
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($user_students, 'email', [
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
        $this->assertEquals($user_students[0]->email, $this->email_in_sink_attr($sink, 1, 'to'));

        $this->close_email_sink($sink);
    }

    public function test_sends_composed_course_message_message_now()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
        
        $sink = $this->open_message_sink();
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($user_students, 'message', []);

        // send a moodle message from the teacher to the students now (not as queued adhoc tasks)
        messenger::compose($user_teacher, $course, $compose_form_data, null, false);

        $this->assertEquals(4, $this->message_sink_message_count($sink));
        $this->close_message_sink($sink);
    }

    public function test_sends_composed_course_email_message_later()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
        
        $sink = $this->open_email_sink();
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $now = time();
        $nextWeek = $now + (7 * 24 * 60 * 60);

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($user_students, 'email', [
            'to_send_at' => $nextWeek
        ]);

        // schedule an email from the teacher to the students (as queued adhoc tasks)
        messenger::compose($user_teacher, $course, $compose_form_data);

        \phpunit_util::run_all_adhoc_tasks();

        $this->assertEquals(0, $this->email_sink_email_count($sink));
        $this->close_email_sink($sink);
    }

    public function test_skips_invalid_user_ids_when_sending()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
        
        $sink = $this->open_email_sink();
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($user_students, 'email', [
            'subject' => 'Hello world',
            'body' => 'This is one fine body.',
            'mailto_ids' => '12,24,36,48,' . $user_students[0]->id
        ]);

        // send an email from the teacher to the students as queued adhoc tasks)
        messenger::compose($user_teacher, $course, $compose_form_data);

        \phpunit_util::run_all_adhoc_tasks();

        $this->assertEquals(1, $this->email_sink_email_count($sink));

        $this->close_email_sink($sink);
    }

}