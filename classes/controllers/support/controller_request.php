<?php

namespace block_quickmail\controllers\support;

class controller_request {

    public $view_name;
    public $view_form_name;
    public $post;
    public $input;

    public function __construct() {
        $this->set_view_props();
        $this->set_input();
    }

    /**
     * Instantiate and return a new request instance
     * 
     * @return controller_request
     */
    public static function make()
    {
        $request = new self();

        return $request;
    }

    /**
     * Sets view name and form name properties on the request
     */
    private function set_view_props()
    {
        $this->view_form_name = ! empty($_POST) && array_key_exists('view_form_name', $_POST)
            ? $_POST['view_form_name']
            : '';

        $this->view_name = $this->view_form_name
            ? substr($this->view_form_name, 0, -5)
            : '';
    }

    /**
     * Sets any relevant submitted input on the request
     */
    private function set_input()
    {
        $this->input = ! empty($_POST)
            ? $this->filter_input($_POST)
            : (object)[];
    }

    /**
     * Reports whether or not this request includes form input of a given key
     * 
     * @param  string  $key  the input's key
     * @return bool
     */
    public function has_input($key)
    {
        return property_exists($this->input, $key);
    }

    /**
     * Returns filtered post input given an array of posted data
     * 
     * @param  array  $post
     * @return \stdClass
     */
    private function filter_input($post)
    {
        $input = (object)[];

        // strip out a few keys from the post...
        foreach ($post as $key => $value) {
            if (in_array($key, ['view_form_name', 'sesskey'])) {
                continue;
            } else if (strpos($key, '_qf__') === 0) {
                continue;
            }

            $input->$key = $value;
        }

        return $input;
    }

}
