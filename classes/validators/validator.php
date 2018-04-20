<?php

namespace block_quickmail\validators;
use block_quickmail_config;

abstract class validator {

    public $form_data;
    public $extra_params;
    public $transformed_data;
    public $errors;
    public $course;

    /**
     * Constructs a validator
     * 
     * @param object $form_data  post data submission object
     * @param array  $extra_params     an array of extra params that may be necessary for validation
     */
    public function __construct($form_data, $extra_params = []) {
        $this->form_data = $form_data;
        $this->extra_params = $extra_params;
        $this->errors = [];
        $this->course = null;
    }

    /**
     * Sets the given course on the validator object
     * 
     * @param  object  $course  moodle course
     * @return void
     */
    public function for_course($course)
    {
        $this->course = $course;
    }

    /**
     * Performs validation against the validator's set form data
     * 
     * @return void
     */
    public function validate()
    {
        $this->validator_rules();
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
    
    /**
     * Reports whether or not the given form data key has any value or not
     * 
     * @param  string  $key  a key on the form_data object
     * @return bool
     */
    public function is_missing($key)
    {
        return empty($this->form_data->$key);
    }

    /**
     * Returns the configuration array or value with respect to the set course (if any)
     * 
     * @param  string  $key
     * @return mixed
     */
    public function get_config($key = '')
    {
        $course_id = empty($this->course) ? 0 : $this->course->id;

        return block_quickmail_config::get($key, $course_id);
    }

    /**
     * Reports whether or not this validator contains any extra params with the given key/value
     *
     * If no value is passed in the check, this will return true if the param was set
     * 
     * @param  string  $key
     * @param  mixed   $value
     * @return bool
     */
    public function check_extra_params_value($key, $value = null)
    {
        // if the key doesn't exists in extra_params, return false
        if ( ! array_key_exists($key, $this->extra_params)) {
            return false;
        }

        // if the key does exist, but no value was specified, then any value will do
        if (is_null($value)) {
            return true;
        }

        return $this->extra_params[$key] == $value;
    }

}