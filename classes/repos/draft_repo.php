<?php

namespace block_quickmail\repos;

use block_quickmail\repos\repo;
use block_quickmail\persistents\message;

class draft_repo extends repo {

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
        'created' => [
            'key' => 'timecreated',
            'type' => 'int',
        ],
        'modified' => [
            'key' => 'timemodified',
            'type' => 'int',
        ],
    ];

    /**
     * Fetches a draft message by id, or returns null
     * 
     * @param  int  $message_id
     * @return message|null
     */
    public static function find_or_null($message_id)
    {
        // first, try to find the message by id, returning null by default
        if ( ! $message = message::find_or_null($message_id)) {
            return null;
        }

        // if this message is NOT a draft, return null
        if ( ! $message->is_message_draft()) {
            return null;
        }

        return $message;
    }

    /**
     * Fetches a message by id which must belong to the given user id, or returns null
     * 
     * @param  integer $message_id
     * @param  integer $user_id
     * @return message|null
     */
    public static function find_for_user_or_null($message_id = 0, $user_id = 0)
    {
        // first, try to find the message by id, returning null by default
        if ( ! $message = self::find_or_null($message_id)) {
            return null;
        }

        // if this message does not belong to this user, return null
        if ( ! $message->is_owned_by_user($user_id)) {
            return null;
        }

        return $message;
    }

    /**
     * Fetches a message by id which must belong to the given user id, or returns null
     * 
     * @param  integer $message_id
     * @param  integer $user_id
     * @param  integer $course_id
     * @return message|null
     */
    public static function find_for_user_course_or_null($message_id = 0, $user_id = 0, $course_id = 0)
    {
        // first, try to find the message by id, returning null by default
        if ( ! $message = self::find_for_user_or_null($message_id, $user_id)) {
            return null;
        }

        // if this message does not belong to this course, return null
        if ( ! $message->is_owned_by_course($course_id)) {
            return null;
        }

        return $message;
    }

    /**
     * Returns all unsent, non-deleted, draft messages belonging to the given user id
     *
     * Optionally, can be scoped to a specific course if given a course_id
     * 
     * @param  int     $user_id
     * @param  int     $course_id   optional, defaults to 0 (all)
     * @param  string  $sort_by     optional, id|course|subject|created|modified
     * @param  string  $sort_dir    optional, asc|desc
     * @return array
     */
    public static function get_for_user($user_id, $course_id = 0, $sort_by = '', $sort_dir = '')
    {
        $sort_by = $sort_by ?: 'id';
        $sort_dir = $sort_dir ?: 'asc';

        $params = [
            'user_id' => $user_id, 
            'is_draft' => 1, 
            'sent_at' => 0, 
            'timedeleted' => 0
        ];

        if ($course_id) {
            $params['course_id'] = $course_id;
        }

        $collection = message::get_records($params);

        self::sort_collection($collection, $sort_by, $sort_dir);

        return $collection;
    }

}