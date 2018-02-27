<?php

namespace block_quickmail\repos;

use block_quickmail\repos\repo;
use block_quickmail\persistents\message;

class sent_repo extends repo {

    public $default_sort = 'sent';

    public $default_dir = 'desc';
    
    public $sortable_attrs = [
        'id' => 'id',
        'course' => 'course_id',
        'subject' => 'subject',
        'sent' => 'sent_at',
        'created' => 'timecreated',
        'modified' => 'timemodified',
    ];

    /**
     * Returns all sent messages belonging to the given user id
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
                  
        $sql .= ' AND m.is_draft = 0 AND m.timedeleted = 0 AND m.sent_at > 0 ORDER BY ' . $sort_by . ' ' . $sort_dir;

        return $sql;
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