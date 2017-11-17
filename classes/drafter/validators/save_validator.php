<?php

namespace block_quickmail\drafter\validators;

use block_quickmail\drafter\drafter;

class save_validator {

    public $drafter;
    public $errors;
    
    /**
     * Construct the validator service
     * 
     * @param block_quickmail\drafter\drafter  $drafter
     */
    public function __construct(drafter $drafter) {
        $this->drafter = $drafter;
        $this->errors = [];
    }

    /**
     * Validates a draft message save
     *  - adds any errors to the stack
     * 
     * @return void
     */
    public function validate()
    {
        $this->validate_scope();

        $this->validate_user();

        $this->validate_additional_emails();

        $this->validate_output_channel();

        // TODO: validate signature - make sure it is not deleted and belongs to the user???
    }

    /**
     * Checks that scope-based variables are set for the drafter, adding any errors to the stack
     * 
     * @return void
     */
    private function validate_scope()
    {
        switch ($this->drafter->message_scope) {
            // if sending from a course perspective
            case 'course':
                // make sure we have a course
                if (empty($this->drafter->course)) {
                    $this->errors[] = 'Invalid course.';   // TODO: change this message?
                }
                
                break;
            
            // if sending from a system perspective
            case 'system':
                // make sure we DON'T have a course
                if ( ! empty($this->drafter->course)) {
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
        if (empty($this->drafter->user)) {
            $this->errors[] = 'Invalid user.';   // TODO: change this message?
        }
    }

    /**
     * Checks that any and all additional emails requested are valid emails, adding any errors to the stack
     * 
     * @return void
     */
    private function validate_additional_emails()
    {
        foreach ($this->drafter->message_data->additional_emails as $email) {
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
        $supported_option = $this->drafter->config['output_channels_available'];

        if ($supported_option == 'all') {
            return;
        }

        if ($supported_option !== $this->drafter->message_data->output_channel) {
            $this->errors[] = 'That send method is not allowed.';
        }
    }

}