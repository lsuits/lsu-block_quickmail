<?php

namespace block_quickmail\validators;

class validator {

    public $form_data;
    public $message;
    public $errors;
    public $extra_data;

    public function __construct($form_data, $extra_data = []) {
        $this->form_data = $form_data;
        $this->extra_data = $extra_data;
        $this->message = 'Validation error!';
        $this->errors = [];

        $this->validate();
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
     * Returns a value of extra data with the given key
     * 
     * @param  string  $key
     * @return mixed
     */
    public function get_extra_data($key)
    {
        return $this->extra_data[$key];
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

}