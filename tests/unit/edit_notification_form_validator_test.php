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

use block_quickmail\validators\edit_notification_form_validator;

class block_quickmail_edit_notification_form_validator_testcase extends advanced_testcase {
    
    use has_general_helpers,
        sets_up_courses,
        sets_up_notifications;

    public function test_validate_notification_name()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // check exist

        $input = $this->get_notification_input(['notification_name' => '']);

        $validator = new edit_notification_form_validator($input, [
            'notification_type' => 'reminder',
        ]);
        $validator->validate();

        $this->assertTrue($validator->has_errors());
        $this->assertEquals('Missing notification name.', $validator->errors[0]);

        // check length

        $input = $this->get_notification_input(['notification_name' => 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz']);

        $validator = new edit_notification_form_validator($input, [
            'notification_type' => 'reminder',
        ]);
        $validator->validate();

        $this->assertTrue($validator->has_errors());
        $this->assertEquals('Notification name must be 40 characters or less.', $validator->errors[0]);
    }

    public function test_validate_message_subject()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // check exist

        $input = $this->get_notification_input(['message_subject' => '']);

        $validator = new edit_notification_form_validator($input, [
            'notification_type' => 'reminder',
        ]);
        $validator->validate();

        $this->assertTrue($validator->has_errors());
        $this->assertEquals('Missing subject line.', $validator->errors[0]);
    }

    public function test_validate_message_body()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // check exist

        $input = $this->get_notification_input(['message_body' => ['text' => '', 'format' => '1']]);

        $validator = new edit_notification_form_validator($input, [
            'notification_type' => 'reminder',
        ]);
        $validator->validate();

        $this->assertTrue($validator->has_errors());
        $this->assertEquals('Missing message body.', $validator->errors[0]);
    }

    public function test_validate_invalid_message_type_is_invalid()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);

        $input = $this->get_notification_input(['message_type' => 'invalid']);

        $validator = new edit_notification_form_validator($input, [
            'notification_type' => 'reminder',
        ]);
        $validator->validate();

        $this->assertTrue($validator->has_errors());
        $this->assertEquals('That send method is not allowed.', $validator->errors[0]);
    }

    public function test_validate_unsupported_message_type_is_invalid()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);

        $this->update_system_config_value('block_quickmail_message_types_available', 'email');

        $input = $this->get_notification_input(['message_type' => 'invalid']);

        $validator = new edit_notification_form_validator($input, [
            'notification_type' => 'reminder',
        ]);
        $validator->validate();

        $this->assertTrue($validator->has_errors());
        $this->assertEquals('That send method is not allowed.', $validator->errors[0]);
    }

    public function test_validate_body_with_substitution_code_typo_scenario_one()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        $input = $this->get_notification_input(['message_body' => [
            'text' => 'Hey [:firstname I think I may have [:messed up',
            'format' => '1',
        ]]);

        $validator = new edit_notification_form_validator($input, [
            'notification_type' => 'reminder',
        ]);
        $validator->validate();

        $this->assertTrue($validator->has_errors());
        $this->assertEquals('Message body substitution codes not formatted properly.', $validator->errors[0]);
    }

    public function test_validate_body_with_substitution_code_typo_scenario_two()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        $input = $this->get_notification_input(['message_body' => [
            'text' => 'Hey [:firstname I am trying:] this again, did it work?',
            'format' => '1',
        ]]);

        $validator = new edit_notification_form_validator($input, [
            'notification_type' => 'reminder',
        ]);
        $validator->validate();


        $this->assertTrue($validator->has_errors());
        $this->assertEquals('Message body substitution codes not formatted properly.', $validator->errors[0]);
    }

    public function test_validate_body_with_substitution_code_typo_scenario_three()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        $input = $this->get_notification_input(['message_body' => [
            'text' => 'Hey [: firstname:] let me try this again :(',
            'format' => '1',
        ]]);

        $validator = new edit_notification_form_validator($input, [
            'notification_type' => 'reminder',
        ]);
        $validator->validate();

        $this->assertTrue($validator->has_errors());
        $this->assertEquals('Message body substitution codes not formatted properly.', $validator->errors[0]);
    }

    public function test_validate_body_with_substitution_code_typo_scenario_four()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        $input = $this->get_notification_input(['message_body' => [
            'text' => ':] and again',
            'format' => '1',
        ]]);

        $validator = new edit_notification_form_validator($input, [
            'notification_type' => 'reminder',
        ]);
        $validator->validate();

        $this->assertTrue($validator->has_errors());
        $this->assertEquals('Message body substitution codes not formatted properly.', $validator->errors[0]);
    }

    public function test_validate_body_with_substitution_code_typo_scenario_five()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        $input = $this->get_notification_input(['message_body' => [
            'text' => ' :]is this it?[:',
            'format' => '1',
        ]]);

        $validator = new edit_notification_form_validator($input, [
            'notification_type' => 'reminder',
        ]);
        $validator->validate();

        $this->assertTrue($validator->has_errors());
        $this->assertEquals('Message body substitution codes not formatted properly.', $validator->errors[0]);
    }

    public function test_validate_body_with_substitution_code_typo_scenario_six()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        $input = $this->get_notification_input(['message_body' => [
            'text' => '[: nothisisit:]',
            'format' => '1',
        ]]);

        $validator = new edit_notification_form_validator($input, [
            'notification_type' => 'reminder',
        ]);
        $validator->validate();

        $this->assertTrue($validator->has_errors());
        $this->assertEquals('Message body substitution codes not formatted properly.', $validator->errors[0]);
    }

    public function test_validate_message_body_with_invalid_substitution_code()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        $input = $this->get_notification_input(['message_body' => [
            'text' => 'Hello [:firstname:] lets try an [:invalidcode:]. Is your email still [:email:]?',
            'format' => '1',
        ]]);

        $validator = new edit_notification_form_validator($input, [
            'notification_type' => 'reminder',
        ]);
        $validator->validate();

        $this->assertTrue($validator->has_errors());
        $this->assertEquals('Custom data key "invalidcode" is not allowed.', $validator->errors[0]);
    }

    public function test_validate_message_body_with_unallowed_substitution_code()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        $input = $this->get_notification_input(['message_body' => [
            'text' => 'Hello [:firstname:] lets try an [:coursefullname:]. Is your email still [:email:]?',
            'format' => '1',
        ]]);

        $validator = new edit_notification_form_validator($input, [
            'notification_type' => 'reminder',
        ]);
        $validator->validate();

        $this->assertTrue($validator->has_errors());
        $this->assertEquals('Custom data key "coursefullname" is not allowed.', $validator->errors[0]);
    }

    public function test_validate_message_body_with_allowed_substitution_code()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        $input = $this->get_notification_input(['message_body' => [
            'text' => 'Hello [:firstname:] lets try an [:coursefullname:]. Is your email still [:email:]?',
            'format' => '1',
        ]]);

        $validator = new edit_notification_form_validator($input, [
            'substitution_code_classes' => ['user', 'course']
        ]);
        $validator->validate();

        $this->assertFalse($validator->has_errors());
    }

    // commenting out as this validation is now being done on front-end during user input

    // public function test_validate_condition_time_unit_is_valid_for_notification_with_required_keys()
    // {
    //     // reset all changes automatically after this test
    //     $this->resetAfterTest(true);
 
    //     $input = $this->get_notification_input(['condition_time_unit' => 'decade']);

    //     $validator = new edit_notification_form_validator($input, [
    //         'notification_type' => 'reminder',
    //         'required_condition_keys' => ['time_unit', 'time_amount'],
    //     ]);
    //     $validator->validate();

    //     $this->assertTrue($validator->has_errors());
    //     $this->assertEquals('Invalid unit of time for condition.', $validator->errors[0]);

    //     // reset all changes automatically after this test
    //     $this->resetAfterTest(true);
 
    //     $input = $this->get_notification_input(['schedule_time_unit' => 'day']);

    //     $validator = new edit_notification_form_validator($input, [
    //         'notification_type' => 'reminder',
    //     ]);
    //     $validator->validate();

    //     $this->assertFalse($validator->has_errors());
    // }

    // commenting out as this validation is now being done on front-end during user input
    
    // public function test_validate_condition_time_amount_is_valid_for_notification_with_required_keys()
    // {
    //     // reset all changes automatically after this test
    //     $this->resetAfterTest(true);
 
    //     $input = $this->get_notification_input(['condition_time_amount' => '']);

    //     $validator = new edit_notification_form_validator($input, [
    //         'notification_type' => 'reminder',
    //         'required_condition_keys' => ['time_unit', 'time_amount'],
    //     ]);
    //     $validator->validate();

    //     $this->assertTrue($validator->has_errors());
    //     $this->assertEquals('Invalid amount of time for condition.', $validator->errors[0]);

    //     $input = $this->get_notification_input(['condition_time_amount' => 'longtime']);

    //     $validator = new edit_notification_form_validator($input, [
    //         'notification_type' => 'reminder',
    //         'required_condition_keys' => ['time_unit', 'time_amount'],
    //     ]);
    //     $validator->validate();

    //     $this->assertTrue($validator->has_errors());
    //     $this->assertEquals('Invalid amount of time for condition.', $validator->errors[0]);

    //     $input = $this->get_notification_input(['condition_time_amount' => '2']);

    //     $validator = new edit_notification_form_validator($input, [
    //         'notification_type' => 'reminder',
    //         'required_condition_keys' => ['time_unit', 'time_amount'],
    //     ]);
    //     $validator->validate();

    //     $this->assertFalse($validator->has_errors());
    // }

    public function test_validate_conditions_for_notification_with_no_required_keys()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        $input = $this->get_notification_input(['condition_time_unit' => 'decade', 'condition_time_amount' => 'no']);

        $validator = new edit_notification_form_validator($input, [
            'notification_type' => 'reminder',
        ]);
        $validator->validate();

        $this->assertFalse($validator->has_errors());
    }

}