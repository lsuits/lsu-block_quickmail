<?php
 
require_once(dirname(__FILE__) . '/traits/unit_testcase_traits.php');

use block_quickmail\persistents\message;
use block_quickmail\persistents\message_recipient;
use block_quickmail\persistents\message_additional_email;

class block_quickmail_message_persistent_testcase extends advanced_testcase {
    
    use has_general_helpers,
        sets_up_courses;

    private function create_message()
    {
        return message::create_new([
            'course_id' => 1,
            'user_id' => 1,
            'message_type' => 'email',
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

    /////////////////////////////////////////////////////////////////////////////////

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

    public function sync_recipients()
    {
        //
    }

    public function sync_additional_emails()
    {
        //
    }

    public function find_draft_or_null()
    {
        //
    }
    
    public function find_user_draft_or_null()
    {
        //
    }
    
    public function find_user_course_draft_or_null()
    {
        //
    }
    
    public function get_all_unsent_drafts_for_user()
    {
        //
    }
    
    public function get_all_historical_for_user()
    {
        //
    }

}