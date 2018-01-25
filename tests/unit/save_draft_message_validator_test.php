<?php
 
require_once(dirname(__FILE__) . '/traits/unit_testcase_traits.php');

use block_quickmail\validators\save_draft_message_form_validator;

class block_quickmail_save_draft_message_validator_testcase extends advanced_testcase {
    
    use has_general_helpers,
        sets_up_courses,
        submits_compose_message_form;

    public function test_subject_is_not_required()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($user_students, 'email', [
            'subject' => ''
        ]);

        $validator = new save_draft_message_form_validator($compose_form_data);
        $validator->for_course($course);
        $validator->validate();

        $this->assertFalse($validator->has_errors());
    }

    public function test_body_is_not_required()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($user_students, 'email', [
            'body' => ''
        ]);

        $validator = new save_draft_message_form_validator($compose_form_data);
        $validator->for_course($course);
        $validator->validate();

        $this->assertFalse($validator->has_errors());
    }

    public function test_body_cannot_contain_unsupported_injection_keys()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($user_students, 'email', [
            'body' => 'Hello world! Here is [:this:], [:that:], and the [:other:]!'
        ]);

        $validator = new save_draft_message_form_validator($compose_form_data);
        $validator->for_course($course);
        $validator->validate();

        $this->assertTrue($validator->has_errors());
        $this->assertEquals('Custom data key "this" is not allowed.', $validator->errors[0]);
    }

    public function test_validate_additional_email_list_is_valid()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($user_students, 'email', [
            'additional_emails' => 'test@email.com, another@email.com'
        ]);

        $validator = new save_draft_message_form_validator($compose_form_data);
        $validator->for_course($course);
        $validator->validate();

        $this->assertFalse($validator->has_errors());
    }

    public function test_validate_additional_email_list_is_invalid()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($user_students, 'email', [
            'additional_emails' => 'invalid@email, another@email.com'
        ]);

        $validator = new save_draft_message_form_validator($compose_form_data);
        $validator->for_course($course);
        $validator->validate();

        $this->assertTrue($validator->has_errors());
        $this->assertEquals('The additional email "invalid@email" you entered is invalid', $validator->errors[0]);
    }

    public function test_validate_invalid_message_type_is_invalid()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($user_students, 'invalid');

        $validator = new save_draft_message_form_validator($compose_form_data);
        $validator->for_course($course);
        $validator->validate();

        $this->assertTrue($validator->has_errors());
        $this->assertEquals('That send method is not allowed.', $validator->errors[0]);
    }

    public function test_validate_unsupported_message_type_is_invalid()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $this->update_system_config_value('block_quickmail_message_types_available', 'email');

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($user_students, 'message');

        $validator = new save_draft_message_form_validator($compose_form_data);
        $validator->for_course($course);
        $validator->validate();

        $this->assertTrue($validator->has_errors());
        $this->assertEquals('That send method is not allowed.', $validator->errors[0]);
    }

}