<?php
 
require_once(dirname(__FILE__) . '/traits/unit_testcase_traits.php');

use block_quickmail\repos\group_repo;

class block_quickmail_group_repo_testcase extends advanced_testcase {
    
    use has_general_helpers,
        sets_up_courses;

    public function test_get_course_user_selectable_groups()
    {
        $this->resetAfterTest(true);

        list($course, $course_context, $enrolled_users, $course_groups) = $this->create_course_with_users_and_groups();

        $red_group = $course_groups['red'];
        $yellow_group = $course_groups['yellow'];
        $blue_group = $course_groups['blue'];
        
        // should have access to all three groups
        $editingteacher = $enrolled_users['editingteacher'][0];

        $groups = group_repo::get_course_user_selectable_groups($course, $editingteacher);

        $first_group = current($groups);

        $this->assertInternalType('array', $groups);
        $this->assertCount(3, $groups);
        $this->assertArrayHasKey($red_group->id, $groups);
        $this->assertArrayHasKey($yellow_group->id, $groups);
        $this->assertArrayHasKey($blue_group->id, $groups);
        $this->assertInternalType('object', $first_group);
        $this->assertObjectHasAttribute('id', $first_group);
        $this->assertObjectHasAttribute('name', $first_group);

        $student = $enrolled_users['student'][0];

        // should have access to only two groups
        $groups = group_repo::get_course_user_selectable_groups($course, $student);

        $this->assertInternalType('array', $groups);
        $this->assertCount(2, $groups);
        $this->assertArrayHasKey($red_group->id, $groups);
        $this->assertArrayHasKey($yellow_group->id, $groups);
        $this->assertArrayNotHasKey($blue_group->id, $groups);
    }

    public function test_get_course_user_groups()
    {
        $this->resetAfterTest(true);

        list($course, $course_context, $enrolled_users, $course_groups) = $this->create_course_with_users_and_groups();

        $red_group = $course_groups['red'];
        $yellow_group = $course_groups['yellow'];
        $blue_group = $course_groups['blue'];
        
        // should not be in any groups
        $editingteacher = $enrolled_users['editingteacher'][0];

        $groups = group_repo::get_course_user_groups($course, $editingteacher, $course_context);

        $this->assertInternalType('array', $groups);
        $this->assertCount(0, $groups);

        $student = $enrolled_users['student'][0];

        // should have access to only two groups (red and yellow)
        $groups = group_repo::get_course_user_groups($course, $student, $course_context);

        $first_group = current($groups);

        $this->assertInternalType('array', $groups);
        $this->assertCount(2, $groups);
        $this->assertArrayHasKey($red_group->id, $groups);
        $this->assertArrayHasKey($yellow_group->id, $groups);
        $this->assertArrayNotHasKey($blue_group->id, $groups);
        $this->assertInternalType('object', $first_group);
        $this->assertObjectHasAttribute('id', $first_group);
        $this->assertObjectHasAttribute('name', $first_group);

        $student = $enrolled_users['student'][38];

        // should not be in any groups
        $groups = group_repo::get_course_user_groups($course, $student, $course_context);

        $first_group = current($groups);

        $this->assertInternalType('array', $groups);
        $this->assertCount(0, $groups);
    }

}