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
use block_quickmail\repos\interfaces\role_repo_interface;

class role_repo extends repo implements role_repo_interface {

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

    /**
     * Returns an array of role selection values
     *
     * Note: if a course or course id is given, the returned array will limit to course-level configuration,
     * otherwise, defaults to block-level configuration.
     * 
     * @param  mixed $courseorid
     * @return array  [role id => role name]
     */
    public static function get_alternate_email_role_selection_array($courseorid = null)
    {
        if ( ! $allowed_role_ids = \block_quickmail_config::get_role_selection_array($courseorid)) {
            return [];
        }

        global $DB;

        $roles = $DB->get_records('role', null, 'sortorder ASC');

        return array_reduce($roles, function ($carry, $role) use ($allowed_role_ids) {
            if (in_array($role->id, $allowed_role_ids)) {
                $carry[$role->id] = $role->name;
            }

            return $carry;
        }, []);
    }

    /**
     * Returns an array of role ids that are assigned to a given user id in a given course id
     * 
     * @param  int             $user_id
     * @param  int             $course_id
     * @param  context_course  $course_context   optional, will be fetched if not given
     * @return array
     */
    public static function get_user_roles_in_course($user_id, $course_id, $course_context = null)
    {
        // if a context was not passed, pull one now
        $course_context = $course_context ?: \context_course::instance($course_id);

        // get the user's roles in the course context (no parents)
        $roles = get_user_roles($course_context, $user_id, false);

        // return as a simple array of role ids
        return array_values(array_map(function($role) {
            return (int) $role->roleid;
        }, $roles));
    }

}