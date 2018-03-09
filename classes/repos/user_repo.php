<?php

namespace block_quickmail\repos;

use block_quickmail\repos\repo;
use context_course;

class user_repo extends repo {

    public $default_sort = 'id';

    public $default_dir = 'asc';
    
    public $sortable_attrs = [
        'id' => 'id',
    ];

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
     * @param  integer $group_id
     * @param  boolean $active_only     whether or not to filter by active enrollment, defaults to true
     * @param  string  $user_fields     comma-separated list of fields to include in results, must be prefixed with "u."
     * @return array
     */
    public static function get_course_group_users($course_context, $group_id, $active_only = true, $user_fields = null)
    {
        return self::get_course_users($course_context, $active_only, $user_fields, $group_id);
    }

    /**
     * Get all users with a given role within a course
     * 
     * @param  object  $course_context  must be a course context
     * @param  integer $role_id
     * @param  boolean $active_only     whether or not to filter by active enrollment, defaults to true
     * @param  string  $user_fields     comma-separated list of fields to include in results, must be prefixed with "u."
     * @return array
     */
    public static function get_course_role_users($course_context, $role_id, $active_only = true, $user_fields = null)
    {
        // set fields to include
        $user_fields = ! empty($user_fields) ? $user_fields : 'u.id,u.firstname,u.lastname';

        $order_by = 'u.firstname ASC';

        $users = get_role_users($role_id, $course_context, false, $user_fields, $order_by, ! $active_only);

        return $users;
    }

    /**
     * Returns an array of unique user ids given arrays of included and excluded "entity ids"
     * 
     * @param  object  $course
     * @param  array   $included_entity_ids   [role_(role id), group_(group id), user_(user id)]
     * @param  array   $excluded_entity_ids   [role_(role id), group_(group id), user_(user id)]
     * @return array
     */
    public static function get_unique_course_user_ids_from_selected_entities($course, $included_entity_ids = [], $excluded_entity_ids = [])
    {
        $result_user_ids = [];

        // if none included, return no results
        if (empty($included_entity_ids)) {
            return $result_user_ids;
        }

        // make sure there are no duplicates in the incoming arrays
        $included_entity_ids = array_unique($included_entity_ids);
        $excluded_entity_ids = array_unique($excluded_entity_ids);

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

        // iterate through initial container of included/excluded role/group
        foreach (['included', 'excluded'] as $type) {
            foreach (['role', 'group'] as $name) {
                foreach ($filtered_entity_ids[$type][$name] as $name_id) {
                    
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
        $course_users = self::get_course_users($course_context);

        // convert these users to an array of ids
        $course_user_ids = array_map(function($user) {
            return $user->id;
        }, $course_users);

        //////////////////////////////////////////////////////////////
        /// ADD IN EACH EXPLICITLY INCLUDED/EXCLUDED USER TO THE APPROPRIATE CONTAINER
        //////////////////////////////////////////////////////////////

        foreach (['included', 'excluded'] as $type) {
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
        /// REMOVE ANY EXCLUDED USER IDS FROM THE INCLUDED USER IDS, CREATING A NEW FINAL CONTAINER
        //////////////////////////////////////////////////////////////

        $result_user_ids = array_filter($included_user_ids, function($id) use ($excluded_user_ids) {
            return ! in_array($id, $excluded_user_ids);
        });

        // return a unique list of user ids
        return array_unique($result_user_ids);
    }

}