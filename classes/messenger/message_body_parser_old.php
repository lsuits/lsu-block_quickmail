<?php

namespace block_quickmail\messenger;

class message_body_parser_old {

    public $message_body;
    public $supported_keys;
    public $selected_keys;
    public $user_fields_keys;

    /**
     * Construct the message body parser
     * 
     * @param string  $message_body     the message content body
     * @param array   $supported_keys   custom user data keys supported by the site config
     * @param array   $selected_keys    custom user data keys that need to be parsed
     */
    public function __construct($message_body, $supported_keys = [], $selected_keys = []) {
        $this->message_body = $message_body;
        $this->supported_keys = $supported_keys;
        $this->selected_keys = $selected_keys;
        $this->user_fields_keys = [];
    }

    /**
     * Returns the delimeter that should be typed in front of the custom user data key
     * 
     * @return string
     */
    public function get_first_delimiter()
    {
        return '[:';
    }

    /**
     * Returns the delimeter that should be typed behind the custom user data key
     * 
     * @return string
     */
    public function get_last_delimiter()
    {
        return ':]';
    }

    /**
     * Parses the message body for instances of injected user data and sets and returns those field names "keys"
     * 
     * @return array
     */
    public function get_keys_from_message_body()
    {
        // make a copy of the message body for manipulation
        $message = '_' . trim($this->message_body);

        $first_delimeter = $this->get_first_delimiter();
        $last_delimeter = $this->get_last_delimiter();

        // while there still exists a custom user data injection key in the message body
        while ($next_first_delimiter = strpos($message, $first_delimeter)) {
            // trim up until the delimiter
            $message = substr($message, $next_first_delimiter + strlen($first_delimeter));

            // if no ending delimiter, no bueno
            if ( ! $next_last_delimeter = strpos($message, $last_delimeter)) {
                throw new \Exception('Custom data delimiters now formatted properly.');
            }

            // get the custom user key and add to the results
            $user_field_key = substr($message, 0, $next_last_delimeter);

            $this->user_fields_keys[] = $user_field_key;
            
            // trim the value and ending delimiter out of the remaining message and continue
            $message = substr($message, $next_last_delimeter + strlen($last_delimeter));
        }

        return $this->user_fields_keys;
    }

    /**
     * Checks that the message body does NOT contain any unsupported data keys
     * 
     * @return array [success status boolean, array of all (assumed valid) keys]
     */
    public function validate_supported_fields()
    {
        $this->get_keys_from_message_body();

        $success = count(array_diff($this->user_fields_keys, $this->supported_keys)) ? false : true;

        return [$success, $this->user_fields_keys];
    }

    /**
     * Returns a parsed, user-data-injected message body for a specific user
     * 
     * @param  core_user  $user
     * @return string
     */
    public function inject_user_data($user)
    {
        $message_body = $this->message_body;

        foreach ($this->selected_keys as $field) {
            // make sure there is actually user data for this field, otherwise just use the field name in parentheses
            $user_data = empty($user->$field) || ! property_exists($user, $field) ? '(' . $field . ')' : $user->$field;

            // find and replace the field instance(s)
            $message_body = str_replace($this->get_first_delimiter() . $field . $this->get_last_delimiter(), $user_data, $message_body);
        }

        return $message_body;
    }

}