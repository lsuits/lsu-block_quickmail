<?php

class block_quickmail_request {

    public $form;
    public $data;

    public function __construct()
    {
        $this->form = null;
        $this->data = (object)[];
    }

    ////////////////////////////////////////
    ///
    /// INSTANTIATION METHODS
    /// 
    ////////////////////////////////////////

    /**
     * Returns an instantiated request for the given "route"
     * 
     * @param  string  $route_name
     * @return object
     */
    public static function for_route($route_name)
    {
        $request = '\block_quickmail\requests\\' . $route_name . '_request';

        return new $request;
    }

    /**
     * Sets the given form on the request object
     * 
     * @param  object  $form
     * @return object
     */
    public function with_form($form)
    {
        $this->form = $form;

        if ($this->was_submitted()) {
            $this->data = static::get_transformed_post_data($this->form->get_data());
        }

        return $this;
    }

    ////////////////////////////////////////
    ///
    /// FORM SUBMISSION HANDLING
    /// 
    ////////////////////////////////////////

    /**
     * Reports whether or not this request was a submission of data
     * Optionally, whether or not a specific action was submitted
     * 
     * @param  string  $action  save|send
     * @return bool
     */
    public function was_submitted($action = null) {

        if (empty($this->form)) {
            return false;
        }

        // get raw form data
        $form_data = $this->form->get_data();

        // if no post data, return false
        if (empty($form_data)) {
            return false;
        }

        // otherwise, if no explicit action specified, return true
        if (empty($action)) {
            return true;
        }

        return property_exists($form_data, $action);
    }

    /**
     * Reports whether or not the submitted request has a given input element key
     * 
     * @param  string  $input_element_key
     * @return bool
     */
    public function has_form_data_key($input_element_key) {
        // get raw form data
        $form_data = $this->form->get_data();

        // if no post data, return false
        if (empty($form_data)) {
            return false;
        }

        // return whether the given key exists in the posted data
        return property_exists($form_data, $input_element_key);
    }

    /**
     * Reports whether or not the submitted request has an input element key that matches the given value
     * 
     * @param  string  $input_element_key
     * @param  string  $value
     * @return bool
     */
    public function has_form_data_matching($input_element_key, $value) {
        // if the given element key does not exist in the post, return false
        if ( ! $this->has_form_data_key($input_element_key)) {
            return false;
        }
        
        // get raw form data
        $form_data = $this->form->get_data();

        return $form_data->$input_element_key == $value;
    }

    /**
     * Reports whether or not this request is a form cancellation
     * 
     * @return bool
     */
    public function is_form_cancellation()
    {
        if (empty($this->form)) {
            return false;
        }

        return (bool) $this->form->is_cancelled();
    }

    ////////////////////////////////////////
    ///
    /// REDIRECTS
    /// 
    ////////////////////////////////////////

    /**
     * Returns a redirect header to the given URL with the given message
     * 
     * @return (http redirect header)
     */
    public function redirect_to_url($url, $message)
    {
        redirect($url, $message, 2);
    }

}