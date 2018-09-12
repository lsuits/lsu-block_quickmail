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

class block_quickmail_messenger_drafting_testcase extends advanced_testcase {
    
    use has_general_helpers, 
        sets_up_courses, 
        submits_compose_message_form, 
        sends_emails, 
        sends_messages,
        assigns_mentors;
    
    // public function test_message_with_alternate_id_posted_is_sent_from_that_alternate_email()

    public function test_messenger_saves_draft_email()
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

        // save this email message as a draft
        $message = messenger::save_compose_draft($user_teacher, $course, $compose_form_data);

        $message_recipients = $message->get_message_recipients();

        $this->assertEquals(0, $this->email_sink_email_count($sink));
        $this->assertCount(4, $message_recipients);
        $this->assertInstanceOf(message::class, $message);
        $this->assertEquals(1, $message->get('is_draft'));

        $this->close_email_sink($sink);
    }
    
    public function test_cannot_duplicate_a_draft_that_not_created_by_the_given_user()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
        
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // specify recipients
        $recipients['included']['user'] = $this->get_user_ids_from_user_array($user_students);

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($recipients, 'email', [
            'subject' => 'Hello world',
            'body' => 'This is one fine body.',
        ]);

        // save this email message as a draft
        $draft_message = messenger::save_compose_draft($user_teacher, $course, $compose_form_data);

        $this->expectException(validation_exception::class);

        // now attempt to duplicate this draft which belongs to the teacher
        $duplicated_draft = messenger::duplicate_draft($draft_message->get('id'), $user_students[0]);

        $this->assertNotInstanceOf(message::class, $duplicated_draft);
    }

    public function test_duplicates_drafts()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
        
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // specify recipients
        $recipients['included']['user'] = $this->get_user_ids_from_user_array($user_students);

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($recipients, 'email', [
            'subject' => 'Hello world',
            'body' => 'This is one fine body.',
        ]);

        // save this email message as a draft
        $draft_message = messenger::save_compose_draft($user_teacher, $course, $compose_form_data);

        // now attempt to duplicate this draft
        $duplicated_draft = messenger::duplicate_draft($draft_message->get('id'), $user_teacher);
        $this->assertInstanceOf(message::class, $duplicated_draft);
        $this->assertEquals($draft_message->get('course_id'), $duplicated_draft->get('course_id'));
        $this->assertEquals($draft_message->get('user_id'), $duplicated_draft->get('user_id'));
        $this->assertEquals($draft_message->get('message_type'), $duplicated_draft->get('message_type'));
        $this->assertEquals($draft_message->get('alternate_email_id'), $duplicated_draft->get('alternate_email_id'));
        $this->assertEquals($draft_message->get('signature_id'), $duplicated_draft->get('signature_id'));
        $this->assertEquals($draft_message->get('subject'), $duplicated_draft->get('subject'));
        $this->assertEquals($draft_message->get('body'), $duplicated_draft->get('body'));
        $this->assertEquals($draft_message->get('editor_format'), $duplicated_draft->get('editor_format'));
        $this->assertEquals(1, $duplicated_draft->get('is_draft'));
        $this->assertEquals($draft_message->get('send_receipt'), $duplicated_draft->get('send_receipt'));
        $this->assertEquals($draft_message->get('no_reply'), $duplicated_draft->get('no_reply'));
        // $this->assertEquals($user_teacher->id, $duplicated_draft->get('usermodified'));

        $draft_message_recipients = $draft_message->get_message_recipients();
        $duplicated_draft_recipients = $duplicated_draft->get_message_recipients();
        $this->assertEquals(count($draft_message_recipients), count($duplicated_draft_recipients));

        $draft_message_additional_emails = $draft_message->get_additional_emails();
        $duplicated_draft_additional_emails = $duplicated_draft->get_additional_emails();
        $this->assertEquals(count($draft_message_additional_emails), count($duplicated_draft_additional_emails));
    }

}