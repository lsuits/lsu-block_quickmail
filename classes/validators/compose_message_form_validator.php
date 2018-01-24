<?php

namespace block_quickmail\validators;

use block_quickmail\validators\validator;
use block_quickmail\messenger\body_parser;
use block_quickmail\requests\compose as compose_request;

class compose_message_form_validator extends validator {

    /**
     * Defines this specific validator's validation rules
     * 
     * @return void
     */
    public function validator_rules()
    {
        $this->validate_subject();

        $this->validate_message_body();

        $this->validate_additional_emails();

        $this->validate_message_type();

        $this->validate_to_send_at();
    }

    /**
     * Checks that the message has a valid subject line, adding any errors to the stack
     * 
     * @return void
     */
    private function validate_subject()
    {
        if ($this->is_missing('subject')) {
            $this->add_error('Missing subject line.');
        }
    }

    /**
     * Checks that the message body exists and that it does not contain any unsupported custom user data keys, adding any errors to the stack
     * 
     * @return void
     */
    private function validate_message_body()
    {
        $body = compose_request::get_transformed_message_body($this->form_data);

        // first, check that there is a message body which is required
        if (empty($body)) {
            $this->add_error('Missing message body.');
        }

        $parser = new body_parser($body);

        if ($parser->has_errors()) {
            foreach($parser->errors as $parse_error) {
                $this->add_error($parse_error);
            }
        }
    }

    /**
     * Checks that any and all additional emails requested are valid emails, adding any errors to the stack
     * 
     * @return void
     */
    private function validate_additional_emails()
    {
        // get an array of cleansed emails from the post data
        $emails = compose_request::get_transformed_additional_emails($this->form_data);

        //  validate each email value
        foreach ($emails as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL) == false) {
                $this->errors[] = 'The additional email "' . $email . '" you entered is invalid';
            }
        }
    }

    /**
     * Checks that the selected "message type" is allowed per site config, adding any errors to the stack
     * 
     * @return void
     */
    private function validate_message_type()
    {
        if ( ! in_array($this->form_data->message_type, \block_quickmail_config::get_supported_message_types())) {
            $this->errors[] = 'That send method is not allowed.';
        }

        $supported_option = $this->get_config('message_types_available');

        if ($supported_option == 'all') {
            return;
        }

        if ($supported_option !== $this->form_data->message_type) {
            $this->errors[] = 'That send method is not allowed.';
        }
    }

    private function validate_to_send_at()
    {
        //
    }

}