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
use block_quickmail\repos\interfaces\group_repo_interface;

class group_repo extends repo implements group_repo_interface {

    public $default_sort = 'id';

    public $default_dir = 'asc';
    
    public $sortable_attrs = [
        'id' => 'id',
    ];

    /**
     * Returns an array of all groups that are allowed to be selected to message in the given course by the given user
     *
     * @param  object  $course
     * @param  object  $user
     * @param  bool    $include_group_users    if true, group users will be return in the results
     * @param  object  $course_context  optional, if not given, will be resolved
     * @return array   keyed by group id
     */
    public static function get_course_user_selectable_groups($course, $user, $include_group_users = false, $course_context = null)
    {
        // if a context was not passed, pull one now
        $course_context = $course_context ?: \context_course::instance($course->id);

        // if user cannot access all groups in the course, and the course is set to be strict
        if ( ! \block_quickmail_plugin::user_has_capability('viewgroupusers', $user, $course_context) && \block_quickmail_config::be_ferpa_strict_for_course($course)) {
            // get this user's group associations, by groupings
            $grouping_array = groups_get_user_groups($course->id, $user->id);
            
            // transform this array to an array of groups
            $groups = self::transform_grouping_array_to_groups($grouping_array);

            // add a "members" property to the result objects to be consistent
            $groups = array_map(function($group) {
                $g = $group;
                $g->members = [];
                return $g;
            }, $groups);
        } else {
            $groups = array_map(function($group) use ($include_group_users) {
                $g = $group;
                $g->members = $include_group_users
                    ? array_keys(groups_get_members($group->id, 'u.id'))
                    : [];
                return $g;
            }, groups_get_all_groups($course->id));
        }

        return $groups;
    }

    /**
     * Returns an array of all groups that the given user is associated with in the given course
     *
     * @param  object  $course
     * @param  object  $user
     * @param  object  $course_context  optional, if not given, will be resolved
     * @return array   keyed by group id
     */
    public static function get_course_user_groups($course, $user, $course_context = null)
    {
        // get this user's group associations, by groupings
        $grouping_array = groups_get_user_groups($course->id, $user->id);
        
        // transform this array to an array of groups
        $groups = self::transform_grouping_array_to_groups($grouping_array);

        return $groups;
    }

    /**
     * Returns an array of groups given an array of groupings with nested groups
     * 
     * @param  array  $grouping_array
     * @return array  keyed by group id
     */
    private static function transform_grouping_array_to_groups($grouping_array)
    {
        if ( ! $grouping_array) {
            return [];
        }

        $group_ids = [];

        // iterate through each grouping
        foreach ($grouping_array as $grouping_group_array) {
            // extract only group ids
            $group_ids = array_map(function($group_id) {
                return $group_id;
            }, $grouping_group_array);
        }

        // reduce list down to unique group ids
        $group_ids = array_unique($group_ids);

        $groups = [];

        // iterate through each group id
        foreach ($group_ids as $group_id) {
            // pull the group object, adding it to the container
            $groups[$group_id] = groups_get_group($group_id);
        }

        return $groups;
    }

}