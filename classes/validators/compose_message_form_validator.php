<?php

namespace block_quickmail\validators;

use block_quickmail\validators\validator;
use block_quickmail\messenger\message_body_parser;
use block_quickmail\requests\compose as compose_request;

class compose_message_form_validator extends validator {

    /**
     * Performs validation against the validator's set form data
     * 
     * @return void
     */
    public function validate()
    {
        $this->validate_subject();

        $this->validate_message_body();

        $this->validate_additional_emails();

        $this->validate_output_channel();

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
        $course_config = $this->get_extra_data('course_config');

        $body = compose_request::get_transformed_message_body($this->form_data);

        // first, check that there is a message body which is required
        if (empty($body)) {
            $this->add_error('Missing message body.');
        }

        $parser = new message_body_parser($body, $course_config['allowed_user_fields']);

        try {
            // attempt to check that any custom fields in the body are allowed, and fetch the keys in the process (for later use)
            list($success, $user_data_keys_in_message) = $parser->validate_supported_fields();

            $this->custom_user_data_keys = $user_data_keys_in_message;

            if ( ! $success) {
                $this->add_error('The message body contains custom user data that is not supported, please remove those references.');
            }
        } catch (\Exception $e) {
            $this->add_error($e->getMessage());
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
     * Checks that the selected "output channel" is allowed per site config, adding any errors to the stack
     * 
     * @return void
     */
    private function validate_output_channel()
    {
        $course_config = $this->get_extra_data('course_config');

        if ( ! in_array($this->form_data->output_channel, \block_quickmail_plugin::get_supported_output_channels())) {
            $this->errors[] = 'That send method is not allowed.';
        }

        $supported_option = $course_config['output_channels_available'];

        if ($supported_option == 'all') {
            return;
        }

        if ($supported_option !== $this->form_data->output_channel) {
            $this->errors[] = 'That send method is not allowed.';
        }
    }

    private function validate_to_send_at()
    {
        //
    }

}