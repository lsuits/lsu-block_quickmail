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

namespace block_quickmail\repos;

use block_quickmail\repos\repo;
use block_quickmail\repos\interfaces\draft_repo_interface;
use block_quickmail\persistents\message;

class draft_repo extends repo implements draft_repo_interface {

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
        // instantiate repo
        $repo = new self($params);

        // set params for db query
        $query_params = [
            'user_id' => $user_id, 
            'is_draft' => 1, 
            'sent_at' => 0, 
            'timedeleted' => 0
        ];

        // conditionally add course id to db query params if appropriate
        if ($course_id) {
            $query_params['course_id'] = $course_id;
        }

        // if not paginating, return all sorted results
        if ( ! $repo->paginate) {
            $data = message::get_records(
                $query_params,
                $repo->get_sort_column_name($repo->sort),
                strtoupper($repo->dir)
            );
        
        // otherwise, paginate and set the sorted results
        } else {
            // get total count of records (necessary for pagination)
            $count = message::count_records(
                $query_params
            );

            // get the calculated pagination parameters object
            $paginated = $repo->get_paginated($count);

            // set the pagination object on the result
            $repo->set_result_pagination($paginated);

            // pull the data with the validated pagination offset
            $data = message::get_records(
                $query_params,
                $repo->get_sort_column_name($repo->sort),
                strtoupper($repo->dir),
                $paginated->offset,
                $paginated->per_page
            );
        }

        $repo->set_result_data($data);

        return $repo->result;
    }

}