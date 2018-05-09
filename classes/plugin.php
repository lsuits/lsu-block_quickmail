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
     * Checks if the given user can send the given type of message in the given context, throwing an exception if not
     * 
     * @param  string  $send_type  broadcast|compose
     * @param  object  $user
     * @param  object  $context   an instance of a SYSTEM or COURSE context
     * @return void
     * @throws required_capability_exception
     */
    public static function require_user_can_send($send_type, $user, $context)
    {
        if ( ! self::user_can_send($send_type, $user, $context)) {
            $capability = $send_type == 'broadcast' ? 'myaddinstance' : 'cansend';

            throw new required_capability_exception($context, 'block/quickmail:' . $capability, 'nopermissions', '');
        }
    }

    /**
     * Checks if the given user has the given capability in the given context, throwing an exception if not
     * 
     * @param  string $capability
     * @param  mixed  $user
     * @param  object $context  an instance of a context
     * @return void
     * @throws required_capability_exception
     */
    public static function require_user_capability($capability, $user, $context)
    {
        if ( ! self::user_has_capability($capability, $user, $context)) {
            throw new required_capability_exception($context, 'block/quickmail:' . $capability, 'nopermissions', '');
        }
    }

    /**
     * Checks if the given user has the ability to message within the given course id
     * 
     * @param  object  $user
     * @param  int     $course_id
     * @return void
     * @throws required_capability_exception
     */
    public static function require_user_has_course_message_access($user, $course_id)
    {
        $send_type = $course_id == SITEID
            ? 'broadcast'
            : 'compose';

        $context = $send_type == 'broadcast'
            ? context_system::instance()
            : context_course::instance($course_id);

        self::require_user_can_send($send_type, $user, $context);
    }

    /**
     * Reports whether or not the given user can send the given type of message in the given context
     * 
     * @param  string  $send_type                broadcast|compose
     * @param  object  $user
     * @param  object  $context                  an instance of a SYSTEM or COURSE context
     * @param  bool    $include_student_access   if true (default), will check a course's "allowstudents" config as a last resort for access
     * @return bool
     */
    public static function user_can_send($send_type, $user, $context, $include_student_access = true)
    {
        // must be a valid send_type
        if ( ! in_array($send_type, ['broadcast', 'compose'])) {
            return false;
        }

        // if we're broadcasting, only allow admins
        if ($send_type == 'broadcast') {
            // make sure we have the correct context (system)
            if (get_class($context) !== 'context_system') {
                return false;
            }

            return self::user_has_capability('myaddinstance', $user, $context);
        }

        // otherwise, we're composing
        // make sure we have the correct context (course)
        if (get_class($context) !== 'context_course') {
            return false;
        }

        if (self::user_has_capability('cansend', $user, $context)) {
            return true;
        }
        
        // if we're checking for student access AND this course allows students to send
        if ($include_student_access && block_quickmail_config::course($context->instanceid, 'allowstudents')) {
            global $CFG;
            
            // iterate over system's "student" roles
            foreach (explode(',', $CFG->gradebookroles) as $role_id) {
                // if the user is associated with one of these roles in the (course) context
                if (user_has_role_assignment($user->id, $role_id, $context->id)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Reports whether or not the authenticated user has the given capability within the given context
     * 
     * @param  string $capability
     * @param  object $user
     * @param  object $context
     * @return bool
     */
    public static function user_has_capability($capability, $user, $context)
    {
        // always allow site admins
        // TODO: change this to a role capability?
        if (is_siteadmin($user)) {
            return true;
        }

        return has_capability('block/quickmail:' . $capability, $context, $user);
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