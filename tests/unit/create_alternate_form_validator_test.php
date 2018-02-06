<?php
 
require_once(dirname(__FILE__) . '/traits/unit_testcase_traits.php');

use block_quickmail\validators\create_alternate_form_validator;

class block_quickmail_create_alternate_form_validator_testcase extends advanced_testcase {
    
    use has_general_helpers,
        submits_create_alternate_form;

    public function test_validate_email_is_missing()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // get a create alternate form submission
        $create_alternate_data = $this->get_create_alternate_form_submission([
            'email' => ''
        ]);

        $validator = new create_alternate_form_validator($create_alternate_data);
        $validator->validate();

        $this->assertTrue($validator->has_errors());
        $this->assertEquals('Missing email address.', $validator->errors[0]);
    }

    public function test_validate_email_is_invalid()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // get a create alternate form submission
        $create_alternate_data = $this->get_create_alternate_form_submission([
            'email' => 'invalid email'
        ]);

        $validator = new create_alternate_form_validator($create_alternate_data);
        $validator->validate();

        $this->assertTrue($validator->has_errors());
        $this->assertEquals('Invalid email address.', $validator->errors[0]);
    }

    public function test_validate_firstname_is_missing()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // get a create alternate form submission
        $create_alternate_data = $this->get_create_alternate_form_submission([
            'firstname' => ''
        ]);

        $validator = new create_alternate_form_validator($create_alternate_data);
        $validator->validate();

        $this->assertTrue($validator->has_errors());
        $this->assertEquals('Missing first name.', $validator->errors[0]);
    }

    public function test_validate_lastname_is_missing()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // get a create alternate form submission
        $create_alternate_data = $this->get_create_alternate_form_submission([
            'lastname' => ''
        ]);

        $validator = new create_alternate_form_validator($create_alternate_data);
        $validator->validate();

        $this->assertTrue($validator->has_errors());
        $this->assertEquals('Missing last name.', $validator->errors[0]);
    }

    public function test_validate_availability_is_valid()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
 
        // get a create alternate form submission
        $create_alternate_data = $this->get_create_alternate_form_submission([
            'availability' => 'anytime'
        ]);

        $validator = new create_alternate_form_validator($create_alternate_data);
        $validator->validate();

        $this->assertTrue($validator->has_errors());
        $this->assertEquals('Invalid availability value.', $validator->errors[0]);
    }

}