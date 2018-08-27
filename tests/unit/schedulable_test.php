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

use block_quickmail\persistents\schedule;
use block_quickmail\persistents\reminder_notification;
use block_quickmail\persistents\interfaces\schedulable_interface;

class block_quickmail_schedulable_testcase extends advanced_testcase {
    
    use has_general_helpers,
        sets_up_courses,
        sets_up_notifications;

    public function test_gets_schedule_from_schedulable()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);

        // create schedulable (reminder_notification)
        $schedulable = $this->create_test_schedulable_reminder_notification([
            'unit' => 'week',
            'amount' => 1,
            'begin_at' => $this->get_soon_time(),
            'end_at' => null,
        ]);

        $schedule = $schedulable->get_schedule();

        $this->assertInstanceOf(schedule::class, $schedule);
    }

    public function test_schedulable_getters()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);

        // create schedulable (reminder_notification) with default creation params
        $schedulable = $this->create_test_schedulable_reminder_notification([
            'unit' => 'week',
            'amount' => 1,
            'begin_at' => $this->get_soon_time(),
            'end_at' => $this->get_future_time(),
        ]);

        $this->assertNull($schedulable->get_last_run_time());
        $this->assertNull($schedulable->get_next_run_time());
        $this->assertFalse($schedulable->is_running());
    }

    public function test_sets_next_run_time_for_never_run_schedulable()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);

        $soon = $this->get_soon_time();

        // create schedulable (reminder_notification) with default creation params
        $schedulable = $this->create_test_schedulable_reminder_notification([
            'unit' => 'week',
            'amount' => 1,
            'begin_at' => $this->get_soon_time(),
            'end_at' => $this->get_future_time(),
        ]);

        $schedulable->set_next_run_time();

        $this->assertEquals($soon, $schedulable->get_next_run_time());
        $this->assertNull($schedulable->get_last_run_time());

        // attempt to set the next run time again, should not change
        $schedulable->set_next_run_time();

        $this->assertEquals($soon, $schedulable->get_next_run_time());
    }

    public function test_increments_next_run_time_for_non_expired_schedule()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);

        $begin = $this->get_timestamp_for_date('may 13 2018 08:30:00');

        // create schedulable (reminder_notification)
        $schedulable = $this->create_test_schedulable_reminder_notification([
            'unit' => 'week',
            'amount' => 1,
            'begin_at' => $begin,
            'end_at' => $this->get_timestamp_for_date('may 17 2020'),
        ]);

        $schedulable->set_next_run_time();
        $this->assertEquals($begin, $schedulable->get_next_run_time());

        // mark this schedulable as having been run once
        $lastrun = $this->get_timestamp_for_date('may 13 2018 09:00:00');
        $schedulable = $this->update_schedulable_reminder_notification_last_run_time($schedulable, $lastrun);
        $this->assertEquals($lastrun, $schedulable->get_last_run_time());

        $schedulable->set_next_run_time();

        // next run should be 1 week from begin time
        $nextrun = $this->get_timestamp_for_date('may 20 2018 08:30:00');

        $this->assertEquals($nextrun, $schedulable->get_next_run_time());
    }

    public function test_nulls_next_run_time_for_expired_schedule()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);

        $begin = $this->get_timestamp_for_date('may 5 2018 08:30:00');
        $end = $this->get_timestamp_for_date('may 10 2018 08:30:00');

        // create schedulable (reminder_notification)
        $schedulable = $this->create_test_schedulable_reminder_notification([
            'unit' => 'week',
            'amount' => 1,
            'begin_at' => $begin,
            'end_at' => $end,
        ]);

        $schedulable->set_next_run_time();
        $this->assertEquals($begin, $schedulable->get_next_run_time());

        // mark this schedulable as having been run once
        $lastrun = $this->get_timestamp_for_date('may 5 2018 09:00:00');
        $schedulable = $this->update_schedulable_reminder_notification_last_run_time($schedulable, $lastrun);

        $schedulable->set_next_run_time();

        // next run should be null since schedule has expired
        $this->assertNull($schedulable->get_next_run_time());
    }

    public function test_sets_next_run_time_when_created()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);

        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // create a reminder notification to run soon
        $reminder_notification = reminder_notification::create_type('course-non-participation', $course, $course, $user_teacher, $this->get_reminder_notification_params([], [
            'schedule_unit' => 'week',
            'schedule_amount' => 2,
            'schedule_begin_at' => $this->get_soon_time()
        ]));

        // next run time should be soon
        $this->assertEquals($reminder_notification->get_next_run_time(), $this->get_soon_time());

        // create a reminder notification to run past
        $reminder_notification = reminder_notification::create_type('course-non-participation', $course, $course, $user_teacher, $this->get_reminder_notification_params([], [
            'schedule_unit' => 'week',
            'schedule_amount' => 2,
            'schedule_begin_at' => $this->get_past_time()
        ]));

        // next run time should be past
        $this->assertEquals($reminder_notification->get_next_run_time(), $this->get_past_time());
    }

    ///////////////////////////////////////////////
    ///
    /// HELPERS
    /// 
    //////////////////////////////////////////////
    
    /**
     * Generates a reminder notification for an internally generated course/teacher with default params,
     * and explicit schedule params
     * 
     * @param  array  $schedule_params  (unit,amount,begin_at,end_at)
     * @return int  reminder_notification id
     */
    private function create_test_schedulable_reminder_notification($schedule_params)
    {
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        global $DB;

        $params = $this->get_reminder_notification_params();
        $now = time();

        // create the parent notification record
        $notification = new \stdClass();
        $notification->name = $params['name'];
        $notification->type = 'reminder';
        $notification->course_id = $course->id;
        $notification->user_id = $user_teacher->id;
        $notification->is_enabled = $params['is_enabled'];
        $notification->conditions = $params['conditions'];
        $notification->message_type = $params['message_type'];
        $notification->alternate_email_id = $params['alternate_email_id'];
        $notification->subject = $params['subject'];
        $notification->signature_id = $params['signature_id'];
        $notification->body = $params['body'];
        $notification->editor_format = $params['editor_format'];
        $notification->send_receipt = $params['send_receipt'];
        $notification->send_to_mentors = $params['send_to_mentors'];
        $notification->no_reply = $params['no_reply'];
        $notification->usermodified = $user_teacher->id;
        $notification->timecreated = $now;
        $notification->timemodified = $now;
        $notification->timedeleted = 0;
        $notification->no_reply = $params['no_reply'];
        $notification_id = $DB->insert_record('block_quickmail_notifs', $notification, true);

        // create the schedule record
        $schedule = new \stdClass();
        $schedule->unit = $schedule_params['unit'];
        $schedule->amount = $schedule_params['amount'];
        $schedule->begin_at = $schedule_params['begin_at'];
        $schedule->end_at = $schedule_params['end_at'];
        $schedule->usermodified = $user_teacher->id;
        $schedule->timecreated = $now;
        $schedule->timemodified = $now;
        $schedule->timedeleted = 0;
        $schedule_id = $DB->insert_record('block_quickmail_schedules', $schedule, true);

        // create the schedulable reminder notification record
        $schedulable = new \stdClass();
        $schedulable->notification_id = $notification_id;
        $schedulable->type = 'course-non-participation';
        $schedulable->object_id = $course->id;
        $schedulable->max_per_interval = $params['max_per_interval'];
        $schedulable->schedule_id = $schedule_id;
        $schedulable->last_run_at = null;
        $schedulable->next_run_at = null;
        $schedulable->usermodified = $user_teacher->id;
        $schedulable->timecreated = $now;
        $schedulable->timemodified = $now;
        $schedulable->timedeleted = 0;
        $schedulable_id = $DB->insert_record('block_quickmail_rem_notifs', $schedulable, true);

        return reminder_notification::find_or_null($schedulable_id);
    }

    public function update_schedulable_reminder_notification_last_run_time($schedulable, $timestamp)
    {
        $data = $schedulable->to_record();

        global $DB;

        $data->last_run_at = $timestamp;

        $DB->update_record('block_quickmail_rem_notifs', $data);

        return reminder_notification::find_or_null($data->id);
    }

}