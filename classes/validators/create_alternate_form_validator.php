<?php

namespace block_quickmail\validators;

use block_quickmail\validators\validator;
use block_quickmail_string;

class create_alternate_form_validator extends validator {

    /**
     * Defines this specific validator's validation rules
     * 
     * @return void
     */
    public function validator_rules()
    {
        $this->validate_email();

        $this->validate_firstname();

        $this->validate_lastname();

        $this->validate_availability();
    }

    /**
     * Checks that the data has a valid email, adding any errors to the stack
     * 
     * @return void
     */
    private function validate_email()
    {
        if ($this->is_missing('email')) {
            $this->add_error(block_quickmail_string::get('missing_email'));
        }

        if (filter_var($this->form_data->email, FILTER_VALIDATE_EMAIL) == false) {
            $this->add_error(block_quickmail_string::get('invalid_email'));
        }
    }

    /**
     * Checks that the data has a valid firstname, adding any errors to the stack
     * 
     * @return void
     */
    private function validate_firstname()
    {
        if ($this->is_missing('firstname')) {
            $this->add_error(block_quickmail_string::get('missing_firstname'));
        }
    }

    /**
     * Checks that the data has a valid lastname, adding any errors to the stack
     * 
     * @return void
     */
    private function validate_lastname()
    {
        if ($this->is_missing('lastname')) {
            $this->add_error(block_quickmail_string::get('missing_lastname'));
        }
    }

    /**
     * Checks that the data has a valid availability, adding any errors to the stack
     * 
     * @return void
     */
    private function validate_availability()
    {
        if ( ! in_array($this->form_data->availability, [
            'only',
            'user',
            'course'
        ])) { 
            $this->add_error(block_quickmail_string::get('invalid_availability'));
        }
    }

}