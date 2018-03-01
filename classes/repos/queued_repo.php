<?php

namespace block_quickmail\repos;

use block_quickmail\repos\repo;
use block_quickmail\persistents\message;

class queued_repo extends repo {

    public $default_sort = 'created';

    public $default_dir = 'desc';
    
    public $sortable_attrs = [
        'id' => 'id',
        'course' => 'course_id',
        'subject' => 'subject',
        'created' => 'timecreated',
        'scheduled' => 'to_send_at',
    ];

    /**
     * Fetches a queued message by id, or returns null
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

        // if this message is NOT a queued message, return null
        if ( ! $message->is_queued_message()) {
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
     * Returns all queued messages belonging to the given user id
     *
     * Optionally, can be scoped to a specific course if given a course_id
     * 
     * @param  int     $user_id
     * @param  int     $course_id   optional, defaults to 0 (all)
     * @param  array   $params  sort|dir|paginate|page|per_page|uri
     * @return array
     */
    public static function get_for_user($user_id, $course_id = 0, $params = [])
    {
        // instantiate repo
        $repo = new self($params);
        $sort_by = $repo->get_sort_column_name($repo->sort);
        $sort_dir = strtoupper($repo->dir);

        // set params for db query
        $query_params = [
            'user_id' => $user_id, 
        ];

        // conditionally add course id to db query params if appropriate
        if ($course_id) {
            $query_params['course_id'] = $course_id;
        }
        
        global $DB;

        // if not paginating, return all sorted results
        if ( ! $repo->paginate) {
            // get SQL given params
            $sql = self::get_for_user_sql($course_id, $sort_by, $sort_dir, false);

            // pull data, iterate through recordset, instantiate persistents, add to array
            $data = [];
            $recordset = $DB->get_recordset_sql($sql, $query_params);
            foreach ($recordset as $record) {
                $data[] = new message(0, $record);
            }
            $recordset->close();
        } else {
            // get (count) SQL given params
            $sql = self::get_for_user_sql($course_id, $sort_by, $sort_dir, true);
         
            // pull count
            $count = $DB->count_records_sql($sql, $query_params);
            
            // get the calculated pagination parameters object
            $paginated = $repo->get_paginated($count);

            // set the pagination object on the result
            $repo->set_result_pagination($paginated);

            // get SQL given params
            $sql = self::get_for_user_sql($course_id, $sort_by, $sort_dir, false);
         
            // pull data, iterate through recordset, instantiate persistents, add to array
            $data = [];
            $recordset = $DB->get_recordset_sql($sql, $query_params, $paginated->offset, $paginated->per_page);
            foreach ($recordset as $record) {
                $data[] = new message(0, $record);
            }
            $recordset->close();
        }

        $repo->set_result_data($data);

        return $repo->result;
    }

    private static function get_for_user_sql($course_id, $sort_by, $sort_dir, $as_count = false)
    {
        $sql = $as_count
            ? 'SELECT COUNT(DISTINCT m.id) '
            : 'SELECT DISTINCT m.* ';

        $sql .= 'FROM {block_quickmail_messages} m
                  WHERE m.user_id = :user_id';

        if ($course_id) {
            $sql .= ' AND m.course_id = :course_id';
        }
                  
        $sql .= ' AND m.to_send_at <> 0 AND m.timedeleted = 0 AND m.sent_at = 0 ORDER BY ' . $sort_by . ' ' . $sort_dir;

        return $sql;
    }

}