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
use block_quickmail\repos\interfaces\user_repo_interface;
use context_course;
use block_quickmail\repos\role_repo;
use block_quickmail\repos\group_repo;
use block_quickmail_plugin;
use block_quickmail_config;
require_once($CFG->dirroot.'/user/profile/lib.php');

class user_repo extends repo implements user_repo_interface {

    public $default_sort = 'id';

    public $default_dir = 'asc';
    
    public $sortable_attrs = [
        'id' => 'id',
    ];

    /**
     * Returns an array of all users that are allowed to be selected to message in the given course by the given user
     *
     * @param  object  $course
     * @param  object  $user
     * @param  object  $course_context  optional, if not given, will be resolved
     * @return array   keyed by user id
     */
    public static function get_course_user_selectable_users($course, $user, $course_context = null)
    {
        // if a context was not passed, pull one now
        $course_context = $course_context ?: context_course::instance($course->id);

        // if user cannot access all groups in the course, and the course is set to be strict
        if ( ! block_quickmail_plugin::user_has_capability('viewgroupusers', $user, $course_context) && block_quickmail_config::be_ferpa_strict_for_course($course)) {
            // get all users with non-"group limited role"'s
            $allaccess_users = get_enrolled_users($course_context, 'moodle/site:accessallgroups', 0, 'u.*', null, 0, 0, true);

            // get the groups that this user is associated with
            $groups = group_repo::get_course_user_groups($course, $user, $course_context);

            $group_ids = array_keys($groups);

            // get all users within any groups the user belongs to
            $peer_users = self::get_course_group_users($course_context, $group_ids, true, 'u.*');

            $users = array_merge($allaccess_users, $peer_users);

            // be sure that we have unique users
            $users = array_unique($users, SORT_REGULAR);
        } else {
            // get all users in course
            $users = self::get_course_users($course_context);
        }
        
        return $users;
    }

    /**
     * Get all users within a course
     * 
     * @param  object  $course_context  must be a course context
     * @param  boolean $active_only     whether or not to filter by active enrollment, defaults to true
     * @param  string  $user_fields     comma-separated list of fields to include in results, must be prefixed with "u."
     * @param  integer $group_id        group id to filter by, should be left as default (0) for pulling all course users
     * @return array
     */
    public static function get_course_users($course_context, $active_only = true, $user_fields = null, $group_id = 0)
    {
        // set fields to include
        $user_fields = ! empty($user_fields) ? $user_fields : 'u.id,u.firstname,u.lastname';

        $users = get_enrolled_users($course_context, '', $group_id, $user_fields, null, 0, 0, $active_only);

        return $users;
    }

    /**
     * Get all users within a course group
     * 
     * @param  object  $course_context  must be a course context
     * @param  mixed   $group_id        a group id, or an array of group ids
     * @param  boolean $active_only     whether or not to filter by active enrollment, defaults to true
     * @param  string  $user_fields     comma-separated list of fields to include in results, must be prefixed with "u."
     * @return array   keyed by user id
     */
    public static function get_course_group_users($course_context, $group_id, $active_only = true, $user_fields = null)
    {
        // if this is a single group, return all users for the group
        if ( ! is_array($group_id)) {
            $group_users = self::get_course_users($course_context, $active_only, $user_fields, $group_id);

            $users = [];

            // rekey the returned array by user id
            foreach ($group_users as $group_user) {
                $users[$group_user->id] = $group_user;
            }
        
        // otherwise, get the unique users within the given list of group ids
        } else {
            $users = [];

            // for each given group id
            foreach ($group_id as $gid) {
                // pull the users within the group
                $group_users = self::get_course_users($course_context, $active_only, $user_fields, $gid);

                // add each to the container
                foreach ($group_users as $group_user) {
                    $users[$group_user->id] = $group_user;
                }

                // be sure we have a unique list of users (still necessary?)
                $users = array_unique($users, SORT_REGULAR);
            }
        }

        return $users;
    }

    /**
     * Get all users with a given role (or roles) within a given course
     * 
     * @param  object  $course_context  must be a course context
     * @param  mixed   $role_id         a role id, or an array of role ids
     * @param  boolean $active_only     whether or not to filter by active enrollment, defaults to true
     * @param  string  $user_fields     comma-separated list of fields to include in results, must be prefixed with "u."
     * @return array   keyed by user id
     */
    public static function get_course_role_users($course_context, $role_id, $active_only = true, $user_fields = null)
    {
        // set fields to include
        $user_fields = ! empty($user_fields) ? $user_fields : 'u.id,u.firstname,u.lastname';

        $order_by = 'u.firstname ASC';

        // if this is a single role, return all users for the role
        if ( ! is_array($role_id)) {
            // pull all
            $role_users = get_role_users($role_id, $course_context, false, $user_fields, $order_by, ! $active_only);

            $users = [];

            // rekey the returned array by user id
            foreach ($role_users as $role_user) {
                $users[$role_user->id] = $role_user;
            }
        
        // otherwise, get the unique users within the given list of role ids
        } else {
            $users = [];

            // for each given role id
            foreach ($role_id as $rid) {
                // pull the users within the role
                $role_users = get_role_users($rid, $course_context, false, $user_fields, $order_by, ! $active_only);

                // add each to the container
                foreach ($role_users as $role_user) {
                    $users[$role_user->id] = $role_user;
                }

                // be sure we have a unique list of users (still necessary?)
                $users = array_unique($users, SORT_REGULAR);
            }
        }

        return $users;
    }

    /**
     * Returns an array of unique user ids, "selectable" by the given user, given arrays of included and excluded "entity ids"
     * 
     * @param  object  $course
     * @param  object  $user
     * @param  array   $included_entity_ids   [role_(role id), group_(group id), user_(user id)]
     * @param  array   $excluded_entity_ids   [role_(role id), group_(group id), user_(user id)]
     * @return array
     */
    public static function get_unique_course_user_ids_from_selected_entities($course, $user, $included_entity_ids = [], $excluded_entity_ids = [])
    {
        $result_user_ids = [];

        // if none included, return no results
        if (empty($included_entity_ids)) {
            return $result_user_ids;
        }

        // make sure there are no duplicates in the incoming arrays
        $included_entity_ids = array_unique($included_entity_ids);
        $excluded_entity_ids = array_unique($excluded_entity_ids);

        // determine whether or not we're sending to all
        $sending_to_all = in_array('all', $included_entity_ids);

        // ignore "exclude all"
        if (($key = array_search('all', $excluded_entity_ids)) !== false) {
            unset($excluded_entity_ids[$key]);
        }

        //////////////////////////////////////////////////////////////
        /// CREATE A CONTAINER FOR INCLUDED/EXCLUDED ROLE/GROUP IDS
        //////////////////////////////////////////////////////////////

        $filtered_entity_ids = [
            'included' => [
                'role' => [],
                'group' => [],
            ],
            'excluded' => [
                'role' => [],
                'group' => [],
            ]
        ];

        //////////////////////////////////////////////////////////////
        /// EXTRACT TABLE IDS FOR ROLES/GROUPS, ADDING THEM TO THE CONTAINER
        //////////////////////////////////////////////////////////////

        // iterate through (included, excluded)
        foreach ($filtered_entity_ids as $type => $entity) {
            // if we're sending to all, do not worry about determining included ids
            if ($sending_to_all && $type == 'included') {
                continue;
            }

            // iterate through each entity name within this type (role, group)
            foreach ($entity as $name => $keys) {
                $type_key = $type . '_entity_ids';

                // get entity keys for this included/excluded role/group
                $entity_keys = array_filter($$type_key, function($key) use ($name) {
                    return strpos($key, $name . '_') === 0;
                });

                // remove entity name prefix and add to filtered results
                $filtered_entity_ids[$type][$name] = array_map(function($key) use ($name) {
                    return str_replace($name . '_', '', $key);
                }, $entity_keys);
            }
        }

        //////////////////////////////////////////////////////////////
        /// REMOVE ANY EXCLUDED ROLE/GROUP IDS FROM EXISTING INCLUDED ROLE/GROUP IDS IN THE CONTAINER
        //////////////////////////////////////////////////////////////

        // iterate through excluded entity names (role, group)
        foreach ($filtered_entity_ids['excluded'] as $name => $entity_keys) {
            // iterate through each value in this excluded role/group
            foreach ($entity_keys as $key_key => $key_value) {
                // if this excluded role/group value appears in the included container
                if (in_array($key_value, $filtered_entity_ids['included'][$name])) {
                    // get the array key within the included container
                    $included_key = array_search($key_value, $filtered_entity_ids['included'][$name]);
                    // remove this value from both the includes and excludes
                    unset($filtered_entity_ids['included'][$name][$included_key]);
                    // unset($filtered_entity_ids['excluded'][$name][$key_key]); // <-- this was causing problems, removing for now
                }
            }
        }

        // get course context for use in upcoming queries
        $course_context = context_course::instance($course->id);

        // create two new containers for final output of included/excluded user ids
        $included_user_ids = [];
        $excluded_user_ids = [];

        //////////////////////////////////////////////////////////////
        /// PULL ALL USERS FOR EACH INCLUDED/EXCLUDED ROLE/GROUP, ADDING THEM TO THE NEW CONTAINERS
        //////////////////////////////////////////////////////////////

        // if not sending to all, pull all selectable roles for the auth user if we're going to be including roles
        $selectable_role_ids = ! empty($filtered_entity_ids['included']['role']) && ! $sending_to_all
            ? array_keys(role_repo::get_course_selectable_roles($course, $course_context))
            : [];
        
        // if not sending to all, pull all selectable groups for the auth user if we're going to be including groups
        $selectable_group_ids = ! empty($filtered_entity_ids['included']['group']) && ! $sending_to_all
            ? array_keys(group_repo::get_course_user_selectable_groups($course, $user, false, $course_context))
            : [];

        // iterate through initial container of included/excluded role/group
        foreach (['included', 'excluded'] as $type) {
            // if we're sending to all, do not worry about determining included roles/groups
            if ($sending_to_all && $type == 'included') {
                continue;
            }

            foreach (['role', 'group'] as $name) {
                foreach ($filtered_entity_ids[$type][$name] as $name_id) {
                    // for inclusions, check that the role or group is selectable by the user
                    if ($type == 'included') {
                        // if this is a role but NOT selectable by this user
                        if ($name == 'role' && ! in_array($name_id, $selectable_role_ids)) {
                            continue;
                        
                        // otherwise, if this is a group but NOT selectable by this user
                        } else if ($name == 'group' && ! in_array($name_id, $selectable_group_ids)) {
                            continue;
                        }
                    }

                    // get all user for this included/excluded role/group, scoped to this course
                    $users = $name == 'role'
                        ? self::get_course_role_users($course_context, $name_id)
                        : self::get_course_group_users($course_context, $name_id);

                    // get appropriate name for the container to place these user ids within
                    $type_container = $type . '_user_ids';

                    // push these new user ids into the appropriate container
                    $$type_container = array_merge($$type_container, array_map(function($user) {
                        return $user->id;
                    }, $users));
                }
            }
        }

        // pull all course users for later use
        $course_users = self::get_course_user_selectable_users($course, $user, $course_context);

        // convert these users to an array of ids
        $course_user_ids = array_map(function($user) {
            return $user->id;
        }, $course_users);

        // if sending to all, add all course user ids to include user ids
        if ($sending_to_all) {
            $included_user_ids = $course_user_ids;
        }

        //////////////////////////////////////////////////////////////
        /// ADD IN EACH EXPLICITLY INCLUDED/EXCLUDED USER TO THE APPROPRIATE CONTAINER
        //////////////////////////////////////////////////////////////

        foreach (['included', 'excluded'] as $type) {
            // if we're sending to all, do not worry about determining included users
            if ($sending_to_all && $type == 'included') {
                continue;
            }

            // get name of appropriate (initial) container
            $type_key = $type . '_entity_ids';

            // extract only the user ids from the container
            $users = array_filter($$type_key, function($key) {
                return strpos($key, 'user_') === 0;
            });

            // filter out any explicitly included users that do not belong to this course
            if ($type == 'included') {
                $users = array_filter($users, function($user) use ($course_user_ids) {
                    return in_array(str_replace('user_', '', $user), $course_user_ids);
                });
            }

            // get name of appropriate output container
            $type_container = $type . '_user_ids';

            // push these user ids into the appropriate container
            $$type_container = array_merge($$type_container, array_map(function($user) {
                return str_replace('user_', '', $user);
            }, $users));
        }

        //////////////////////////////////////////////////////////////
        /// REMOVE ANY EXCLUDED USER IDS FROM THE INCLUDED USER IDS, CREATING A NEW CONTAINER
        //////////////////////////////////////////////////////////////

        $result_user_ids = array_filter($included_user_ids, function($id) use ($excluded_user_ids) {
            return ! in_array($id, $excluded_user_ids);
        });

        //////////////////////////////////////////////////////////////
        /// FINALLY, REMOVE ANY USER IDS THAT THIS USER MAY NOT MESSAGE
        //////////////////////////////////////////////////////////////

        return array_unique(array_intersect(array_map(function ($user) {
            return $user->id;
        }, $course_users), $result_user_ids));
    }

    /**
     * Returns an array of mentor users that are assigned to the given "mentee" user
     * 
     * @param  object  $user
     * @return array  keyed by user ids
     */
    public static function get_mentors_of_user($user)
    {
        global $DB;

        $result = $DB->get_records_sql('SELECT ra.userid as mentor_user_id FROM {context} c JOIN {role_assignments} ra on c.id = ra.contextid WHERE contextlevel = 30 AND instanceid = ?', [$user->id]);

        if ( ! $result) {
            return [];
        }

        return $DB->get_records_list('user', 'id', array_keys($result));
    }

}