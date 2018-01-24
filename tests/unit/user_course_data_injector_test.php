<?php
 
require_once(dirname(__FILE__) . '/traits/unit_testcase_traits.php');

use block_quickmail\messenger\user_course_data_injector;

class block_quickmail_user_course_data_injector_testcase extends advanced_testcase {
    
    use has_general_helpers,
        sets_up_courses;

    public function test_formats_body_with_no_keys()
    {
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $first_student = $user_students[0];

        $body_to_inject = 'Here is the subject!';

        $result_message_body = user_course_data_injector::get_message_body($first_student, $course, $body_to_inject);

        $this->assertEquals('Here is the subject!', $result_message_body);
    }

    public function test_skips_non_supported_keys()
    {
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $first_student = $user_students[0];

        $body_to_inject = 'This one is [:notsupported:]!';

        $result_message_body = user_course_data_injector::get_message_body($first_student, $course, $body_to_inject);

        $expected_body = 'This one is [:notsupported:]!';

        $this->assertEquals($expected_body, $result_message_body);
    }

    public function test_formats_body_with_user_data()
    {
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $first_student = $user_students[0];

        $body_to_inject = 'Hey there [:firstname:] [:middlename:] [:lastname:], your email address is [:email:]!';

        $result_message_body = user_course_data_injector::get_message_body($first_student, $course, $body_to_inject);

        $expected_body = 'Hey there ' . $first_student->firstname . ' ' . $first_student->middlename . ' ' . $first_student->lastname . ', your email address is ' . $first_student->email . '!';

        $this->assertEquals($expected_body, $result_message_body);
    }

    public function test_formats_body_with_course_data()
    {
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $first_student = $user_students[0];

        $body_to_inject = 'Attention, the fullname of this course is [:coursefullname:] but you can call it [:courseshortname:] if you like. In case you might want to know, the ID number is: [:courseidnumber:] but you should know that is is all about [:coursesummary:]. It will be starting [:coursestartdate:] and ending [:courseenddate:]. Good luck.';

        $result_message_body = user_course_data_injector::get_message_body($first_student, $course, $body_to_inject);

        $expected_body = 'Attention, the fullname of this course is ' . $course->fullname . ' but you can call it ' . $course->shortname . ' if you like. In case you might want to know, the ID number is: ' . $course->idnumber . ' but you should know that is is all about ' . $course->summary . '. It will be starting ' . date('F j, Y', $course->startdate) . ' and ending ' . date('F j, Y', $course->enddate) . '. Good luck.';

        $this->assertEquals($expected_body, $result_message_body);
    }

}