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

    ////////////////////////////////////////
    ///
    /// REDIRECTS
    /// 
    ////////////////////////////////////////

    /**
     * Convenience wrapper for redirecting to moodle URLs
     * 
     * @param  string  $url
     * @param  array   $url_params   array of parameters for the given URL
     * @param  int     $delay        delay, in seconds, before redirecting
     * @return (http redirect header)
     */
    public function redirect_to_url($url, $url_params = [], $delay = 2) {
        $moodle_url = new \moodle_url($url, $url_params);

        redirect($moodle_url, '', $delay);
    }

    /**
     * Helper for redirecting to a course, or defaulting to the "my" page
     * 
     * @param  int  $course_id
     * @return (http redirect header)
     */
    public function redirect_to_course_or_my($course_id = 0) {
        if ($course_id) {
            $this->redirect_to_url('/course/view.php', ['id' => $course_id]);
        } else {
            $this->redirect_to_url('/my');
        }
    }

    /**
     * Convenience wrapper for redirecting to moodle URLs while including a status type and message
     * 
     * @param  string  $type         success|info|warning|error
     * @param  string  $message      a pre-rendered string message
     * @param  string  $url
     * @param  array   $url_params   array of parameters for the given URL
     * @param  int     $delay        delay, in seconds, before redirecting
     * @return (http redirect header)
     */
    public function redirect_as_type($type, $message, $url, $url_params = [], $delay = 2) {
        $types = [
            'success' => \core\output\notification::NOTIFY_SUCCESS,
            'info'    => \core\output\notification::NOTIFY_INFO,
            'warning' => \core\output\notification::NOTIFY_WARNING,
            'error'   => \core\output\notification::NOTIFY_ERROR,
        ];

        $moodle_url = new \moodle_url($url, $url_params);

        redirect($moodle_url, $message, $delay, $types[$type]);
    }

    /**
     * Helper function to redirect as type success
     */
    public function redirect_as_success($message, $url, $url_params = [], $delay = 2) {
        $this->redirect_as_type('success', $message, $url, $url_params, $delay);
    }

    /**
     * Helper function to redirect as type info
     */
    public function redirect_as_info($message, $url, $url_params = [], $delay = 2) {
        $this->redirect_as_type('info', $message, $url, $url_params, $delay);
    }

    /**
     * Helper function to redirect as type warning
     */
    public function redirect_as_warning($message, $url, $url_params = [], $delay = 2) {
        $this->redirect_as_type('warning', $message, $url, $url_params, $delay);
    }

    /**
     * Helper function to redirect as type error
     */
    public function redirect_as_error($message, $url, $url_params = [], $delay = 2) {
        $this->redirect_as_type('error', $message, $url, $url_params, $delay);
    }

}
