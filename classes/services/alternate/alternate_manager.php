<?php

namespace block_quickmail\services\alternate;

use block_quickmail\persistents\alternate_email;
use block_quickmail\validators\compose_message_form_validator;
use block_quickmail_config;
use block_quickmail\exceptions\validation_exception;

use html_writer;
use moodle_url;

class alternate_manager {

    // email
    // firstname
    // lastname
    // availability
        // alternate_availability_only
        // alternate_availability_user
        // alternate_availability_course
    public static function create_alternate($user, $params)
    {
        // validate form data
        $validator = new compose_message_form_validator($form_data, [
            'course_config' => block_quickmail_config::_c('', $course)
        ]);
        $validator->validate();

        // if errors, throw exception
        if ($validator->has_errors()) {
            throw new validation_exception('Validation exception!', $validator->errors);
        }

        $alternate = alternate_email::create_new($data = [])

        // create the new alternate email
        $alternate_email = new alternate_email(0, $request->get_create_request_data_object());
        $alternate_email->create();

        // refresh the persistent just in case
        $alternate_email->read();

        // generate a random token, and send confirmation email to user
        $alternate_email->send_confirmation_email($page_params['courseid']);
    }

    public static function resend_confirmation_email()
    {
        //
    }

    public static function confirm_alternate()
    {
        //
    }

}