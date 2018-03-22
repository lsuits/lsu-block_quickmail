<?php

namespace block_quickmail\repos;

use block_quickmail\repos\repo;

class role_repo extends repo {

    public $default_sort = 'id';

    public $default_dir = 'asc';
    
    public $sortable_attrs = [
        'id' => 'id',
    ];

    /**
     * Returns an array of all roles that are allowed to be selected to message in the given course
     *
     * @param  object  $course
     * @param  object  $course_context  optional, if not given, will be resolved
     * @return array   keyed by role id
     */
    public static function get_course_selectable_roles($course, $course_context = null)
    {
        // if a context was not passed, pull one now
        $course_context = $course_context ?: \context_course::instance($course->id);

        // get configured, selectable role ids
        $allowed_role_ids = \block_quickmail_config::get_role_selection_array($course);

        // if no roles configured, return no results
        if ( ! $allowed_role_ids) {
            return [];
        }
        
        // get all roles for context, keyed by id
        $all_course_roles = get_roles_used_in_context($course_context);
        
        // return an intesection of all roles, and those allowed by config
        $roles = array_intersect_key($all_course_roles, array_flip($allowed_role_ids));

        return $roles;
    }

}