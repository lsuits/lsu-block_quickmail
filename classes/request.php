<?php

class block_quickmail_request {

    public $form;

    public function __construct()
    {
        $this->form = null;
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
        $request = '\block_quickmail\requests\\' . $route_name;

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