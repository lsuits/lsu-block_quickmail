<?php

namespace block_quickmail\notifier;

use block_quickmail\persistents\event_notification;

/*
 * This class' methods are called from the event_observer based on the type of
 * event that occured. It is responsible for finding any relevant, active
 * event notifications, and triggering the notification to the appropriate user
 */
class event_notification_handler {

    public static function course_entered($user_id, $course_id)
    {
        // make sure the course is still active?
        // make sure the user is still active?
        // make sure the user is still enrolled in course?

        // get any relevant event notifications for this course
        $event_notifications = self::get_event_notifications_for_course('course-entered', $course_id);

        // attempt to notify each event notification, if appropriate
        foreach ($event_notifications as $event_notification) {
            $event_notification->notify($user_id);
        }
    }

    /**
     * Returns all active event notifications of the given model for the given course
     * 
     * @param  string  $model
     * @param  int     $course_id
     * @return array (event_notification)
     */
    private static function get_event_notifications_for_course($model, $course_id)
    {
        global $DB;

        $recordset = $DB->get_recordset_sql("
            SELECT en.* FROM {block_quickmail_event_notifs} en
            JOIN {block_quickmail_notifs} n on en.notification_id = n.id
            WHERE en.model = ? 
            AND n.course_id = ? 
            AND n.is_enabled = 1 
            AND n.timedeleted = 0", [$model, $course_id]);

        // iterate through recordset, instantiate persistents, add to array
        $data = [];
        foreach ($recordset as $record) {
            $data[] = new event_notification(0, $record);
        }
        $recordset->close();

        return $data;
    }

}