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

        // specify recipients
        $recipients['included']['user'] = $this->get_user_ids_from_user_array($user_students);

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($recipients, 'email', [
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

        // specify recipients
        $recipients['included']['user'] = $this->get_user_ids_from_user_array($user_students);

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($recipients, 'email', [
            'body' => ''
        ]);

        $validator = new save_draft_message_form_validator($compose_form_data);
        $validator->for_course($course);
        $validator->validate();

        $this->assertFalse($validator->has_errors());
    }

    public function test_validate_body_with_substitution_code_typo_scenario_one()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($user_students, 'email', [
            'body' => 'Hey [:firstname I think I may have [:messed up'
        ]);

        $validator = new save_draft_message_form_validator($compose_form_data);
        $validator->for_course($course);
        $validator->validate();

        $this->assertTrue($validator->has_errors());
        $this->assertEquals('Message body substitution codes not formatted properly.', $validator->errors[0]);
    }

    public function test_validate_body_with_substitution_code_typo_scenario_two()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($user_students, 'email', [
            'body' => 'Hey [:firstname I am trying:] this again, did it work?'
        ]);

        $validator = new save_draft_message_form_validator($compose_form_data);
        $validator->for_course($course);
        $validator->validate();

        $this->assertTrue($validator->has_errors());
        $this->assertEquals('Message body substitution codes not formatted properly.', $validator->errors[0]);
    }

    public function test_validate_body_with_substitution_code_typo_scenario_three()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($user_students, 'email', [
            'body' => 'Hey [: firstname:] let me try this again :('
        ]);

        $validator = new save_draft_message_form_validator($compose_form_data);
        $validator->for_course($course);
        $validator->validate();

        $this->assertTrue($validator->has_errors());
        $this->assertEquals('Message body substitution codes not formatted properly.', $validator->errors[0]);
    }

    public function test_validate_body_with_substitution_code_typo_scenario_four()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($user_students, 'email', [
            'body' => ':] and again'
        ]);

        $validator = new save_draft_message_form_validator($compose_form_data);
        $validator->for_course($course);
        $validator->validate();

        $this->assertTrue($validator->has_errors());
        $this->assertEquals('Message body substitution codes not formatted properly.', $validator->errors[0]);
    }

    public function test_validate_body_with_substitution_code_typo_scenario_five()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($user_students, 'email', [
            'body' => ' :]is this it?[:'
        ]);

        $validator = new save_draft_message_form_validator($compose_form_data);
        $validator->for_course($course);
        $validator->validate();

        $this->assertTrue($validator->has_errors());
        $this->assertEquals('Message body substitution codes not formatted properly.', $validator->errors[0]);
    }

    public function test_validate_body_with_substitution_code_typo_scenario_six()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($user_students, 'email', [
            'body' => '[: nothisisit:]'
        ]);

        $validator = new save_draft_message_form_validator($compose_form_data);
        $validator->for_course($course);
        $validator->validate();

        $this->assertTrue($validator->has_errors());
        $this->assertEquals('Message body substitution codes not formatted properly.', $validator->errors[0]);
    }

    public function test_validate_compose_body_with_invalid_substitution_code()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($user_students, 'email', [
            'body' => 'Hello [:firstname:] lets try an [:invalidcode:]. Is your email still [:email:]?'
        ]);

        $validator = new save_draft_message_form_validator($compose_form_data);
        $validator->for_course($course);
        $validator->validate();

        $this->assertTrue($validator->has_errors());
        $this->assertEquals('Custom data key "invalidcode" is not allowed.', $validator->errors[0]);
    }




    public function test_validate_additional_email_list_is_valid()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // set up a course with a teacher and students
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // specify recipients
        $recipients['included']['user'] = $this->get_user_ids_from_user_array($user_students);

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($recipients, 'email', [
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

        // specify recipients
        $recipients['included']['user'] = $this->get_user_ids_from_user_array($user_students);

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($recipients, 'email', [
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

        // specify recipients
        $recipients['included']['user'] = $this->get_user_ids_from_user_array($user_students);

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($recipients, 'invalid');

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

        // specify recipients
        $recipients['included']['user'] = $this->get_user_ids_from_user_array($user_students);

        $this->update_system_config_value('block_quickmail_message_types_available', 'email');

        // get a compose form submission
        $compose_form_data = $this->get_compose_message_form_submission($recipients, 'message');

        $validator = new save_draft_message_form_validator($compose_form_data);
        $validator->for_course($course);
        $validator->validate();

        $this->assertTrue($validator->has_errors());
        $this->assertEquals('That send method is not allowed.', $validator->errors[0]);
    }

}