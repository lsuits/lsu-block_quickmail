<?php

namespace block_quickmail\repos;

use block_quickmail\repos\repo;
use block_quickmail\repos\interfaces\course_repo_interface;

class course_repo extends repo implements course_repo_interface {

    public $default_sort = 'id';

    public $default_dir = 'asc';
    
    public $sortable_attrs = [
        'id' => 'id',
    ];

    /**
     * Returns an array of all courses that the given user is enrolled in
     *
     * @param  object  $user
     * @param  bool    $active_only
     * @return array   [course id => course shortname]
     */
    public static function get_user_course_array($user, $active_only = false)
    {
        if ( ! $courses = enrol_get_all_users_courses($user->id, $active_only)) {
            return [];
        }

        return array_map(function ($course) {
            return $course->shortname;
        }, $courses);
    }

}