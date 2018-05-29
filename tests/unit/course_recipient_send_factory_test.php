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

use block_quickmail\messenger\factories\course_recipient_send\recipient_send_factory;
use block_quickmail\messenger\factories\course_recipient_send\email_recipient_send_factory;
use block_quickmail\messenger\factories\course_recipient_send\message_recipient_send_factory;

class block_quickmail_course_recipient_send_factory_testcase extends advanced_testcase {
    
    use has_general_helpers,
        sets_up_courses,
        creates_message_records;

    public function test_makes_email_recipient_send_factory()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $message = $this->create_compose_message($course, $user_teacher, [], [
            'message_type' => 'email'
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

        $message = $this->create_compose_message($course, $user_teacher, [], [
            'message_type' => 'message'
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

        $message = $this->create_compose_message($course, $user_teacher, [], [
            'message_type' => 'email'
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

        $message = $this->create_compose_message($course, $user_teacher, [], [
            'message_type' => 'email'
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

        $message = $this->create_compose_message($course, $user_teacher, [], [
            'message_type' => 'message'
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

        $message = $this->create_compose_message($course, $user_teacher, [], [
            'subject' => 'This is the subject',
            'body' => 'This is the body.',
        ]);

        $first_student = $user_students[0];

        $recipient = $this->create_message_recipient_from_user($message, $first_student);

        $factory = recipient_send_factory::make($message, $recipient);

        $this->assertEquals('This is the subject', $factory->message_params->subject);
    }

    public function test_recipient_send_factory_sets_prepended_subject_correctly()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        $this->update_system_config_value('block_quickmail_prepend_class', 'shortname');

        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $message = $this->create_compose_message($course, $user_teacher, [], [
            'subject' => 'This is the subject',
            'body' => 'This is the body.',
        ]);

        $first_student = $user_students[0];

        $recipient = $this->create_message_recipient_from_user($message, $first_student);

        $factory = recipient_send_factory::make($message, $recipient);

        $this->assertEquals('[' . $course->shortname . '] This is the subject', $factory->message_params->subject);
    }

    public function test_recipient_send_factory_formats_fullmessage_with_data()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $message = $this->create_compose_message($course, $user_teacher, [], [
            'body' => 'Hey there [:firstname:] [:middlename:] [:lastname:], your email address is [:email:]!',
        ]);

        $first_student = $user_students[0];

        $recipient = $this->create_message_recipient_from_user($message, $first_student);

        $factory = recipient_send_factory::make($message, $recipient);

        $expected_body = 'Hey there ' . $first_student->firstname . ' ' . $first_student->middlename . ' ' . $first_student->lastname . ', your email address is ' . $first_student->email . '!';

        $this->assertEquals(format_text_email($expected_body, 1), $factory->message_params->fullmessage);
    }

    public function test_recipient_send_factory_formats_fullmessagehtml_with_data()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $message = $this->create_compose_message($course, $user_teacher, [], [
            'body' => 'Hey there [:firstname:] [:middlename:] [:lastname:], your email address is [:email:]!',
        ]);

        $first_student = $user_students[0];

        $recipient = $this->create_message_recipient_from_user($message, $first_student);

        $factory = recipient_send_factory::make($message, $recipient);

        $expected_body = 'Hey there ' . $first_student->firstname . ' ' . $first_student->middlename . ' ' . $first_student->lastname . ', your email address is ' . $first_student->email . '!';

        $this->assertEquals(purify_html($expected_body), $factory->message_params->fullmessagehtml);
    }

    public function test_recipient_send_factory_sets_no_reply_params_correctly()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        $this->update_system_config_value('noreplyaddress', 'no@reply.com');

        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $message = $this->create_compose_message($course, $user_teacher, [], [
            'subject' => 'This is the subject',
            'body' => 'This is the body.',
            'no_reply' => true,
        ]);

        $first_student = $user_students[0];

        $recipient = $this->create_message_recipient_from_user($message, $first_student);

        $factory = recipient_send_factory::make($message, $recipient);

        $this->assertFalse($factory->message_params->usetrueaddress);
        $this->assertEquals('no@reply.com', $factory->message_params->replyto);
        $this->assertEquals('no@reply.com', $factory->message_params->replytoname);
    }

    public function test_recipient_send_factory_sets_reply_params_correctly()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        $this->update_system_config_value('noreplyaddress', 'no@reply.com');

        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $message = $this->create_compose_message($course, $user_teacher, [], [
            'subject' => 'This is the subject',
            'body' => 'This is the body.',
            'no_reply' => false,
        ]);

        $first_student = $user_students[0];

        $recipient = $this->create_message_recipient_from_user($message, $first_student);

        $factory = recipient_send_factory::make($message, $recipient);

        $this->assertTrue($factory->message_params->usetrueaddress);
        $this->assertEquals($user_teacher->email, $factory->message_params->replyto);
        $this->assertEquals(fullname($user_teacher), $factory->message_params->replytoname);
    }

}