<?php
 
// require_once(dirname(__FILE__) . '/unit_testcase_traits.php');

// class block_quickmail_compose_messages_testcase extends advanced_testcase {
    
//     use unit_testcase_has_general_helpers;
//     use unit_testcase_sets_up_courses;
//     use unit_testcase_submits_compose_message_form;

//     public function test_send_composed_course_message_now()
//     {
//         // reset all changes automatically after this test
//         $this->resetAfterTest(true);
 
//         // set up a course with a teacher and students
//         list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

//         // get a compose form submission
//         $compose_form_data = $this->get_compose_message_form_submission($user_students, 'email', 0, []);

//         // send a message from the teacher to the students now
//         \block_quickmail\messenger\messenger::send_composed_course_message($user_teacher, $course, $compose_form_data);

//         // assert that emails have been sent, etc.
//     }

// }