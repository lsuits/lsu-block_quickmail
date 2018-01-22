<?php
 
require_once(dirname(__FILE__) . '/unit_testcase_traits.php');

use block_quickmail\messenger\factories\course_recipient_send\recipient_send_factory;
use block_quickmail\messenger\factories\course_recipient_send\email_recipient_send_factory;
use block_quickmail\messenger\factories\course_recipient_send\message_recipient_send_factory;

class block_quickmail_course_recipient_recipient_send_factory_testcase extends advanced_testcase {
    
    use unit_testcase_has_general_helpers;
    use unit_testcase_sets_up_courses;
    use unit_testcase_creates_message_records;

    public function test_makes_email_recipient_send_factory()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $message = $this->create_course_message($course, $user_teacher, [], [
            'output_channel' => 'email'
        ]);

        $first_student = $user_students[0];

        $recipient = $this->create_message_recipient_from_user($message, $first_student);

        $factory = recipient_send_factory::make($message, $recipient);

        $this->assertInstanceOf(email_recipient_send_factory::class, $factory);
    }

    public function test_makes_message_recipient_send_factory()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $message = $this->create_course_message($course, $user_teacher, [], [
            'output_channel' => 'message'
        ]);

        $first_student = $user_students[0];

        $recipient = $this->create_message_recipient_from_user($message, $first_student);

        $factory = recipient_send_factory::make($message, $recipient);

        $this->assertInstanceOf(message_recipient_send_factory::class, $factory);
    }

    public function test_recipient_send_factory_sets_global_params_correctly()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $message = $this->create_course_message($course, $user_teacher, [], [
            'output_channel' => 'email'
        ]);

        $first_student = $user_students[0];

        $recipient = $this->create_message_recipient_from_user($message, $first_student);

        $factory = recipient_send_factory::make($message, $recipient);

        $this->assertInternalType('object', $factory->message_params->userto);
        $this->assertEquals($first_student->id, $factory->message_params->userto->id);
        $this->assertInternalType('object', $factory->message_params->userfrom);
        $this->assertEquals($user_teacher->id, $factory->message_params->userfrom->id);
    }

    public function test_recipient_email_send_factory_sets_factory_params_correctly()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $message = $this->create_course_message($course, $user_teacher, [], [
            'output_channel' => 'email'
        ]);

        $first_student = $user_students[0];

        $recipient = $this->create_message_recipient_from_user($message, $first_student);

        $factory = recipient_send_factory::make($message, $recipient);

        $this->assertEquals(79, $factory->message_params->wordwrapwidth);
    }

    public function test_recipient_message_send_factory_sets_factory_params_correctly()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $message = $this->create_course_message($course, $user_teacher, [], [
            'output_channel' => 'message'
        ]);

        $first_student = $user_students[0];

        $recipient = $this->create_message_recipient_from_user($message, $first_student);

        $factory = recipient_send_factory::make($message, $recipient);

        $this->assertEquals('block_quickmail', $factory->message_params->component);
        $this->assertEquals('quickmessage', $factory->message_params->name);
        $this->assertEquals(FORMAT_HTML, $factory->message_params->fullmessageformat);
        $this->assertEquals(false, $factory->message_params->notification);
    }

    public function test_recipient_send_factory_sets_static_subject_correctly()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $message = $this->create_course_message($course, $user_teacher, [], [
            'subject' => 'This is the subject',
            'body' => 'This is the body.',
        ]);

        $first_student = $user_students[0];

        $recipient = $this->create_message_recipient_from_user($message, $first_student);

        $factory = recipient_send_factory::make($message, $recipient);

        $this->assertEquals('This is the subject', $factory->message_params->subject);
    }

    public function test_recipient_send_factory_sets_subject_with_prepended_idnumber_correctly()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $this->dd($course);

        $message = $this->create_course_message($course, $user_teacher, [], [
            'subject' => 'This is the subject',
            'body' => 'This is the body.',
        ]);

        $first_student = $user_students[0];

        $recipient = $this->create_message_recipient_from_user($message, $first_student);

        $factory = recipient_send_factory::make($message, $recipient);

        $this->assertEquals('This is the subject', $factory->message_params->subject);
    }

    // idnumber
    // shortname
    // fullname

}