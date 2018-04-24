<?php
 
require_once(dirname(__FILE__) . '/traits/unit_testcase_traits.php');

use block_quickmail\messenger\messenger;
use block_quickmail\persistents\message;
use block_quickmail\persistents\signature;
use block_quickmail\exceptions\validation_exception;

class block_quickmail_messenger_testcase extends advanced_testcase {
    
    use has_general_helpers, 
        sets_up_courses, 
        submits_compose_message_form, 
        sends_emails, 
        sends_messages;
    
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
        $this->assertEquals($user_students[0]->email, $this->email_in_sink_attr($sink, 1, 'to'));

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