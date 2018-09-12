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

use block_quickmail\repos\draft_repo;
use block_quickmail\persistents\message;
use block_quickmail\repos\pagination\paginated;

class block_quickmail_draft_repo_testcase extends advanced_testcase {
    
    use has_general_helpers,
        sets_up_courses;

    public function test_find_or_null()
    {
        $this->resetAfterTest(true);

        $draft = $this->create_message(true);

        $found_draft = draft_repo::find_or_null($draft->get('id'));

        $this->assertInstanceOf(message::class, $found_draft);

        $message = $this->create_message();

        $not_found_draft = draft_repo::find_or_null($message->get('id'));

        $this->assertNull($not_found_draft);
    }

    public function test_find_for_user_or_null()
    {
        $this->resetAfterTest(true);

        $draft = $this->create_message(true);

        $found_draft = draft_repo::find_for_user_or_null($draft->get('id'), 1);

        $this->assertInstanceOf(message::class, $found_draft);

        $different_user_draft = draft_repo::find_for_user_or_null($draft->get('id'), 2);

        $this->assertNull($different_user_draft);

        $different_message_id_draft = draft_repo::find_for_user_or_null($draft->get('id') + 1, 1);

        $this->assertNull($different_message_id_draft);

        $message = $this->create_message(false);

        $not_found_message = draft_repo::find_for_user_or_null($message->get('id'), 1);

        $this->assertNull($not_found_message);
    }

    public function test_find_for_user_course_or_null()
    {
        $this->resetAfterTest(true);

        $draft = $this->create_message(true);

        $found_draft = draft_repo::find_for_user_course_or_null($draft->get('id'), 1, 1);

        $this->assertInstanceOf(message::class, $found_draft);

        $different_user_draft = draft_repo::find_for_user_course_or_null($draft->get('id'), 2, 1);

        $this->assertNull($different_user_draft);

        $different_message_id_draft = draft_repo::find_for_user_course_or_null($draft->get('id') + 1, 1, 1);

        $this->assertNull($different_message_id_draft);

        $message = $this->create_message(false);

        $different_course_draft = draft_repo::find_for_user_course_or_null($draft->get('id'), 1, 2);

        $this->assertNull($different_course_draft);

        $message = $this->create_message(false);

        $not_found_message = draft_repo::find_for_user_course_or_null($message->get('id'), 1, 1);

        $this->assertNull($not_found_message);
    }

    
    public function test_get_for_user()
    {
        $this->resetAfterTest(true);

        // create 3 drafts for user id: 1
        $draft1 = $this->create_message(true);
        $draft2 = $this->create_message(true);
        $draft3 = $this->create_message(true);
        
        // create 2 drafts for user id: 2
        $draft4 = $this->create_message(true);
        $draft4->set('user_id', 2);
        $draft4->update();
        $draft5 = $this->create_message(true);
        $draft5->set('user_id', 2);
        $draft5->update();

        // create a non-draft message for user id: 1
        $draft6 = $this->create_message();

        // create a soft-deleted message for user id: 1
        $draft7 = $this->create_message(true);
        $draft7->soft_delete();

        // create a message for user: 1, course: 2
        $draft8 = $this->create_message(true);
        $draft8->set('course_id', 2);
        $draft8->update();

        // get all drafts for user: 1
        $drafts = draft_repo::get_for_user(1, 0);

        $this->assertCount(4, $drafts->data);

        // get all drafts for user: 1, course: 1
        $drafts = draft_repo::get_for_user(1, 1);

        $this->assertCount(3, $drafts->data);

        // get all drafts for user: 1, course: 2
        $drafts = draft_repo::get_for_user(1, 2);

        $this->assertCount(1, $drafts->data);
    }

    public function test_sorts_get_for_user()
    {
        $this->resetAfterTest(true);

        $this->create_test_drafts();

        // get all drafts for user: 1
        $drafts = draft_repo::get_for_user(1, 0);
        $this->assertCount(7, $drafts->data);
        $this->assertEquals('date', $drafts->data[0]->get('subject'));

        // sort by id
        $drafts = draft_repo::get_for_user(1, 0, [
            'sort' => 'id',
            'dir' => 'asc'
        ]);
        $this->assertEquals(144000, $drafts->data[0]->get('id'));

        $drafts = draft_repo::get_for_user(1, 0, [
            'sort' => 'id',
            'dir' => 'desc'
        ]);
        $this->assertEquals(144006, $drafts->data[0]->get('id'));

        // sort by course
        $drafts = draft_repo::get_for_user(1, 0, [
            'sort' => 'course',
            'dir' => 'asc'
        ]);
        $this->assertEquals(1, $drafts->data[0]->get('course_id'));

        $drafts = draft_repo::get_for_user(1, 0, [
            'sort' => 'course',
            'dir' => 'desc'
        ]);
        $this->assertEquals(5, $drafts->data[0]->get('course_id'));

        // sort by subject
        $drafts = draft_repo::get_for_user(1, 0, [
            'sort' => 'subject',
            'dir' => 'asc'
        ]);
        $this->assertEquals('apple', $drafts->data[0]->get('subject'));

        $drafts = draft_repo::get_for_user(1, 0, [
            'sort' => 'subject',
            'dir' => 'desc'
        ]);
        $this->assertEquals('grape', $drafts->data[0]->get('subject'));

        // sort by (time) created
        $drafts = draft_repo::get_for_user(1, 0, [
            'sort' => 'created',
            'dir' => 'asc'
        ]);
        $this->assertEquals(1111111111, $drafts->data[0]->get('timecreated'));

        $drafts = draft_repo::get_for_user(1, 0, [
            'sort' => 'created',
            'dir' => 'desc'
        ]);
        $this->assertEquals(8888888888, $drafts->data[0]->get('timecreated'));

        // sort by (time) modified
        $drafts = draft_repo::get_for_user(1, 0, [
            'sort' => 'modified',
            'dir' => 'asc'
        ]);
        $this->assertEquals(1010101010, $drafts->data[0]->get('timemodified'));

        $drafts = draft_repo::get_for_user(1, 0, [
            'sort' => 'modified',
            'dir' => 'desc'
        ]);
        $this->assertEquals(5454545454, $drafts->data[0]->get('timemodified'));
    }

    public function test_sorts_get_for_user_and_course()
    {
        $this->resetAfterTest(true);

        $this->create_test_drafts();

        // get all drafts for user: 1, course: 1
        $drafts = draft_repo::get_for_user(1, 1);
        $this->assertCount(4, $drafts->data);
        $this->assertEquals('date', $drafts->data[0]->get('subject'));

        // sort by id
        $drafts = draft_repo::get_for_user(1, 1, [
            'sort' => 'id',
            'dir' => 'asc'
        ]);
        $this->assertEquals(144000, $drafts->data[0]->get('id'));

        $drafts = draft_repo::get_for_user(1, 1, [
            'sort' => 'id',
            'dir' => 'desc'
        ]);
        $this->assertEquals(144006, $drafts->data[0]->get('id'));

        // sort by course
        $drafts = draft_repo::get_for_user(1, 1, [
            'sort' => 'course',
            'dir' => 'asc'
        ]);
        $this->assertEquals(1, $drafts->data[0]->get('course_id'));

        $drafts = draft_repo::get_for_user(1, 1, [
            'sort' => 'course',
            'dir' => 'desc'
        ]);
        $this->assertEquals(1, $drafts->data[0]->get('course_id'));

        // sort by subject
        $drafts = draft_repo::get_for_user(1, 1, [
            'sort' => 'subject',
            'dir' => 'asc'
        ]);
        $this->assertEquals('apple', $drafts->data[0]->get('subject'));

        $drafts = draft_repo::get_for_user(1, 1, [
            'sort' => 'subject',
            'dir' => 'desc'
        ]);
        $this->assertEquals('fig', $drafts->data[0]->get('subject'));

        // sort by (time) created
        $drafts = draft_repo::get_for_user(1, 1, [
            'sort' => 'created',
            'dir' => 'asc'
        ]);
        $this->assertEquals(1111111111, $drafts->data[0]->get('timecreated'));

        $drafts = draft_repo::get_for_user(1, 1, [
            'sort' => 'created',
            'dir' => 'desc'
        ]);
        $this->assertEquals(8888888888, $drafts->data[0]->get('timecreated'));

        // sort by (time) modified
        $drafts = draft_repo::get_for_user(1, 1, [
            'sort' => 'modified',
            'dir' => 'asc'
        ]);
        $this->assertEquals(1010101010, $drafts->data[0]->get('timemodified'));

        $drafts = draft_repo::get_for_user(1, 1, [
            'sort' => 'modified',
            'dir' => 'desc'
        ]);
        $this->assertEquals(5454545454, $drafts->data[0]->get('timemodified'));
    }

    public function test_gets_paginated_results_for_user()
    {
        $this->resetAfterTest(true);

        // create 30 drafts for user id: 1
        foreach (range(1, 30) as $i) {
            $this->create_message(true);
        }

        // get all drafts for user: 1
        $drafts = draft_repo::get_for_user(1, 0, [
            'sort' => 'id',
            'dir' => 'asc',
            'paginate' => true,
            'page' => '2',
            'per_page' => '4',
            'uri' => '/blocks/quickmail/sent.php?courseid=7&sort=subject&dir=asc',
        ]);

        $this->assertCount(4, $drafts->data);
        $this->assertInstanceOf(paginated::class, $drafts->pagination);
        $this->assertEquals(8, $drafts->pagination->page_count);
        $this->assertEquals(4, $drafts->pagination->offset);
        $this->assertEquals(4, $drafts->pagination->per_page);
        $this->assertEquals(2, $drafts->pagination->current_page);
        $this->assertEquals(3, $drafts->pagination->next_page);
        $this->assertEquals(1, $drafts->pagination->previous_page);
        $this->assertEquals(30, $drafts->pagination->total_count);
        $this->assertEquals('/blocks/quickmail/sent.php?courseid=7&sort=subject&dir=asc&page=2', $drafts->pagination->uri_for_page);
        $this->assertEquals('/blocks/quickmail/sent.php?courseid=7&sort=subject&dir=asc&page=1', $drafts->pagination->first_page_uri);
        $this->assertEquals('/blocks/quickmail/sent.php?courseid=7&sort=subject&dir=asc&page=8', $drafts->pagination->last_page_uri);
        $this->assertEquals('/blocks/quickmail/sent.php?courseid=7&sort=subject&dir=asc&page=3', $drafts->pagination->next_page_uri);
        $this->assertEquals('/blocks/quickmail/sent.php?courseid=7&sort=subject&dir=asc&page=1', $drafts->pagination->previous_page_uri);
    }

    ///////////////////////////////////////////////
    ///
    /// HELPERS
    /// 
    //////////////////////////////////////////////
    
    private function create_message($is_draft = false)
    {
        return message::create_new([
            'course_id' => 1,
            'user_id' => 1,
            'message_type' => 'email',
            'is_draft' => $is_draft
        ]);
    }

    private function create_test_drafts()
    {
        global $DB;

        // id: 144000
        $draft1 = $this->create_message(true);
        $draft1->set('course_id', 1);
        $draft1->set('subject', 'date');
        $draft1->update();
        $draft = $draft1->to_record();
        $draft->timecreated = 8888888888;
        $draft->timemodified = 3232323232;
        $DB->update_record('block_quickmail_messages', $draft);

        // id: 144001
        $draft2 = $this->create_message(true);
        $draft2->set('course_id', 5);
        $draft2->set('subject', 'elderberry');
        $draft2->update();
        $draft = $draft2->to_record();
        $draft->timecreated = 4444444444;
        $draft->timemodified = 5252525252;
        $DB->update_record('block_quickmail_messages', $draft);

        // id: 144002
        $draft3 = $this->create_message(true);
        $draft3->set('course_id', 3);
        $draft3->set('subject', 'coconut');
        $draft3->update();
        $draft = $draft3->to_record();
        $draft->timecreated = 7777777777;
        $draft->timemodified = 1919191919;
        $DB->update_record('block_quickmail_messages', $draft);

        // id: 144003
        $draft4 = $this->create_message(true);
        $draft4->set('course_id', 1);
        $draft4->set('subject', 'apple');
        $draft4->update();
        $draft = $draft4->to_record();
        $draft->timecreated = 1111111111;
        $draft->timemodified = 5454545454;
        $DB->update_record('block_quickmail_messages', $draft);

        // id: 144004
        $draft5 = $this->create_message(true);
        $draft5->set('course_id', 1);
        $draft5->set('subject', 'banana');
        $draft5->update();
        $draft = $draft5->to_record();
        $draft->timecreated = 2222222222;
        $draft->timemodified = 3333333333;
        $DB->update_record('block_quickmail_messages', $draft);

        // id: 144005
        $draft6 = $this->create_message(true);
        $draft6->set('course_id', 2);
        $draft6->set('subject', 'grape');
        $draft6->update();
        $draft = $draft6->to_record();
        $draft->timecreated = 1212121212;
        $draft->timemodified = 2525252525;
        $DB->update_record('block_quickmail_messages', $draft);

        // id: 144006
        $draft7 = $this->create_message(true);
        $draft7->set('course_id', 1);
        $draft7->set('subject', 'fig');
        $draft7->update();
        $draft = $draft7->to_record();
        $draft->timecreated = 3434343434;
        $draft->timemodified = 1010101010;
        $DB->update_record('block_quickmail_messages', $draft);
    }

}