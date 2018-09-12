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
use block_quickmail\repos\interfaces\notification_repo_interface;
use block_quickmail\persistents\notification;

class notification_repo extends repo implements notification_repo_interface {

    public $default_sort = 'type';

    public $default_dir = 'desc';
    
    public $sortable_attrs = [
        'id' => 'id',
        'type' => 'type',
        'name' => 'name',
        'enabled' => 'is_enabled',
        'created' => 'timecreated',
    ];

    /**
     * Returns all notifications belonging to the given course id
     *
     * @param  int     $course_id
     * @param  mixed   $user_id     if given, scopes notification to this user id
     * @param  array   $params  sort|dir|paginate|page|per_page|uri
     * @return array
     */
    public static function get_all_for_course($course_id, $user_id = null, $params = [])
    {
        // instantiate repo
        $repo = new self($params);
        $sort_by = $repo->get_sort_column_name($repo->sort);
        $sort_dir = strtoupper($repo->dir);

        global $DB;

        $query_params['course_id'] = $course_id;

        // conditionally add user id as param
        if (is_numeric($user_id)) {
            $query_params['user_id'] = $user_id;
        }

        // if not paginating, return all sorted results
        if ( ! $repo->paginate) {
            // get SQL given params
            $sql = self::get_all_for_course_sql($course_id, $user_id, $sort_by, $sort_dir, false);

            // pull data, iterate through recordset, instantiate persistents, add to array
            $data = [];
            $recordset = $DB->get_recordset_sql($sql, $query_params);
            foreach ($recordset as $record) {
                $data[] = new notification(0, $record);
            }
            $recordset->close();
        } else {
            // get (count) SQL given params
            $sql = self::get_all_for_course_sql($course_id, $user_id, $sort_by, $sort_dir, true);
         
            // pull count
            $count = $DB->count_records_sql($sql, $query_params);
            
            // get the calculated pagination parameters object
            $paginated = $repo->get_paginated($count);

            // set the pagination object on the result
            $repo->set_result_pagination($paginated);

            // get SQL given params
            $sql = self::get_all_for_course_sql($course_id, $user_id, $sort_by, $sort_dir, false);
         
            // pull data, iterate through recordset, instantiate persistents, add to array
            $data = [];
            $recordset = $DB->get_recordset_sql($sql, $query_params, $paginated->offset, $paginated->per_page);
            foreach ($recordset as $record) {
                $data[] = new notification(0, $record);
            }
            $recordset->close();
        }

        $repo->set_result_data($data);

        return $repo->result;
    }

    /**
     * Returns a notification which must belong to the given course and user ids, returns null if no record
     * 
     * @param  int $notification_d
     * @param  int $course_id
     * @param  int $user_id
     * @return notification|null
     */
    public static function get_notification_for_course_user_or_null($notification_id, $course_id, $user_id)
    {
        if ( ! $notification = notification::get_record([
            'id' => $notification_id, 
            'course_id' => $course_id, 
            'user_id' => $user_id, 
            'timedeleted' => 0
        ])) {
            return null;
        }

        return $notification;
    }

    private static function get_all_for_course_sql($course_id, $user_id = null, $sort_by, $sort_dir, $as_count = false)
    {
        $sql = $as_count
            ? 'SELECT COUNT(DISTINCT n.id) '
            : 'SELECT DISTINCT n.* ';

        $sql .= 'FROM {block_quickmail_notifs} n
                  WHERE n.course_id = :course_id';

        if (is_numeric($user_id)) {
            $sql .= ' AND n.user_id = :user_id';
        }

        $sql .= ' AND n.timedeleted = 0 
                 ORDER BY ' . $sort_by . ' ' . $sort_dir;

        return $sql;
    }

}