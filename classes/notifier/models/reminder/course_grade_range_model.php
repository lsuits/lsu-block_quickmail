<?php

namespace block_quickmail\notifier\models\reminder;

use block_quickmail\notifier\models\interfaces\reminder_notification_model_interface;
use block_quickmail\notifier\models\reminder_notification_model;
use block_quickmail\services\grade_calculator\course_grade_calculator;

class course_grade_range_model extends reminder_notification_model implements reminder_notification_model_interface {

    public static $object_type = 'course';
    
    public static $condition_keys = [
        'grade_greater_than',
        'grade_less_than',
    ];

    /**
     * Returns an array of user ids to be notified based on this reminder_notification_model's conditions
     * 
     * @return array
     */
    public function get_user_ids_to_notify()
    {
        // make sure a grade_greater_than boundary is set
        if ( ! $greater_than = $this->condition->get_value('grade_greater_than')) {
            $greater_than = 0;
        }

        // make sure a grade_less_than boundary is set
        if ( ! $less_than = $this->condition->get_value('grade_less_than')) {
            $less_than = 0; // ?
        }

        // get distinct user ids 
        // where users are in a specific course
        
        global $DB;

        $query_results = $DB->get_records_sql('SELECT u.id
            FROM {user} u
            INNER JOIN {user_enrolments} ue ON ue.userid = u.id
            INNER JOIN {enrol} e ON e.id = ue.enrolid
            INNER JOIN {course} c ON c.id = e.courseid
            INNER JOIN {role_assignments} ra ON ra.userid = u.id
            INNER JOIN {context} ctx ON ctx.id = ra.contextid AND ctx.instanceid = c.id
            WHERE ra.roleid IN (SELECT value FROM {config} WHERE name = "gradebookroles")
            AND c.id = ?
            GROUP BY u.id', [$this->get_course_id()]);

        $course_user_ids = array_keys($query_results);

        // set a default return container
        $results = [];

        // attempt to instantiate a grade calculator for this course, if cannot be
        // constructed, fail gracefully by returning empty results
        if ( ! $calculator = course_grade_calculator::for_course($this->get_course_id())) {
            return $results;
        }

        foreach ($course_user_ids as $user_id) {
            try {
                // fetch "round" grade for this course user
                $round_grade = $calculator->get_user_course_grade($user_id, 'round');

                // the user's calculated grade falls within the boundaries
                if ($round_grade >= $greater_than && $round_grade <= $less_than) {
                    // add to the results
                    $results[] = $user_id;
                }
            } catch (\Exception $e) {
                //
            }
        }

        return $results;
    }

}