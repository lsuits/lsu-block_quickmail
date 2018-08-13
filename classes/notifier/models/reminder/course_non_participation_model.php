<?php

namespace block_quickmail\notifier\models\reminder;

use block_quickmail\notifier\models\interfaces\reminder_notification_model_interface;
use block_quickmail\notifier\models\reminder_notification_model;

class course_non_participation_model extends reminder_notification_model implements reminder_notification_model_interface {

    public static $object_type = 'course';
    
    public static $condition_keys = [
        'time_amount',
        'time_unit',
    ];

    /**
     * Returns an array of user ids to be notified based on this reminder_notification_model's conditions
     * 
     * @return array
     */
    public function get_user_ids_to_notify()
    {
        // get distinct user ids 
        // where users are in a specific course
        // and where have not accessed the course since a conditionally set increment of time before now
        
        global $DB;

        $results = $DB->get_records_sql('SELECT u.id
            FROM {user} u
            INNER JOIN {user_enrolments} ue ON ue.userid = u.id
            INNER JOIN {enrol} e ON e.id = ue.enrolid
            INNER JOIN {course} c ON c.id = e.courseid
            INNER JOIN {role_assignments} ra ON ra.userid = u.id
            INNER JOIN {context} ctx ON ctx.id = ra.contextid AND ctx.instanceid = c.id
            WHERE u.id NOT IN (SELECT la.userid FROM {user_lastaccess} la WHERE la.courseid = c.id AND la.timeaccess > ?)
            AND ra.roleid IN (SELECT value FROM {config} WHERE name = "gradebookroles")
            AND c.id = ?
            GROUP BY u.id', [$this->condition->get_offset_timestamp_from_now('before'), $this->get_course_id()]);

        return array_keys($results);
    }

}