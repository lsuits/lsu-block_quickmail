<?php

namespace block_quickmail\repos;

use block_quickmail\repos\repo;
use block_quickmail\persistents\message;

class sent_repo extends repo {

    public static $sortable_attrs = [
        'id' => [
            'key' => 'id',
            'type' => 'int',
        ],
        'course' => [
            'key' => 'course_id',
            'type' => 'int',
        ],
        'subject' => [
            'key' => 'subject',
            'type' => 'string',
        ],
        'sent' => [
            'key' => 'sent_at',
            'type' => 'int',
        ],
    ];

    /**
     * Returns all sent messages belonging to the given user id
     *
     * Optionally, can be scoped to a specific course if given a course_id
     * 
     * @param  int     $user_id
     * @param  int     $course_id   optional, defaults to 0 (all)
     * @param  string  $sort_by     optional, id|course|subject|sent
     * @param  string  $sort_dir    optional, asc|desc
     * @return array
     */
    public static function get_for_user($user_id, $course_id = 0, $sort_by = '', $sort_dir = '')
    {
        $sort_by = $sort_by ?: 'id';
        $sort_dir = $sort_dir ?: 'asc';

        $params['user_id'] = $user_id;

        if ($course_id) {
            $params['course_id'] = $course_id;
        }

        global $DB;
 
        $sql = 'SELECT DISTINCT m.*
                  FROM {block_quickmail_messages} m
                  WHERE m.user_id = :user_id';

        if ($course_id) {
            $sql .= ' AND m.course_id = :course_id';
        }
                  
        $sql .= ' AND m.is_draft = 0 AND m.timedeleted = 0 AND m.sent_at > 0';
     
        $collection = [];
     
        $recordset = $DB->get_recordset_sql($sql, $params);
        foreach ($recordset as $record) {
            $collection[] = new message(0, $record);
        }
        $recordset->close();

        self::sort_collection($collection, $sort_by, $sort_dir);

        return $collection;
    }

    /**
     * Returns all sent or queued, non-deleted, messages belonging to the given user id
     *
     * @param  int     $user_id
     * @return array
     */
    // public static function get_all_historical_for_user($user_id)
    // {
    //     global $DB;
 
    //     $sql = 'SELECT DISTINCT m.*
    //               FROM {' . static::TABLE . '} m
    //               WHERE m.user_id = :user_id
    //               AND m.is_draft = 0
    //               AND m.timedeleted = 0
    //               AND m.sent_at > 0
    //               OR m.user_id = :user_id2
    //               AND m.is_draft = 0
    //               AND m.timedeleted = 0
    //               AND m.sent_at = 0
    //               AND m.to_send_at > 0';
     
    //     $persistents = [];
     
    //     $recordset = $DB->get_recordset_sql($sql, ['user_id' => $user_id, 'user_id2' => $user_id]);
    //     foreach ($recordset as $record) {
    //         $persistents[] = new static(0, $record);
    //     }
    //     $recordset->close();
     
    //     return $persistents;
    // }

}