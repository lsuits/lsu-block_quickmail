<?php
 
require_once(dirname(__FILE__) . '/unit_testcase_traits.php');

class block_quickmail_message_user_course_data_injector_testcase extends advanced_testcase {
    
    use unit_testcase_has_general_helpers;
    use unit_testcase_sets_up_courses;

    public function test_formats_body_with_no_keys()
    {
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $first_student = $user_students[0];

        $body_to_inject = 'Here is the subject!';

        $result_message_body = \block_quickmail\messenger\message_user_course_data_injector::get_message_body($first_student, $course, $body_to_inject);

        $this->assertEquals('Here is the subject!', $result_message_body);
    }

    public function test_formats_body_with_user_data()
    {
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $first_student = $user_students[0];

        $body_to_inject = 'Hey there [:firstname:] [:middlename:] [:lastname:], your email address is [:email:]!';

        $result_message_body = \block_quickmail\messenger\message_user_course_data_injector::get_message_body($first_student, $course, $body_to_inject);

        $this->assertEquals('Hey there ' . $first_student->firstname . ' ' . $first_student->middlename . ' ' . $first_student->lastname . ', your email address is ' . $first_student->email . '!', $result_message_body);
    }

    public function test_skips_non_supported_keys()
    {
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $first_student = $user_students[0];

        $body_to_inject = 'This one is [:notsupported:]!';

        $result_message_body = \block_quickmail\messenger\message_user_course_data_injector::get_message_body($first_student, $course, $body_to_inject);

        $this->assertEquals('This one is [:notsupported:]!', $result_message_body);
    }

}