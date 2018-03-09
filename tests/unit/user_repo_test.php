<?php
 
require_once(dirname(__FILE__) . '/traits/unit_testcase_traits.php');

use block_quickmail\repos\user_repo;

class block_quickmail_user_repo_testcase extends advanced_testcase {
    
    use has_general_helpers,
        sets_up_courses;

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

        // get posted includs/excludes
        list($included_entity_ids, $excluded_entity_ids) = $this->get_post_scenario_one($enrolled_users, $groups);

        $user_ids = user_repo::get_unique_course_user_ids_from_selected_entities($course, $included_entity_ids, $excluded_entity_ids);

        $this->assertCount(23, $user_ids);
    }

    public function test_get_unique_course_user_ids_from_selected_entities_scenario_two()
    {
        $this->resetAfterTest(true);

        list($course, $course_context, $enrolled_users, $groups) = $this->create_course_with_users_and_groups();

        // get posted includs/excludes
        list($included_entity_ids, $excluded_entity_ids) = $this->get_post_scenario_two($enrolled_users, $groups);

        $user_ids = user_repo::get_unique_course_user_ids_from_selected_entities($course, $included_entity_ids, $excluded_entity_ids);

        $this->assertCount(10, $user_ids);
    }

    public function test_get_unique_course_user_ids_from_selected_entities_scenario_three()
    {
        $this->resetAfterTest(true);

        list($course, $course_context, $enrolled_users, $groups) = $this->create_course_with_users_and_groups();

        // get posted includs/excludes
        list($included_entity_ids, $excluded_entity_ids) = $this->get_post_scenario_three($enrolled_users);

        $user_ids = user_repo::get_unique_course_user_ids_from_selected_entities($course, $included_entity_ids, $excluded_entity_ids);

        $this->assertCount(13, $user_ids);
    }

    public function test_get_unique_course_user_ids_from_selected_entities_scenario_four()
    {
        $this->resetAfterTest(true);

        list($course, $course_context, $enrolled_users, $groups) = $this->create_course_with_users_and_groups();

        // get posted includs/excludes
        list($included_entity_ids, $excluded_entity_ids) = $this->get_post_scenario_four($enrolled_users);

        $user_ids = user_repo::get_unique_course_user_ids_from_selected_entities($course, $included_entity_ids, $excluded_entity_ids);

        $this->assertCount(15, $user_ids);
    }

    public function test_get_unique_course_user_ids_from_selected_entities_scenario_five()
    {
        $this->resetAfterTest(true);

        list($course, $course_context, $enrolled_users, $groups) = $this->create_course_with_users_and_groups();

        // get posted includs/excludes
        list($included_entity_ids, $excluded_entity_ids) = $this->get_post_scenario_five($enrolled_users, $groups);

        $user_ids = user_repo::get_unique_course_user_ids_from_selected_entities($course, $included_entity_ids, $excluded_entity_ids);

        $this->assertCount(15, $user_ids);
    }

    ///////////////////////////////////////////////
    ///
    /// HELPERS
    /// 
    //////////////////////////////////////////////
    
    /**
     * Returns a test course with:
     * - 1 editing teacher
     * - 3 teachers
     * - 40 students
     * - "red" group with 11 members (1 teacher, 10 students)
     * - "yellow" group with 15 members (1 teacher, 14 students)
     * - "blue" group with 15 members (1 teacher, 14 students)
     * 
     * @return array [course, course_context, users, groups]
     */
    private function create_course_with_users_and_groups()
    {
        // create course with enrolled users
        list($course, $course_context, $users) = $this->setup_course_with_users([
            'editingteacher' => 1,
            'teacher' => 3,
            'student' => 40,
        ]);

        $groups = [];

        $student_start = 1;

        foreach (['red', 'yellow', 'blue'] as $color) {
            // create a group 
            $groups[$color] = $this->getDataGenerator()->create_group([
                'courseid' => $course->id, 
                'name' => $color
            ]);

            // assign a unique teacher to the group
            $this->getDataGenerator()->create_group_member([
                'userid' => $users['teacher'][0]->id, 
                'groupid' => $groups[$color]->id]
            );

            // assign 10 unique users to the group
            foreach (range($student_start, $student_start + 9) as $i) {
                $this->getDataGenerator()->create_group_member([
                    'userid' => $users['student'][$i - 1]->id, 
                    'groupid' => $groups[$color]->id]
                );
            }

            $student_start += 10;
        }

        // assign first 4 students from group red into group yellow as well
        foreach (range(1, 4) as $i) {
            $this->getDataGenerator()->create_group_member([
                'userid' => $users['student'][$i - 1]->id, 
                'groupid' => $groups['yellow']->id]
            );
        }

        // assign first 4 students from group yellow into group blue as well
        foreach (range(11, 14) as $i) {
            $this->getDataGenerator()->create_group_member([
                'userid' => $users['student'][$i - 1]->id, 
                'groupid' => $groups['blue']->id]
            );
        }

        return [$course, $course_context, $users, $groups];
    }

    /**
     * This returns include and excluded "entity ids" (user_, role_, group_)
     *
     * This scenario will return included:
     * - 13 users (students)
     * - 1 group (yellow)
     *
     * - the yellow group contains 1 teacher & 10 students
     * - 8 of the explicity included students belong to the yellow group
     * - 1 additional student belongs to the yellow group
     *
     * This scenario will return excluded:
     * - none
     * 
     * This returns: 23 net included users
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
     * This returns: 10 net included users
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
     * This returns: 13 net included users
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