<?php
 
require_once(dirname(__FILE__) . '/unit_testcase_traits.php');

class block_quickmail_compose_course_message_validator_testcase extends advanced_testcase {
    
    use unit_testcase_has_general_helpers,
        unit_testcase_sets_up_courses,
        unit_testcase_submits_compose_message_form;

    public function test_validate_subject_is_missing()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($user_students, 'email', [
            'subject' => ''
        ]);

        $validator = new \block_quickmail\validators\compose_message_form_validator($compose_form_data);
        $validator->for_course($course);
        $validator->validate();

        $this->assertTrue($validator->has_errors());
        $this->assertEquals('Missing subject line.', $validator->errors[0]);
    }

    public function test_validate_body_with_no_injection_is_missing()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($user_students, 'email', [
            'body' => ''
        ]);

        $validator = new \block_quickmail\validators\compose_message_form_validator($compose_form_data);
        $validator->for_course($course);
        $validator->validate();

        $this->assertTrue($validator->has_errors());
        $this->assertEquals('Missing message body.', $validator->errors[0]);
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

        $validator = new \block_quickmail\validators\compose_message_form_validator($compose_form_data);
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

        $validator = new \block_quickmail\validators\compose_message_form_validator($compose_form_data);
        $validator->for_course($course);
        $validator->validate();

        $this->assertTrue($validator->has_errors());
        $this->assertEquals('The additional email "invalid@email" you entered is invalid', $validator->errors[0]);
    }

    public function test_validate_invalid_output_channel_is_invalid()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($user_students, 'invalid');

        $validator = new \block_quickmail\validators\compose_message_form_validator($compose_form_data);
        $validator->for_course($course);
        $validator->validate();

        $this->assertTrue($validator->has_errors());
        $this->assertEquals('That send method is not allowed.', $validator->errors[0]);
    }

    public function test_validate_unsupported_output_channel_is_invalid()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $this->update_system_config_value('block_quickmail_output_channels_available', 'email');

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($user_students, 'message');

        $validator = new \block_quickmail\validators\compose_message_form_validator($compose_form_data);
        $validator->for_course($course);
        $validator->validate();

        $this->assertTrue($validator->has_errors());
        $this->assertEquals('That send method is not allowed.', $validator->errors[0]);
    }

}