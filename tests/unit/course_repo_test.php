<?php
 
require_once(dirname(__FILE__) . '/traits/unit_testcase_traits.php');

use block_quickmail\repos\course_repo;

class block_quickmail_course_repo_testcase extends advanced_testcase {
    
    use has_general_helpers,
        sets_up_courses;

    public function test_get_user_course_array()
    {
        $this->resetAfterTest(true);

        list($course, $course_context, $enrolled_users, $groups) = $this->create_course_with_users_and_groups();

        $teacher = $enrolled_users['teacher'][0];

        $courses = course_repo::get_user_course_array($teacher);

        $this->assertCount(1, $courses);
    }

    // more tests...

}