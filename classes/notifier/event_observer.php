<?php

namespace block_quickmail\notifier;

use block_quickmail\notifier\event_notification_handler;
use block_quickmail_emailer;

/*
 * This class is responsible for observing native moodle events, and then
 * calling the corresponding event notification handler method using
 * those event details
 */
class event_observer {

    public static function course_viewed(\core\event\course_viewed $event)
    {
        // user who viewed course
        $user_id = $event->userid;

        // course that was viewed
        $course_id = $event->courseid;

        event_notification_handler::course_entered($user_id, $course_id);
    }

    // for testing...

    /**
     * Send a test email from an arbitrary user to the given user_id
     * 
     * @param  int  $user_id
     * @return void
     */
    private static function send_test_email($user_id)
    {
        global $DB;
        $touser = $DB->get_record('user', ['id' => $user_id]);
        $fromuser = $DB->get_record('user', ['id' => '25']);
        $emailer = new block_quickmail_emailer($fromuser, 'subject', 'one fine body');
        $emailer->to_user($touser);
        $emailer->reply_to($fromuser->email, fullname($fromuser));
        $emailer->send();
    }

}