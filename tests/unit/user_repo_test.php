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
 
require_once(dirname(__FILE__) . '/traits/unit_testcase_traits.php');

use block_quickmail\repos\user_repo;

class block_quickmail_user_repo_testcase extends advanced_testcase {
    
    use has_general_helpers,
        sets_up_courses,
        assigns_mentors;

    public function test_get_course_user_selectable_users()
    {
        $this->resetAfterTest(true);

        list($course, $course_context, $enrolled_users, $groups) = $this->create_course_with_users_and_groups();

        // manager: 0
        // coursecreator: 0
        // editingteacher: 1
        // teacher: 3
        // student: 40
        // guest: 0
        // user: 0
        // frontpage: 0
        $teacher = $enrolled_users['teacher'][0];

        // should have access to all users
        $editingteacher = $enrolled_users['editingteacher'][0];
        $student = $enrolled_users['student'][0];

        $users = user_repo::get_course_user_selectable_users($course, $editingteacher, $course_context);

        $first_user = current($users);

        $this->assertInternalType('array', $users);
        $this->assertCount(44, $users);
        $this->assertArrayHasKey($editingteacher->id, $users);
        $this->assertArrayHasKey($student->id, $users);
        $this->assertInternalType('object', $first_user);
        $this->assertObjectHasAttribute('id', $first_user);
        $this->assertObjectHasAttribute('firstname', $first_user);
        $this->assertObjectHasAttribute('lastname', $first_user);

        // should have limited access
        $users = user_repo::get_course_user_selectable_users($course, $student, $course_context);

        $this->assertInternalType('array', $users);
        $this->assertCount(22, $users);
    }

    public function test_get_course_users()
    {
        $this->resetAfterTest(true);

        list($course, $course_context, $enrolled_users, $groups) = $this->create_course_with_users_and_groups();
        
        $users = user_repo::get_course_users($course_context);

        $this->assertCount(44, $users);

        $first_user = reset($users);

        $this->assertObjectHasAttribute('id', $first_user);
        $this->assertObjectHasAttribute('firstname', $first_user);
        $this->assertObjectHasAttribute('lastname', $first_user);

        // create course with enrolled users
        list($course, $course_context, $users) = $this->setup_course_with_users();

        // get users from course with no users
        $users = user_repo::get_course_users($course_context);

        $this->assertCount(0, $users);
    }

    public function test_get_course_group_users()
    {
        $this->resetAfterTest(true);

        list($course, $course_context, $enrolled_users, $groups) = $this->create_course_with_users_and_groups();
        
        // get red group users
        $users = user_repo::get_course_group_users($course_context, $groups['red']->id);
        $this->assertCount(11, $users);

        $first_user = reset($users);

        $this->assertObjectHasAttribute('id', $first_user);
        $this->assertObjectHasAttribute('firstname', $first_user);
        $this->assertObjectHasAttribute('lastname', $first_user);

        // get yellow group users
        $users = user_repo::get_course_group_users($course_context, $groups['yellow']->id);
        $this->assertCount(15, $users);

        // get blue group users
        $users = user_repo::get_course_group_users($course_context, $groups['blue']->id);
        $this->assertCount(15, $users);

        // create course with enrolled users
        list($course, $course_context, $users) = $this->setup_course_with_users();

        // get users from non-existent group
        $users = user_repo::get_course_group_users($course_context, 123456);

        $this->assertCount(0, $users);
    }

    public function test_get_course_role_users()
    {
        $this->resetAfterTest(true);

        list($course, $course_context, $enrolled_users, $groups) = $this->create_course_with_users_and_groups();
        
        // editingteacher (id: 3)
        // teacher (id: 4)
        // student (id: 5)

        // get editingteacher users
        $users = user_repo::get_course_role_users($course_context, 3);
        $this->assertCount(1, $users);

        $first_user = reset($users);

        $this->assertObjectHasAttribute('id', $first_user);
        $this->assertObjectHasAttribute('firstname', $first_user);
        $this->assertObjectHasAttribute('lastname', $first_user);

        // get teacher users
        $users = user_repo::get_course_role_users($course_context, 4);
        $this->assertCount(3, $users);

        // get student users
        $users = user_repo::get_course_role_users($course_context, 5);
        $this->assertCount(40, $users);

        // create course with no enrolled users
        list($course, $course_context, $users) = $this->setup_course_with_users();

        // get editingteacher users
        $users = user_repo::get_course_role_users($course_context, 3);
        $this->assertCount(0, $users);

        // get teacher users
        $users = user_repo::get_course_role_users($course_context, 3);
        $this->assertCount(0, $users);

        // get student users
        $users = user_repo::get_course_role_users($course_context, 3);
        $this->assertCount(0, $users);
    }

    public function test_get_unique_course_user_ids_from_selected_entities_scenario_one()
    {
        $this->resetAfterTest(true);

        list($course, $course_context, $enrolled_users, $groups) = $this->create_course_with_users_and_groups();

        $editingteacher = $enrolled_users['editingteacher'][0];
        $student = $enrolled_users['student'][0];

        // get posted includs/excludes
        list($included_entity_ids, $excluded_entity_ids) = $this->get_post_scenario_one($enrolled_users, $groups);

        $user_ids = user_repo::get_unique_course_user_ids_from_selected_entities($course, $editingteacher, $included_entity_ids, $excluded_entity_ids);

        $this->assertCount(23, $user_ids);
    }

    public function test_get_unique_course_user_ids_from_selected_entities_scenario_two()
    {
        $this->resetAfterTest(true);

        list($course, $course_context, $enrolled_users, $groups) = $this->create_course_with_users_and_groups();

        $editingteacher = $enrolled_users['editingteacher'][0];
        $student = $enrolled_users['student'][0];

        // get posted includs/excludes
        list($included_entity_ids, $excluded_entity_ids) = $this->get_post_scenario_two($enrolled_users, $groups);

        $user_ids = user_repo::get_unique_course_user_ids_from_selected_entities($course, $editingteacher, $included_entity_ids, $excluded_entity_ids);

        $this->assertCount(10, $user_ids);
    }

    public function test_get_unique_course_user_ids_from_selected_entities_scenario_three()
    {
        $this->resetAfterTest(true);

        list($course, $course_context, $enrolled_users, $groups) = $this->create_course_with_users_and_groups();

        $editingteacher = $enrolled_users['editingteacher'][0];
        $student = $enrolled_users['student'][0];

        // get posted includs/excludes
        list($included_entity_ids, $excluded_entity_ids) = $this->get_post_scenario_three($enrolled_users);

        $user_ids = user_repo::get_unique_course_user_ids_from_selected_entities($course, $editingteacher, $included_entity_ids, $excluded_entity_ids);

        $this->assertCount(13, $user_ids);
    }

    public function test_get_unique_course_user_ids_from_selected_entities_scenario_four()
    {
        $this->resetAfterTest(true);

        list($course, $course_context, $enrolled_users, $groups) = $this->create_course_with_users_and_groups();

        $editingteacher = $enrolled_users['editingteacher'][0];
        $student = $enrolled_users['student'][0];

        // get posted includs/excludes
        list($included_entity_ids, $excluded_entity_ids) = $this->get_post_scenario_four($enrolled_users);

        $user_ids = user_repo::get_unique_course_user_ids_from_selected_entities($course, $editingteacher, $included_entity_ids, $excluded_entity_ids);

        $this->assertCount(15, $user_ids);
    }

    public function test_get_unique_course_user_ids_from_selected_entities_scenario_five()
    {
        $this->resetAfterTest(true);

        list($course, $course_context, $enrolled_users, $groups) = $this->create_course_with_users_and_groups();

        $editingteacher = $enrolled_users['editingteacher'][0];
        $student = $enrolled_users['student'][0];

        // get posted includs/excludes
        list($included_entity_ids, $excluded_entity_ids) = $this->get_post_scenario_five($enrolled_users, $groups);

        $user_ids = user_repo::get_unique_course_user_ids_from_selected_entities($course, $editingteacher, $included_entity_ids, $excluded_entity_ids);

        $this->assertCount(15, $user_ids);
    }

    public function test_get_mentors_of_user()
    {
        $this->resetAfterTest(true);

        list($course, $course_context, $enrolled_users, $groups) = $this->create_course_with_users_and_groups();
        
        // pick the first student to be the mentee
        $mentee_user = reset($enrolled_users['student']);

        // attempt to fetch all mentors of this mentee (should be none)
        $mentor_users = user_repo::get_mentors_of_user($mentee_user);

        $this->assertCount(0, $mentor_users);

        // create mentor for the mentee
        $mentor_user = $this->create_mentor_for_user($mentee_user);

        $mentor_users = user_repo::get_mentors_of_user($mentee_user);

        $this->assertCount(1, $mentor_users);

        $first_mentor = reset($mentor_users);

        $this->assertObjectHasAttribute('id', $first_mentor);
        $this->assertObjectHasAttribute('firstname', $first_mentor);
        $this->assertObjectHasAttribute('lastname', $first_mentor);
    }

    ///////////////////////////////////////////////
    ///
    /// HELPERS
    /// 
    //////////////////////////////////////////////

    // first 4 students from group "red" are in group "yellow" as well
    // first 4 students from group "yellow" are in group "blue" as well

    /**
     * This returns include and excluded "entity ids" (user_, role_, group_)
     *
     * This scenario will return included:
     * - 16 users (students)
     * - 1 group (yellow)
     * --- 15 users (1 teacher, 14 students)
     * --- 8 students already included
     *
     * This scenario will return excluded:
     * - none
     */
    private function get_post_scenario_one($enrolled_users, $groups)
    {
        $included_entity_ids = [];
        $excluded_entity_ids = [];
        
        // INCLUDES

        // include a specified amount of students from each group
        $offset = 0;
        foreach ([3, 5, 8] as $amount) {
            // pull amount of students from group
            $included_students = array_slice($enrolled_users['student'], $offset, $amount);

            // push with user_ prefix into included
            $included_entity_ids = array_merge($included_entity_ids, array_map(function($user) {
                return 'user_' . $user->id;
            }, $included_students));

            // skip to next group
            $offset += 10;
        }

        // include yellow group
        $included_entity_ids = array_merge($included_entity_ids, ['group_' . $groups['yellow']->id]);

        return [$included_entity_ids, $excluded_entity_ids];
    }

    /**
     * This returns include and excluded "entity ids" (user_, role_, group_)
     *
     * This scenario will return included:
     * - 12 users (students)
     * - 1 group (red)
     * --- 15 users (1 teacher, 14 students)
     * --- 8 students already included
     *
     * This scenario will return excluded:
     * - none
     */
    private function get_post_scenario_two($enrolled_users, $groups)
    {
        $included_entity_ids = [];
        $excluded_entity_ids = [];
        
        // INCLUDES

        // include a specified amount of students from each group
        $offset = 0;
        foreach ([9, 1, 3] as $amount) {
            // pull amount of students from group
            $included_students = array_slice($enrolled_users['student'], $offset, $amount);

            // push with user_ prefix into included
            $included_entity_ids = array_merge($included_entity_ids, array_map(function($user) {
                return 'user_' . $user->id;
            }, $included_students));

            // skip to next group
            $offset += 10;
        }

        // include red group
        $included_entity_ids = array_merge($included_entity_ids, ['group_' . $groups['red']->id]);

        // include blue group
        $included_entity_ids = array_merge($included_entity_ids, ['group_' . $groups['blue']->id]);

        // EXCLUDES

        // exclude red group
        $excluded_entity_ids = array_merge($excluded_entity_ids, ['group_' . $groups['red']->id]);

        // exclude yellow group
        $excluded_entity_ids = array_merge($excluded_entity_ids, ['group_' . $groups['yellow']->id]);

        return [$included_entity_ids, $excluded_entity_ids];
    }

    /**
     * This returns include and excluded "entity ids" (user_, role_, group_)
     *
     * This scenario will return included:
     * - 12 users (students)
     * - 1 role (editing teacher)
     * --- 1 user
     *
     * This scenario will return excluded:
     * - none
     */
    private function get_post_scenario_three($enrolled_users)
    {
        $included_entity_ids = [];
        $excluded_entity_ids = [];
        
        // INCLUDES

        // include a specified amount of students from each group
        $offset = 0;
        foreach ([2, 4, 6] as $amount) {
            // pull amount of students from group
            $included_students = array_slice($enrolled_users['student'], $offset, $amount);

            // push with user_ prefix into included
            $included_entity_ids = array_merge($included_entity_ids, array_map(function($user) {
                return 'user_' . $user->id;
            }, $included_students));

            // skip to next group
            $offset += 10;
        }

        // include editingteacher role
        $included_entity_ids = array_merge($included_entity_ids, ['role_3']);

        return [$included_entity_ids, $excluded_entity_ids];
    }

    /**
     * This returns: 15 net included users
     */
    private function get_post_scenario_four($enrolled_users)
    {
        $included_entity_ids = [];
        $excluded_entity_ids = [];
        
        // INCLUDES

        // include a specified amount of students from each group
        $offset = 0;
        foreach ([2, 4, 6] as $amount) {
            // pull amount of students from group
            $included_students = array_slice($enrolled_users['student'], $offset, $amount);

            // push with user_ prefix into included
            $included_entity_ids = array_merge($included_entity_ids, array_map(function($user) {
                return 'user_' . $user->id;
            }, $included_students));

            // skip to next group
            $offset += 10;
        }

        // include editingteacher role
        $included_entity_ids = array_merge($included_entity_ids, ['role_3']);
        
        // include teacher role
        $included_entity_ids = array_merge($included_entity_ids, ['role_4']);

        // EXCLUDES

        // exclude editingteacher role
        $excluded_entity_ids = array_merge($excluded_entity_ids, ['role_3']);

        return [$included_entity_ids, $excluded_entity_ids];
    }

    /**
     * This returns: 15 net included users
     */
    private function get_post_scenario_five($enrolled_users, $groups)
    {
        $included_entity_ids = [];
        $excluded_entity_ids = [];
        
        // INCLUDES

        // include a specified amount of students from each group
        $offset = 0;
        foreach ([3, 10, 3] as $amount) {
            // pull amount of students from group
            $included_students = array_slice($enrolled_users['student'], $offset, $amount);

            // push with user_ prefix into included
            $included_entity_ids = array_merge($included_entity_ids, array_map(function($user) {
                return 'user_' . $user->id;
            }, $included_students));

            // skip to next group
            $offset += 10;
        }

        // include teacher role
        $included_entity_ids = array_merge($included_entity_ids, ['role_4']);

        // EXCLUDES

        // exclude editingteacher role
        $excluded_entity_ids = array_merge($excluded_entity_ids, ['role_3']);

        // exclude red group
        $excluded_entity_ids = array_merge($excluded_entity_ids, ['group_' . $groups['red']->id]);

        return [$included_entity_ids, $excluded_entity_ids];
    }

}