<?php

namespace block_quickmail\messenger\validators;

use block_quickmail\messenger\messenger;
use block_quickmail\messenger\message_body_parser;

class send_validator {

    public $messenger;
    public $errors;
    public $custom_user_data_keys;
    
    /**
     * Construct the messenger service
     * 
     * @param block_quickmail\messenger\messenger  $messenger
     */
    public function __construct(messenger $messenger) {
        $this->messenger = $messenger;
        $this->errors = [];
        $this->custom_user_data_keys = [];
    }

    /**
     * Validates a message send
     *  - adds any errors to the stack
     *  - adds all custom_user_data_keys to the stack
     * 
     * @return void
     */
    public function validate()
    {
        $this->validate_scope();

        $this->validate_user();

        $this->validate_subject();

        $this->validate_message_body();

        $this->validate_additional_emails();

        $this->validate_output_channel();

        // TODO: validate signature - make sure it is not deleted and belongs to the user???
    }

    /**
     * Checks that scope-based variables are set for the messenger, adding any errors to the stack
     * 
     * @return void
     */
    private function validate_scope()
    {
        switch ($this->messenger->message_scope) {
            // if sending from a course perspective
            case 'course':
                // make sure we have a course
                if (empty($this->messenger->course)) {
                    $this->errors[] = 'Invalid course.';   // TODO: change this message?
                }
                
                break;
            
            // if sending from a system perspective
            case 'system':
                // make sure we DON'T have a course
                if ( ! empty($this->messenger->course)) {
                    $this->errors[] = 'Invalid context.';  // TODO: change this message?
                }

                break;

            default:
                $this->errors[] = 'Invalid scope.';  // TODO: change this message?
                break;
        }
    }

    /**
     * Checks that we have a sending user, adding any errors to the stack
     * 
     * @return void
     */
    private function validate_user()
    {
        if (empty($this->messenger->user)) {
            $this->errors[] = 'Invalid user.';   // TODO: change this message?
        }
    }

    /**
     * Checks that the message has a valid subject line, adding any errors to the stack
     * 
     * @return void
     */
    private function validate_subject()
    {
        if (empty($this->messenger->message_data->subject)) {
            $this->errors[] = 'Missing subject line.';
        }
    }

    /**
     * Checks that the message body exists and that it does not contain any unsupported custom user data keys, adding any errors to the stack
     * 
     * @return [type] [description]
     */
    private function validate_message_body()
    {
        // first, check that there is a message body which is required
        if (empty($this->messenger->message_data->message)) {
            $this->errors[] = 'Missing message body.';
        }

        $parser = new message_body_parser($this->messenger->message_data->message, $this->messenger->config['allowed_user_fields']);

        try {
            // attempt to check that any custom fields in the body are allowed, and fetch the keys in the process (for later use)
            list($success, $user_data_keys_in_message) = $parser->validate_supported_fields();

            $this->custom_user_data_keys = $user_data_keys_in_message;

            if ( ! $success) {
                $this->errors[] = 'The message body contains custom user data that is not supported, please remove those references.';
            }
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
        }
    }

    /**
     * Checks that any and all additional emails requested are valid emails, adding any errors to the stack
     * 
     * @return void
     */
    private function validate_additional_emails()
    {
        foreach ($this->messenger->message_data->additional_emails as $email) {
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
        $supported_option = $this->messenger->config['output_channels_available'];

        if ($supported_option == 'all') {
            return;
        }

        if ($supported_option !== $this->messenger->message_data->output_channel) {
            $this->errors[] = 'That send method is not allowed.';
        }
    }

}