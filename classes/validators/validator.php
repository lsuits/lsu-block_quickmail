<?php

namespace block_quickmail\validators;

class validator {

    public $form_data;
    public $errors;
    public $course;

    public function __construct($form_data) {
        $this->form_data = $form_data;
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

        return \block_quickmail_config::_c($key, $course_id);
    }

}