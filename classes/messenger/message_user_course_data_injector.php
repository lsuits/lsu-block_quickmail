<?php

namespace block_quickmail\messenger;

use block_quickmail\messenger\message_body_parser;
use block_quickmail_config;

class message_user_course_data_injector {

    public $user;
    public $course;
    public $message_body;
    public $parser;
    public $message_keys;

    /**
     * Construct the message_user_course_data_injector
     * 
     * @param object  $user          the moodle user
     * @param object  $course        the moodle course
     * @param string  $message_body  the initial message body to be formatted
     */
    public function __construct($user, $course, $message_body) {
        $this->user = $user;
        $this->course = $course;
        $this->message_body = $message_body;
        $this->parser = new message_body_parser($this->message_body);
        $this->keys = $this->parser->get_keys_from_message_body();

    }

    public static function get_message_body($user, $course, $message_body)
    {
        $injector = new self($user, $course, $message_body);

        $injector->inject_message_data();

        return $injector->message_body;
    }

    public function inject_message_data()
    {
        // if no data to inject, skip
        if (empty($this->keys)) {
            return;
        }

        $supported_keys = block_quickmail_config::get_supported_data_injection_fields();

        foreach ($this->keys as $key) {
            if ( ! in_array($key, $supported_keys)) {
                continue;
            }

            $key_stamp = message_body_parser::get_delimited_key_stamp($key);

            $this->message_body = str_replace($key_stamp, $this->get_mapped_data($key), $this->message_body);
        }
    }

    public function get_mapped_data($key)
    {
        $method = 'get_data_' . $key;

        return $this->$method();
    }

    public function get_data_firstname()
    {
        return $this->user->firstname;
    }

    public function get_data_middlename()
    {
        return $this->user->middlename;
    }

    public function get_data_lastname()
    {
        return $this->user->lastname;
    }

    public function get_data_email()
    {
        return $this->user->email;
    }

    public function get_data_alternatename()
    {
        return $this->user->alternatename;
    }

}