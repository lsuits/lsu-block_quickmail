<?php

namespace block_quickmail\messenger;

class message_body_parser {

    public $errors;
    public $message_body;
    public $supported_keys;
    public $message_keys;

    /**
     * Construct the message body parser
     * 
     * @param string  $message_body     the message content body
     */
    public function __construct($message_body) {
        $this->errors = [];
        $this->message_body = $message_body;
        $this->set_supported_keys();
        $this->set_message_keys();
        $this->validate_message_keys();
    }

    /**
     * Sets the block-level configured allowed user keys
     */
    private function set_supported_keys()
    {
        $this->supported_keys = \block_quickmail_config::_c('allowed_user_fields');
    }

    /**
     * Sets the message keys by parsing the set message body
     */
    private function set_message_keys()
    {
        $this->message_keys = $this->get_keys_from_message_body();
    }

    /**
     * Parses the message body for instances of injected data and returns those field names "keys"
     * 
     * @return array
     */
    public function get_keys_from_message_body()
    {
        // make a copy of the message body for manipulation
        $message = '_' . trim($this->message_body);

        $first_delimiter = self::get_first_delimiter();
        $last_delimiter = self::get_last_delimiter();

        $message_keys = [];

        // while there still exists a custom user data injection key in the message body
        while ($next_first_delimiter = strpos($message, $first_delimiter)) {
            // trim up until the delimiter
            $message = substr($message, $next_first_delimiter + strlen($first_delimiter));

            // if no ending delimiter, no bueno
            if ( ! $next_last_delimiter = strpos($message, $last_delimiter)) {
                $this->add_error('Custom data delimiters not formatted properly.');
            }

            // get the custom message key
            $message_key = substr($message, 0, $next_last_delimiter);

            // add to the stack
            $message_keys[] = $message_key;

            // trim the value and ending delimiter out of the remaining message and continue
            $message = substr($message, $next_last_delimiter + strlen($last_delimiter));
        }

        return $message_keys;
    }

    /**
     * Sets an error message in the stack for any parsed message key that is not supported by the given configuration
     * 
     * @return void
     */
    public function validate_message_keys()
    {
        foreach ($this->message_keys as $message_key) {
            if ( ! in_array($message_key, $this->supported_keys)) {
                $this->add_error('Custom data key "' . $message_key . '" is not allowed.');
            }
        }
    }

    public static function get_delimited_key_stamp($value)
    {
        return self::get_first_delimiter() . $value . self::get_last_delimiter();
    }

    /**
     * Returns the delimiter that should be typed in front of the custom user data key
     * 
     * @return string
     */
    public static function get_first_delimiter()
    {
        return '[:';
    }

    /**
     * Returns the delimiter that should be typed behind the custom user data key
     * 
     * @return string
     */
    public static function get_last_delimiter()
    {
        return ':]';
    }

    /**
     * Includes the given error message in the stack of errors
     * 
     * @param string  $message
     * @return void
     */
    public function add_error($message)
    {
        $this->errors[] = $message;
    }

    /**
     * Reports whether or not this validator has any errors
     * 
     * @return bool
     */
    public function has_errors()
    {
        return (bool) count($this->errors);
    }

}