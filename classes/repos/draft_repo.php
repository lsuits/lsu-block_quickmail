<?php

namespace block_quickmail\repos;

use block_quickmail\repos\repo;
use block_quickmail\persistents\message;

class draft_repo extends repo {

    public $default_sort = 'created';

    public $default_dir = 'desc';
    
    public $sortable_attrs = [
        'id' => 'id',
        'course' => 'course_id',
        'subject' => 'subject',
        'created' => 'timecreated',
        'modified' => 'timemodified',
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
     * @param  array   $params  sort|dir|paginate|page|per_page|uri
     * @return mixed
     */
    public static function get_for_user($user_id, $course_id = 0, $params = [])
    {
        // set params
            // sort, must be in list, default id
            // dir, must asc|desc, default asc
            // paginate, default false
            // page, default 1
            // per_page, default 10
            // uri, default : $_SERVER['REQUEST_URI']

        $repo = new self($params);

        $query_params = [
            'user_id' => $user_id, 
            'is_draft' => 1, 
            'sent_at' => 0, 
            'timedeleted' => 0
        ];

        // conditionally add course id if appropriate
        if ($course_id) {
            $query_params['course_id'] = $course_id;
        }

        // if not paginating, return all results (sorted if necessary)
        if ( ! $repo->paginate) {
            $data = message::get_records(
                $query_params,
                $repo->get_sort_column_name($repo->sort),
                strtoupper($repo->dir)
            );
        } else {
            $count = message::count_records(
                $query_params
            );

            $paginator = $repo->make_paginator($count);
            
            var_dump($paginator);die;
        }

        $repo->set_result_data($data);

        return $repo->result;
    }

}