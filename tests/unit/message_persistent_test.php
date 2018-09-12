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

use block_quickmail_string;
use block_quickmail\persistents\message;
use block_quickmail\persistents\message_draft_recipient;
use block_quickmail\persistents\message_recipient;
use block_quickmail\persistents\message_additional_email;

class block_quickmail_message_persistent_testcase extends advanced_testcase {
    
    use has_general_helpers,
        sets_up_courses,
        sets_up_notifications;

    public function test_create_composed_with_recipients_as_draft()
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
            'mentor_copy' => 1,
        ];

        $message = message::create_type('compose', $user_teacher, $course, (object) $params, true);

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
        $this->assertEquals($params['mentor_copy'], $message->get('send_to_mentors'));
        $this->assertEquals(1, $message->get('is_draft'));
        $this->assertCount(2, $message->get_substitution_code_classes());
    }

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
        $this->assertEquals(date('Y-m-d g:i a', $message->get('timecreated')), $message->get_readable_created_at());
        $this->assertEquals(date('Y-m-d g:i a', $message->get('timemodified')), $message->get_readable_last_modified_at());
        $this->assertEquals(block_quickmail_string::get('never'), $message->get_readable_sent_at());
        $this->assertEquals(block_quickmail_string::get('never'), $message->get_readable_to_send_at());
    }

    public function test_find_owned_by_user_or_null()
    {
        $this->resetAfterTest(true);

        $user_one = $this->getDataGenerator()->create_user();
        $user_two = $this->getDataGenerator()->create_user();
        
        $user_one_draft = $this->create_message(true, 1, $user_one->id);
        $user_one_sent = $this->create_message(false, 2, $user_one->id);
        $user_two_draft = $this->create_message(true, 3, $user_two->id);
        $user_two_sent = $this->create_message(false, 2, $user_two->id);

        $my_message = message::find_owned_by_user_or_null($user_one_draft->get('id'), $user_one->id);

        $this->assertInstanceOf(message::class, $my_message);
        $this->assertEquals($user_one_draft->get('id'), $my_message->get('id'));

        $not_my_message = message::find_owned_by_user_or_null($user_one_sent->get('id'), $user_two->id);

        $this->assertNull($not_my_message);
    }

    public function test_get_all_message_recipients()
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
        $message_recipient_array = $message->get_message_recipients('all', true);

        $this->assertCount(3, $message_recipients);
        $this->assertInstanceOf(message_recipient::class, $message_recipients[0]);
        $this->assertCount(3, $message_recipient_array);
        $this->assertEquals($user_two->id, $message_recipient_array[1]);
    }

    public function test_get_message_recipients_by_status()
    {
        $this->resetAfterTest(true);

        $message = $this->create_message();

        // create an unsent-to recip
        $user_one = $this->getDataGenerator()->create_user();
        $one = message_recipient::create_new([
            'message_id' => $message->get('id'),
            'user_id' => $user_one->id,
        ]);

        // create an sent-to recip
        $user_two = $this->getDataGenerator()->create_user();
        $two = message_recipient::create_new([
            'message_id' => $message->get('id'),
            'user_id' => $user_two->id,
            'sent_at' => time()
        ]);

        // create an unsent-to recip
        $user_three = $this->getDataGenerator()->create_user();
        $three = message_recipient::create_new([
            'message_id' => $message->get('id'),
            'user_id' => $user_three->id,
        ]);

        $message_recipients = $message->get_message_recipients('unsent');
        $message_recipient_array = $message->get_message_recipients('unsent', true);

        $this->assertCount(2, $message_recipients);
        $this->assertInstanceOf(message_recipient::class, $message_recipients[0]);
        $this->assertCount(2, $message_recipient_array);
        $this->assertEquals($user_three->id, $message_recipient_array[1]);

        $message_recipients = $message->get_message_recipients('sent');
        $message_recipient_array = $message->get_message_recipients('sent', true);

        $this->assertCount(1, $message_recipients);
        $this->assertInstanceOf(message_recipient::class, $message_recipients[0]);
        $this->assertCount(1, $message_recipient_array);
        $this->assertEquals($user_two->id, $message_recipient_array[0]);
    }

    public function test_get_message_recipient_users()
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

        $message_recipient_users = $message->get_message_recipient_users('all', 'id,email');

        $this->assertInternalType('array', $message_recipient_users);
        $this->assertCount(3, $message_recipient_users);
        $this->assertEquals($user_one->id, $message_recipient_users[$user_one->id]->id);
        $this->assertEquals($user_one->email, $message_recipient_users[$user_one->id]->email);
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

        $original_recipient_array = $message->get_message_recipients('all', true);
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

        $new_recipient_array = $message->get_message_recipients('all', true);
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

    public function test_sync_compose_draft_recipients()
    {
        $this->resetAfterTest(true);

        $message = $this->create_message();

        // create some includes and excludes (with some invalid)
        $includes = [
            'not_good',
            'role_1',
            'role_a',
            'role_3',
            'group_2',
            'group_4',
            'user_11',
            'user_15',
            'something_else'
        ];

        $excludes = [
            'role_1',
            'role_2',
            'group_2',
            'group_45',
            'user_19',
            'user_15',
            'invalid_key'
        ];

        $message->sync_compose_draft_recipients($includes, $excludes);
        
        $count = message_draft_recipient::get_records(['message_id' => $message->get('id')]);
        
        $this->assertCount(12, $count);

        $draft_recipients = $message->get_message_draft_recipients();

        $this->assertCount(12, $draft_recipients);

        $first = $draft_recipients[0];

        $this->assertInstanceOf(message_draft_recipient::class, $first);
        $this->assertEquals('include', $first->get('type'));
        $this->assertEquals('role', $first->get('recipient_type'));
        $this->assertEquals('1', $first->get('recipient_id'));
        $this->assertNotNull($first->get('timecreated'));
        $this->assertNotNull($first->get('timemodified'));

        message_draft_recipient::clear_all_for_message($message);

        $count = message_draft_recipient::get_records(['message_id' => $message->get('id')]);
        
        $this->assertCount(0, $count);

        $message = $this->create_message();

        // create some includes and excludes for this new message
        $includes = [
            'role_1',
            'role_3',
            'group_2',
            'group_4',
            'user_11',
            'user_15',
        ];

        $excludes = [
            'role_1',
            'role_2',
            'group_2',
            'group_45',
            'user_19',
            'user_15',
            'invalid_key'
        ];

        $message->sync_compose_draft_recipients($includes, $excludes);
        
        $count = message_draft_recipient::get_records(['message_id' => $message->get('id')]);

        $this->assertCount(12, $count);

        // create some different includes and excludes for this same message
        $includes = [
            'role_2',
            'role_3',
            'group_1',
            'user_17',
            'user_18',
        ];

        $excludes = [
            'role_5',
            'role_not_good',
            'user_19',
            'user_15',
            'invalid_key'
        ];

        $message->sync_compose_draft_recipients($includes, $excludes);

        $count = message_draft_recipient::get_records(['message_id' => $message->get('id')]);

        $this->assertCount(8, $count);
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
        $this->assertEquals(block_quickmail_string::get('drafted'), $message->get_status());
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
        $this->assertEquals(block_quickmail_string::get('queued'), $message->get_status());
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
        $this->assertEquals(block_quickmail_string::get('sending'), $message->get_status());
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
        $this->assertEquals(block_quickmail_string::get('sent'), $message->get_status());
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

    public function test_create_composed_with_no_recipients_as_draft()
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
            'mentor_copy' => 1,
        ];

        $message = message::create_type('compose', $user_teacher, $course, (object) $params, true);

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
        $this->assertEquals($params['mentor_copy'], $message->get('send_to_mentors'));
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
            'mentor_copy' => 1,
        ];

        $message = message::create_type('compose', $user_teacher, $course, (object) $params, false);

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
            'mentor_copy' => 1,
        ];

        $message = message::create_type('compose', $user_teacher, $course, (object) $creation_params, true);

        $update_params = [
            'message_type' => 'email',
            'alternate_email_id' => 5,
            'signature_id' => 7,
            'subject' => 'an updated subject is here',
            'message' => 'the updated message',
            'receipt' => 1,
            'to_send_at' => 1518124011,
            'no_reply' => 0,
            'mentor_copy' => 0,
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
        $this->assertEquals($update_params['mentor_copy'], $updated_message->get('send_to_mentors'));
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
            'mentor_copy' => 0,
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
        $this->assertEquals(block_quickmail_string::get('queued'), $queued_message->get_status());

        $queued_message->unqueue();

        $this->assertFalse($queued_message->is_queued_message());
        $this->assertEquals(block_quickmail_string::get('drafted'), $queued_message->get_status());
    }

    public function test_creates_message_from_reminder_notification()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
        
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $params = [
            'name' => 'My Reminder Notification',
            'schedule_unit' => 'week',
            'schedule_amount' => 1,
            'schedule_begin_at' => time(),
            'schedule_end_at' => null,
            'max_per_interval' => 0,
            'message_type' => 'email',
            'subject' => 'This is the subject',
            'body' => 'This is the body',
            'is_enabled' => 1,
            'alternate_email_id' => 0,
            'signature_id' => 0,
            'editor_format' => 1,
            'send_receipt' => 0,
            'send_to_mentors' => 0,
            'no_reply' => 1,
            'conditions' => '',
            'condition_time_amount' => 4,
            'condition_time_unit' => 'day',
        ];

        $reminder_notification = $this->create_reminder_notification_for_course_user('course-non-participation', $course, $user_teacher, null, $params);

        $notification = $reminder_notification->get_notification();

        $message = message::create_from_notification($notification, []);

        $this->assertInstanceOf(message::class, $message);
        $this->assertEquals($course->id, $message->get('course_id'));
        $this->assertEquals($user_teacher->id, $message->get('user_id'));
        $this->assertEquals($params['message_type'], $message->get('message_type'));
        $this->assertEquals($params['alternate_email_id'], $message->get('alternate_email_id'));
        $this->assertEquals($params['signature_id'], $message->get('signature_id'));
        $this->assertEquals($params['subject'], $message->get('subject'));
        $this->assertEquals($params['body'], $message->get('body'));
        $this->assertEquals($params['send_receipt'], $message->get('send_receipt'));
        // $this->assertEquals($params['to_send_at'], $message->get('to_send_at'));
        $this->assertEquals($params['no_reply'], $message->get('no_reply'));
        $this->assertEquals($params['send_to_mentors'], $message->get('send_to_mentors'));
        $this->assertEquals(0, $message->get('is_draft'));
        // $this->assertCount(2, $message->get_substitution_code_classes());
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