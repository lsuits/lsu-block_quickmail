<?php

use block_quickmail\repos\role_repo;
use block_quickmail\repos\group_repo;
use block_quickmail\repos\user_repo;

class block_quickmail_plugin {

    public static $name = 'block_quickmail';

    ////////////////////////////////////////////////////
    ///
    ///  AUTHORIZATION
    ///  
    ////////////////////////////////////////////////////

    /**
     * Reports whether or not the authenticated user has the given permission within the given context
     * 
     * @param  string $permission  allowalternate|canconfig|myaddinstance
     * @param  object $context
     * @param  object $user
     * @return bool
     */
    public static function user_has_capability($permission, $context, $user = null) {
        global $USER;

        // if no user was passed, set as the auth user
        $user = empty($user) ? $USER : $user;

        // always allow site admins
        if (is_siteadmin($user)) {
            return true;
        }

        return has_capability('block/quickmail:' . $permission, $context, $user);
    }

    /**
     * Reports whether or not the given user has the permission to access groups in the given context
     *
     * Note: User defaults to auth user
     * 
     * @param  object $context
     * @param  object $user
     * @return bool
     */
    public static function user_can_access_all_groups($context, $user = null) {
        return self::user_has_capability('viewgroupusers', $context, $user);
    }

    /**
     * Helper for checking if the given user has the given Quickmail permission within the given context
     *
     * Defaults to checking the auth user if no user is given
     * 
     * @param  string $permission  cansend|allowalternate|canconfig|myaddinstance
     * @param  object $context
     * @param  mixed  $user
     * @param  bool   $throw_exception   if false, returns a boolean response, otherwise, throws an exception (default)
     * @return mixed  always 'true' for admin users, boolean if not throwing exception
     * @throws Exception if unauthorized and set to throw exceptions
     */
    public static function require_user_capability($permission, $context, $user = null, $throw_exception = true) {
        // first, check for special cases...
        if ($permission == 'cansend' && block_quickmail_config::block('allowstudents')) {
            return true;
        }

        if ( ! $throw_exception) {
            try {
                return require_capability('block/quickmail:' . $permission, $context, $user);
            } catch (\Exception $e) {
                return false;
            }
        } else {
            return require_capability('block/quickmail:' . $permission, $context, $user);
        }
    }

    ////////////////////////////////////////////////////
    ///
    ///  COMPOSE PAGE DATA
    ///  
    ////////////////////////////////////////////////////

    /**
     * Returns an array of role/group/user data for a given course and context
     *
     * This is intended for feeding recipient data to the /compose.php page
     *
     * The returned array includes:
     * - all course roles [id => name]
     * - all course groups [id => name]
     * - all actively enrolled users [id => "fullname"]
     * 
     * @param  object  $course
     * @param  object  $user
     * @param  context $course_context
     * @return array
     */
    public static function get_compose_message_recipients($course, $user, $course_context) {

        // initialize a container for the collection of user data results
        $course_user_data = [
            'roles' => [],
            'groups' => [],
            'users' => [],
        ];
        
        ////////////
        /// ROLES
        ////////////
        
        // get all roles explicitly selectable for this user, allowing only those white-listed by config
        $roles = role_repo::get_course_selectable_roles($course, $course_context);

        // format and add each role to the results
        foreach ($roles as $role) {
            $course_user_data['roles'][] = [
                'id' => $role->id,
                'name' => $role->shortname,
            ];
        }

        ////////////
        /// GROUPS
        ////////////

        // get all groups explicitly selectable for this user
        $groups = group_repo::get_course_user_selectable_groups($course, $user, $course_context);

        // iterate through each group
        foreach ($groups as $group) {
            // add this group's data to the results container
            $course_user_data['groups'][] = [
                'id' => $group->id,
                'name' => $group->name,
            ];
        }

        ////////////
        /// USERS
        ////////////

        // get all users explicitly selectable for this user
        $users = user_repo::get_course_user_selectable_users($course, $user, $course_context);

        // add each user to the results collection
        foreach ($users as $user) {
            $course_user_data['users'][] = [
                'id' => $user->id,
                'name' => $user->firstname . ' ' . $user->lastname,
            ];
        }

        return $course_user_data;
    }

}