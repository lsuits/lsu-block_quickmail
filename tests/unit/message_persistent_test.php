<?php
 
require_once(dirname(__FILE__) . '/traits/unit_testcase_traits.php');

use block_quickmail\persistents\message;
use block_quickmail\persistents\message_recipient;
use block_quickmail\persistents\message_additional_email;

class block_quickmail_message_persistent_testcase extends advanced_testcase {
    
    use has_general_helpers,
        sets_up_courses;

    public function test_getters()
    {
        $this->resetAfterTest(true);

        $message = message::create_new([
            'course_id' => 1,
            'user_id' => 1,
            'message_type' => 'email',
            'subject' => 'Id dolore irure nostrud dolor eu elit et laborum',
            'body' => 'Id dolore irure nostrud dolor eu elit et laborum sit ullamco laboris cillum consectetur irure quis esse occaecat in amet culpa nulla duis id velit in ut officia.',
        ]);

        $this->assertEquals('Id dolore irure...', $message->get_subject_preview(20));
        $this->assertEquals('Id dolore irure nostrud dolor eu elit et...', $message->get_body_preview(40));
        $this->assertEquals(date('Y-m-d H:i:s', $message->get('timecreated')), $message->get_readable_created_at());
        $this->assertEquals(date('Y-m-d H:i:s', $message->get('timemodified')), $message->get_readable_last_modified_at());
        $this->assertEquals(date('Y-m-d H:i:s', $message->get('sent_at')), $message->get_readable_sent_at());
        $this->assertEquals(date('Y-m-d H:i:s', $message->get('to_send_at')), $message->get_readable_to_send_at());
    }

    public function test_get_message_recipients()
    {
        $this->resetAfterTest(true);

        $message = $this->create_message();

        $user_one = $this->getDataGenerator()->create_user();

        $one = message_recipient::create_new([
            'message_id' => $message->get('id'),
            'user_id' => $user_one->id,
        ]);

        $user_two = $this->getDataGenerator()->create_user();

        $two = message_recipient::create_new([
            'message_id' => $message->get('id'),
            'user_id' => $user_two->id,
        ]);

        $user_three = $this->getDataGenerator()->create_user();

        $three = message_recipient::create_new([
            'message_id' => $message->get('id'),
            'user_id' => $user_three->id,
        ]);

        $message_recipients = $message->get_message_recipients();
        $message_recipient_array = $message->get_message_recipients(true);

        $this->assertCount(3, $message_recipients);
        $this->assertInstanceOf(message_recipient::class, $message_recipients[0]);
        $this->assertCount(3, $message_recipient_array);
        $this->assertEquals($user_two->id, $message_recipient_array[1]);
    }

    public function test_get_additional_emails()
    {
        $this->resetAfterTest(true);

        $message = $this->create_message();

        $one = message_additional_email::create_new([
            'message_id' => $message->get('id'),
            'email' => 'email@one.com',
        ]);

        $two = message_additional_email::create_new([
            'message_id' => $message->get('id'),
            'email' => 'email@two.com',
        ]);

        $three = message_additional_email::create_new([
            'message_id' => $message->get('id'),
            'email' => 'email@three.com',
        ]);

        $additional_emails = $message->get_additional_emails();
        $additional_email_array = $message->get_additional_emails(true);

        $this->assertCount(3, $additional_emails);
        $this->assertInstanceOf(message_additional_email::class, $additional_emails[0]);
        $this->assertCount(3, $additional_email_array);
        $this->assertEquals('email@two.com', $additional_email_array[1]);
    }

    public function test_sync_recipients()
    {
        $this->resetAfterTest(true);

        $message = $this->create_message();

        // create 2 original recipients
        
        $user_one = $this->getDataGenerator()->create_user();
        $one = message_recipient::create_new([
            'message_id' => $message->get('id'),
            'user_id' => $user_one->id,
        ]);

        $user_two = $this->getDataGenerator()->create_user();
        $two = message_recipient::create_new([
            'message_id' => $message->get('id'),
            'user_id' => $user_two->id,
        ]);

        $original_recipient_array = $message->get_message_recipients(true);
        $this->assertCount(2, $original_recipient_array);

        // create new users to become recipients

        $user_three = $this->getDataGenerator()->create_user();
        $user_four = $this->getDataGenerator()->create_user();
        $user_five = $this->getDataGenerator()->create_user();

        // sync the recipients

        $message->sync_recipients([
            $user_three->id,
            $user_four->id,
            $user_five->id
        ]);

        $new_recipient_array = $message->get_message_recipients(true);
        $this->assertCount(3, $new_recipient_array);

        $this->assertCount(0, array_intersect($original_recipient_array, $new_recipient_array));
    }

    public function test_sync_recipients_caches_count()
    {
        $this->resetAfterTest(true);

        $message = $this->create_message();

        // create new users to become recipients

        $user_three = $this->getDataGenerator()->create_user();
        $user_four = $this->getDataGenerator()->create_user();
        $user_five = $this->getDataGenerator()->create_user();

        // sync the recipients

        $message->sync_recipients([
            $user_three->id,
            $user_four->id,
            $user_five->id
        ]);

        $cache = \cache::make('block_quickmail', 'qm_msg_recip_count');
        $cached_count = $cache->get($message->get('id'));
        $this->assertEquals(3, $cached_count);

        $value = $message->cached_recipient_count();
        $this->assertEquals(3, $value);
    }

    public function test_sync_additional_emails()
    {
        $this->resetAfterTest(true);

        $message = $this->create_message();

        // create 2 original additional emails
        
        $one = message_additional_email::create_new([
            'message_id' => $message->get('id'),
            'email' => 'email@one.com',
        ]);

        $two = message_additional_email::create_new([
            'message_id' => $message->get('id'),
            'email' => 'email@two.com',
        ]);

        $original_email_array = $message->get_additional_emails(true);
        $this->assertCount(2, $original_email_array);

        $new_email_array = ['email@three.com', 'email@four.com', 'email@five.com'];

        $message->sync_additional_emails($new_email_array);

        $this->assertCount(3, $message->get_additional_emails(true));

        $this->assertCount(0, array_intersect($original_email_array, $new_email_array));
    }

    public function test_sync_additional_emails_caches_count()
    {
        $this->resetAfterTest(true);

        $message = $this->create_message();

        $new_email_array = ['email@three.com', 'email@four.com', 'email@five.com'];

        $message->sync_additional_emails($new_email_array);

        $cache = \cache::make('block_quickmail', 'qm_msg_addl_email_count');
        $cached_count = $cache->get($message->get('id'));
        $this->assertEquals(3, $cached_count);

        $value = $message->cached_additional_email_count();
        $this->assertEquals(3, $value);
    }

    public function test_message_draft_status()
    {
        $this->resetAfterTest(true);

        $message = message::create_new([
            'course_id' => 1,
            'user_id' => 1,
            'message_type' => 'email',
            'is_draft' => true
        ]);

        $this->assertTrue($message->is_message_draft());
        $this->assertEquals('drafted', $message->get_status());
    }

    public function test_message_queued_status()
    {
        $this->resetAfterTest(true);

        $message = message::create_new([
            'course_id' => 1,
            'user_id' => 1,
            'message_type' => 'email',
            'to_send_at' => time()
        ]);

        $this->assertTrue($message->is_queued_message());
        $this->assertEquals('queued', $message->get_status());
    }

    public function test_message_sending_status()
    {
        $this->resetAfterTest(true);

        $message = message::create_new([
            'course_id' => 1,
            'user_id' => 1,
            'message_type' => 'email',
            'is_sending' => true
        ]);

        $this->assertTrue($message->is_being_sent());
        $this->assertEquals('sending', $message->get_status());
    }

    public function test_message_sent_status()
    {
        $this->resetAfterTest(true);

        $message = message::create_new([
            'course_id' => 1,
            'user_id' => 1,
            'message_type' => 'email',
            'sent_at' => time()
        ]);

        $this->assertTrue($message->is_sent_message());
        $this->assertEquals('sent', $message->get_status());
    }

    public function test_message_get_to_send_in_future()
    {
        $this->resetAfterTest(true);

        $now = time();
        $nextWeek = $now + (7 * 24 * 60 * 60);

        $message_now = message::create_new([
            'course_id' => 1,
            'user_id' => 1,
            'message_type' => 'email',
            'to_send_at' => $now
        ]);

        $message_future = message::create_new([
            'course_id' => 1,
            'user_id' => 1,
            'message_type' => 'email',
            'to_send_at' => $nextWeek
        ]);

        $this->assertFalse($message_now->get_to_send_in_future());
        $this->assertTrue($message_future->get_to_send_in_future());
    }

    public function test_create_composed_as_draft()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
        
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $params = [
            'message_type' => 'message',
            'alternate_email_id' => 4,
            'signature_id' => 6,
            'subject' => 'subject is here',
            'message' => 'the message',
            'receipt' => 0,
            'to_send_at' => 0,
            'no_reply' => 1,
        ];

        $message = message::create_composed($user_teacher, $course, (object) $params, true);

        $this->assertInstanceOf(message::class, $message);
        $this->assertEquals($course->id, $message->get('course_id'));
        $this->assertEquals($user_teacher->id, $message->get('user_id'));
        $this->assertEquals($params['message_type'], $message->get('message_type'));
        $this->assertEquals($params['alternate_email_id'], $message->get('alternate_email_id'));
        $this->assertEquals($params['signature_id'], $message->get('signature_id'));
        $this->assertEquals($params['subject'], $message->get('subject'));
        $this->assertEquals($params['message'], $message->get('body'));
        $this->assertEquals($params['receipt'], $message->get('send_receipt'));
        $this->assertEquals($params['to_send_at'], $message->get('to_send_at'));
        $this->assertEquals($params['no_reply'], $message->get('no_reply'));
        $this->assertEquals(1, $message->get('is_draft'));
    }

    public function test_create_composed_not_as_draft()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
        
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $params = [
            'message_type' => 'message',
            'alternate_email_id' => 4,
            'signature_id' => 6,
            'subject' => 'subject is here',
            'message' => 'the message',
            'receipt' => 0,
            'to_send_at' => 0,
            'no_reply' => 1,
        ];

        $message = message::create_composed($user_teacher, $course, (object) $params, false);

        $this->assertInstanceOf(message::class, $message);
        $this->assertEquals(0, $message->get('is_draft'));
    }
    
    public function test_update_draft_as_draft()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
        
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $creation_params = [
            'message_type' => 'message',
            'alternate_email_id' => 4,
            'signature_id' => 6,
            'subject' => 'subject is here',
            'message' => 'the message',
            'receipt' => 0,
            'to_send_at' => 0,
            'no_reply' => 1,
        ];

        $message = message::create_composed($user_teacher, $course, (object) $creation_params, true);

        $update_params = [
            'message_type' => 'email',
            'alternate_email_id' => 5,
            'signature_id' => 7,
            'subject' => 'an updated subject is here',
            'message' => 'the updated message',
            'receipt' => 1,
            'to_send_at' => 1518124011,
            'no_reply' => 0,
        ];

        $updated_message = $message->update_draft((object) $update_params);

        $this->assertInstanceOf(message::class, $updated_message);
        $this->assertEquals($course->id, $updated_message->get('course_id'));
        $this->assertEquals($user_teacher->id, $updated_message->get('user_id'));
        $this->assertEquals($update_params['message_type'], $updated_message->get('message_type'));
        $this->assertEquals($update_params['alternate_email_id'], $updated_message->get('alternate_email_id'));
        $this->assertEquals($update_params['signature_id'], $updated_message->get('signature_id'));
        $this->assertEquals($update_params['subject'], $updated_message->get('subject'));
        $this->assertEquals($update_params['message'], $updated_message->get('body'));
        $this->assertEquals($update_params['receipt'], $updated_message->get('send_receipt'));
        $this->assertEquals($update_params['to_send_at'], $updated_message->get('to_send_at'));
        $this->assertEquals($update_params['no_reply'], $updated_message->get('no_reply'));
        $this->assertEquals(1, $updated_message->get('is_draft'));

        $second_update_params = [
            'message_type' => 'email',
            'alternate_email_id' => 5,
            'signature_id' => 7,
            'subject' => 'an updated subject is here',
            'message' => 'the updated message',
            'receipt' => 1,
            'to_send_at' => 1518124011,
            'no_reply' => 0,
        ];

        $second_updated_message = $message->update_draft((object) $second_update_params, false);

        $this->assertEquals(0, $second_updated_message->get('is_draft'));
    }

    public function test_filter_messages_by_course_from_array()
    {
        $this->resetAfterTest(true);

        $messages = [];

        $messages[] = $this->create_message(false, 1, 1);
        $messages[] = $this->create_message(false, 2, 1);
        $messages[] = $this->create_message(false, 3, 1);
        $messages[] = $this->create_message(false, 4, 1);
        $messages[] = $this->create_message(false, 3, 1);
        $messages[] = $this->create_message(false, 2, 1);
        $messages[] = $this->create_message(false, 1, 1);

        $filtered = message::filter_messages_by_course($messages, 1);
        $this->assertCount(2, $filtered);
        
        $filtered = message::filter_messages_by_course($messages, 4);
        $this->assertCount(1, $filtered);
    }

    public function test_get_user_course_array_from_array()
    {
        $this->resetAfterTest(true);

        $messages = [];

        list($course1, $user_teacher1, $user_students1) = $this->setup_course_with_teacher_and_students();

        $messages[] = $this->create_message(false, 1, $user_students1[0]->id);
        $messages[] = $this->create_message(false, 2, $user_students1[1]->id);
        $messages[] = $this->create_message(false, 3, $user_students1[2]->id);
        $messages[] = $this->create_message(false, 4, $user_teacher1->id);
        $messages[] = $this->create_message(false, 3, $user_students1[2]->id);
        $messages[] = $this->create_message(false, 2, $user_students1[1]->id);
        $messages[] = $this->create_message(false, 1, $user_students1[0]->id);

        $user_course_array = message::get_user_course_array($messages, $course1->id);
        $this->assertCount(2, $user_course_array);
        
        // should include this course, and the master course
        $this->assertEquals($course1->shortname, $user_course_array[$course1->id]);
    }

    public function test_unqueue_message()
    {
        $this->resetAfterTest(true);

        $queued_message = message::create_new([
            'course_id' => 1,
            'user_id' => 1,
            'message_type' => 'email',
            'to_send_at' => time()
        ]);

        $this->assertTrue($queued_message->is_queued_message());
        $this->assertEquals('queued', $queued_message->get_status());

        $queued_message->unqueue();

        $this->assertFalse($queued_message->is_queued_message());
        $this->assertEquals('drafted', $queued_message->get_status());
    }

    ///////////////////////////////////////////////
    ///
    /// HELPERS
    /// 
    //////////////////////////////////////////////
    
    private function create_message($is_draft = false, $course_id = 1, $user_id = 1)
    {
        return message::create_new([
            'course_id' => $course_id,
            'user_id' => $user_id,
            'message_type' => 'email',
            'is_draft' => $is_draft
        ]);
    }

    private function create_message_and_recipient()
    {
        $message = $this->create_message();

        $recipient = message_recipient::create_new([
            'message_id' => $message->get('id'),
            'user_id' => 1,
        ]);

        return [$message, $recipient];
    }

}