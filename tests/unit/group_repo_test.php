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