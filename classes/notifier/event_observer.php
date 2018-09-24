<?php

namespace block_quickmail\notifier;

use block_quickmail_emailer;

/*
 * This class is responsible for observing native moodle events, formatting the event data,
 * and then calling the appropriate event notification mapper function
 */
class event_observer {

    public static function course_viewed(\core\event\course_viewed $event)
    {
        // user who viewed course
        $user_id = $event->userid;

        // course that was viewed
        $course_id = $event->courseid;

        // POC
        global $DB;
        $touser = $DB->get_record('user', ['id' => $user_id]);
        $fromuser = $DB->get_record('user', ['id' => '25']);
        $emailer = new block_quickmail_emailer($fromuser, 'subject', 'one fine body');
        $emailer->to_user($touser);
        $emailer->reply_to($fromuser->email, fullname($fromuser));
        $emailer->send();
    }

}