<?php
 
require_once(dirname(__FILE__) . '/unit_testcase_traits.php');

use block_quickmail\messenger\messenger;

class block_quickmail_compose_course_message_testcase extends advanced_testcase {
    
    use unit_testcase_has_general_helpers,
        unit_testcase_sets_up_courses,
        unit_testcase_submits_compose_message_form,
        unit_testcase_sends_emails,
        unit_testcase_sends_messages;

    public function test_send_composed_course_email_message_later()
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

        // send a message from the teacher to the students now
        messenger::send_composed_course_message($user_teacher, $course, $compose_form_data);

        \phpunit_util::run_all_adhoc_tasks();

        $emails = $sink->get_messages();
        $this->assertEquals(0, count($emails));
        $this->close_email_sink($sink);
    }

    public function test_send_composed_course_email_message_now()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
        
        $sink = $this->open_email_sink();
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($user_students, 'email', []);

        // send a message from the teacher to the students now
        messenger::send_composed_course_message($user_teacher, $course, $compose_form_data);

        \phpunit_util::run_all_adhoc_tasks();

        $messages = $sink->get_messages();
        $this->assertEquals(4, count($messages));
        $this->close_email_sink($sink);
    }

    public function test_send_composed_course_message_message_now()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
        
        $sink = $this->open_message_sink();
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($user_students, 'message', []);

        // send a message from the teacher to the students now
        messenger::send_composed_course_message($user_teacher, $course, $compose_form_data);

        \phpunit_util::run_all_adhoc_tasks();

        $messages = $sink->get_messages();
        $this->assertEquals(4, count($messages));
        $this->close_message_sink($sink);
    }

}