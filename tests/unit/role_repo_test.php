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

use block_quickmail\repos\role_repo;

class block_quickmail_role_repo_testcase extends advanced_testcase {
    
    use has_general_helpers,
        sets_up_courses;

    public function test_get_course_selectable_roles()
    {
        $this->resetAfterTest(true);

        // create course with enrolled users
        list($course, $course_context, $users) = $this->setup_course_with_users([
            'editingteacher' => 1,
            'teacher' => 3,
            'student' => 40,
        ]);

        $roles = role_repo::get_course_selectable_roles($course);

        $this->assertCount(3, $roles);
        $this->assertInternalType('array', $roles);
        $this->assertArrayHasKey(3, $roles);
        $this->assertInternalType('object', $roles[3]);
        $this->assertObjectHasAttribute('id', $roles[3]);
        $this->assertObjectHasAttribute('name', $roles[3]);
        $this->assertObjectHasAttribute('shortname', $roles[3]);

        // update the course's settings to exclude editingteacher
        $new_params = [
            'allowstudents' => '1',
            'roleselection' => '4,5',
            'receipt' => '1',
            'prepend_class' => 'fullname',
            'ferpa' => 'noferpa',
            'downloads' => '1',
            'additionalemail' => '1',
            'default_message_type' => 'email',
            'message_types_available' => 'email',
        ];

        // update the courses config
        block_quickmail_config::update_course_config($course, $new_params);

        $roles = role_repo::get_course_selectable_roles($course);

        $this->assertCount(2, $roles);
        $this->assertInternalType('array', $roles);
        $this->assertArrayHasKey(4, $roles);
        $this->assertInternalType('object', $roles[4]);
        $this->assertObjectHasAttribute('id', $roles[4]);
        $this->assertObjectHasAttribute('name', $roles[4]);
        $this->assertObjectHasAttribute('shortname', $roles[4]);
    }

    public function test_get_alternate_email_role_selection_array()
    {
        $this->resetAfterTest(true);

        $role_selection_array = role_repo::get_alternate_email_role_selection_array();

        $this->assertCount(3, $role_selection_array);
        $this->assertInternalType('array', $role_selection_array);
        $this->assertArrayHasKey(3, $role_selection_array);
        $this->assertInternalType('string', $role_selection_array[3]);

        // create course with enrolled users
        list($course, $course_context, $users) = $this->setup_course_with_users([
            'editingteacher' => 1,
            'teacher' => 3,
            'student' => 40,
        ]);

        // update the course's settings to exclude editingteacher
        $new_params = [
            'roleselection' => '4,5',
        ];

        // update the courses config
        block_quickmail_config::update_course_config($course, $new_params);

        $role_selection_array = role_repo::get_alternate_email_role_selection_array($course);

        $this->assertCount(2, $role_selection_array);
    }

    public function test_get_user_role_id_array_in_course()
    {
        $this->resetAfterTest(true);

        // create course with enrolled users
        list($course, $course_context, $users) = $this->setup_course_with_users([
            'editingteacher' => 1, // role id: 3
            'teacher' => 1, // role id: 4
            'student' => 1, // role id: 5
        ]);

        $editingteacher = $users['editingteacher'][0];
        $teacher = $users['teacher'][0];
        $student = $users['student'][0];

        // editingteacher
        $role_ids = role_repo::get_user_roles_in_course($editingteacher->id, $course->id);

        $this->assertInternalType('array', $role_ids);
        $this->assertCount(1, $role_ids);
        $this->assertContains(3, $role_ids);

        // teacher
        $role_ids = role_repo::get_user_roles_in_course($teacher->id, $course->id);

        $this->assertCount(1, $role_ids);
        $this->assertContains(4, $role_ids);

        // student
        $role_ids = role_repo::get_user_roles_in_course($student->id, $course->id);

        $this->assertCount(1, $role_ids);
        $this->assertContains(5, $role_ids);

        // a nobody
        $nobody = $this->getDataGenerator()->create_user();

        $role_ids = role_repo::get_user_roles_in_course($nobody->id, $course->id);

        $this->assertCount(0, $role_ids);

        // add editingteacher role to teacher
        $this->assign_role_id_to_user_in_course(3, $teacher, $course);

        $role_ids = role_repo::get_user_roles_in_course($teacher->id, $course->id);

        // should have both roles now
        $this->assertCount(2, $role_ids);
        $this->assertContains(4, $role_ids);
        $this->assertContains(3, $role_ids);
    }

}