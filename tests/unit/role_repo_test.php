<?php
 
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
            'allowed_user_fields' => 'firstname',
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

}