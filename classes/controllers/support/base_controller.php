<?php

namespace block_quickmail\controllers\support;

use block_quickmail\controllers\support\controller_request;
use block_quickmail\controllers\support\controller_form;
use block_quickmail\controllers\support\controller_form_component;
use block_quickmail\controllers\support\controller_session;
use moodle_url;

class base_controller {

    public static $forms_path = 'block_quickmail\controllers\forms';
    public static $views = [];
    public static $actions = [];

    public $context;
    public $props;
    public $session;
    public $form_errors;

    public function __construct(&$page, $data = []) {
        $this->set_props($data);
        $this->session = new controller_session($this->get_store_key());
        $this->form_errors = [];
        // $page->navbar->add('add a nav node here...');
    }

    ////////////////////////////////////////////
    ///
    ///  CONTROLLER INSTANTIATION
    /// 
    ////////////////////////////////////////////

    /**
     * Handles a request to the static controller implementation
     * 
     * @param  object  &$page   the PAGE global, for manipulation of nav, etc.
     * @param  array   $data    optional, additional data to be included in controller
     * @param  string  $action  optional, explicit controller action
     * @return mixed
     */
    public static function handle(&$page, $data = [], $action = '')
    {
        $controller = new static($page, $data);
        
        // persist any session data to the next request
        $controller->session->reflash();

        $request = controller_request::make();

        // if no view name is present in the request, we can assume that this is a fresh entrance to the controller
        if ( ! $request->view_name) {
            // clear any session data for this controller
            $controller->session->clear();

            // set the view to the controller's default
            $view_name = self::get_default_view();
        
        // otherwise, use the requested view
        } else {

            $view_name = $request->view_name;
        }

        // if action is relevant to controller
        if (in_array($action, static::$actions)) {
            return $controller->call_action($action, $request);
        }

        // determine which view we are calling
        // if view name is empty, set to first view
        $view_name = $request->view_name ?: self::get_default_view();

        // call the view
        return $controller->call_view($view_name, $request);
    }

    ////////////////////////////////////////////
    ///
    ///  VIEW METHOD DIRECTIVES
    /// 
    ////////////////////////////////////////////

    /**
     * Calls the given "view_name" which should be a controller method
     * 
     * @param  controller_request  $request
     * @param  string  $view_name
     * @return mixed
     */
    public function view(controller_request $request, $view_name)
    {
        return $this->$view_name($request);
    }

    /**
     * Calls the given post_{view_name}_{action} which should be a controller method
     * 
     * @param  controller_request  $request
     * @param  string  $view_name
     * @param  string  $action   back|next
     * @param  array   $override_inputs       additional params to be included in the request input (useful for handling moodle-form-specific inputs)
     * @return mixed
     */
    public function post(controller_request $request, $view_name, $action, $override_inputs = [])
    {
        foreach ($override_inputs as $key => $value) {
            $request->input->$key = $value;
        }

        return $this->{ 'post_' . $view_name . '_' . $action }($request);
    }

    /**
     * Calls the given "action" method on the static controller implementation
     *
     * Additionally renders the page header and footer
     *
     * @param  string              $action_name
     * @param  controller_request  $request
     * @return mixed
     */
    private function call_action($action_name, controller_request $request)
    {
        $action_name = 'action_' . $action_name;

        if ( ! method_exists($this, $action_name)) {
            throw new \Exception('controller action "' . $action_name . '"does not exist!');
        }

        global $OUTPUT;

        echo $OUTPUT->header();

        call_user_func([$this, $action_name], $request);

        echo $OUTPUT->footer();
    }

    /**
     * Calls the given "view name" method on the static controller implementation which should subsequently render the view
     *
     * Additionally renders the page header and footer
     * 
     * @param  string              $view_name
     * @param  controller_request  $request
     * @return string
     */
    private function call_view($view_name, controller_request $request)
    {
        if ( ! method_exists($this, $view_name)) {
            throw new \Exception('controller view "' . $view_name . '"does not exist!');
        }

        global $OUTPUT;

        echo $OUTPUT->header();
        
        call_user_func([$this, $view_name], $request);

        echo $OUTPUT->footer();
    }

    /**
     * Returns the default view name of the static controller implementation
     *
     * Note: this is the first view name in the controller implementation's list array
     * 
     * @return string
     */
    public static function get_default_view()
    {
        return key(static::$views);
    }

    ////////////////////////////////////////////
    ///
    ///  CANCEL METHOD DIRECTIVE
    /// 
    ////////////////////////////////////////////

    /**
     * Calls the given "view_name" which should be a controller method
     * 
     * @return mixed
     */
    public function cancel()
    {
        $this->session->clear();

        // set the view to the controller's default
        $view_name = self::get_default_view();

        $request = controller_request::make();
        
        return $this->$view_name($request);
    }

    ////////////////////////////////////////////
    ///
    ///  VIEW DATA
    /// 
    ////////////////////////////////////////////

    public function view_keys()
    {
        return array_keys(static::$views);
    }

    public function view_data_keys($view)
    {
        return static::$views[$view];
    }

    ////////////////////////////////////////////
    ///
    ///  RENDERING
    /// 
    ////////////////////////////////////////////

    /**
     * Returns rendered HTML for the given form as a component
     * 
     * @param  controller_form  $form
     * @param  array            $params   optional, any additional data to be passed to the renderer
     * @return string
     */
    public function render_form(controller_form $form, $params = [])
    {
        global $PAGE;
        
        $renderer = $PAGE->get_renderer('block_quickmail');

        $rendered = $renderer->controller_form_component(new controller_form_component($form, $params));

        $this->render_form_error_notification();
        
        echo $rendered;
    }

    /**
     * Returns rendered HTML for the given component
     * 
     * @param  string    $component_name
     * @param  array     $params             optional, any additional data to be passed to the renderer
     * @return string
     */
    public function render_component($component_name, $params = [])
    {
        global $PAGE;

        $renderer = $PAGE->get_renderer('block_quickmail');

        $rendered = $renderer->controller_component_template($component_name, $params);
        
        echo $rendered;
    }

    /**
     * Renders a moodle error notification for any form errors
     * 
     * @return string
     */
    public function render_form_error_notification()
    {
        if ($this->form_errors) {
            $html = '<ul style="margin-bottom: 0px;">';
            
            foreach ($this->form_errors as $error) {
                $html .= '<li>' . $error . '</li>';
            }

            $html .= '</ul>';
            
            \core\notification::error($html);
        }
    }

    ////////////////////////////////////////////
    ///
    ///  FORM INSTANTIATION
    /// 
    ////////////////////////////////////////////

    /**
     * Instantiates and return a controller_form instance of the given name
     *
     * Note: this will automatically include the current session input data as a "_customdata" prop on the form with key "stored"
     * 
     * @param  string  $name            a form class name path (\controllers\forms = base path)
     * @param  array   $data            any additional data to be passed to the form
     * @param  string  $target_action   optional, action directive to include on form target URL
     * @return controller_form
     */
    public function make_form($name, $data = [], $target_action = '')
    {
        $class = implode('\\', [self::$forms_path, $name]);

        $target_params = in_array($target_action, static::$actions)
            ? array_merge(['action' => $target_action], $this->get_form_url_params())
            : $this->get_form_url_params();

        $query_string = ! empty($target_params)
            ? '?' . http_build_query($target_params, '', '&')
            : '';

        return new $class($query_string, $this->get_form_custom_data($name, $data), 'post', '', null, true, null);
    }

    /**
     * Returns the target url for controller_form's including any optional parameters set in the static controller implementation
     * 
     * @return string
     */
    private function get_form_url()
    {
        global $CFG;

        $moodle_url = new moodle_url(static::$base_uri, $this->get_form_url_params());

        return $moodle_url->out();
    }

    /**
     * Returns an array of custom data to be passed to a controller_form, prepending the appropriate "view_form_name"
     * 
     * @param  string  $name  a form class name path (\controllers\forms = base path)
     * @param  array   $data  any additional data to be passed to the form
     * @return array
     */
    private function get_form_custom_data($name, $data = [])
    {
        // merge in the current session input data
        return array_merge($data, [
            'view_form_name' => $this->get_form_view_name_from_path($name),
            'stored' => $this->session->get_data()
        ]);
    }

    /**
     * Returns the "view_form_name" short name from the given path
     * 
     * @param  string  $path
     * @return string
     */
    private function get_form_view_name_from_path($path)
    {
        $parts = explode('\\', $path);

        return end($parts);
    }
    
    /**
     * Returns default form url params
     *
     * This method should be included on the static controller implementation if any custom query strings
     * are necessary (ex: courseid)
     * 
     * @return array
     */
    public function get_form_url_params()
    {
        return [];
    }

    ////////////////////////////////////////////
    ///
    ///  SESSION INPUT
    /// 
    ////////////////////////////////////////////

    /**
     * Stores the given input array's specified keys in the session input
     * 
     * @param  array  $input
     * @param  array  $keeps       key names to keep, others will be ignored
     * @param  array  $overrides   optional keyed array of params to override any input given
     * @return void
     */
    public function store($input, $keeps = [], $overrides = [])
    {
        // filter out any unwanted params from input
        $data = \block_quickmail_plugin::array_filter_key((array) $input, function ($k) use ($keeps) {
            return in_array($k, $keeps);
        });

        // fill any wanted data keys that do not exist in the filtered params with a default
        foreach ($keeps as $k) {
            if ( ! in_array($k, array_keys($data))) {
                $data[$k] = '';
            }
        }

        $data = array_merge($data, $overrides);

        $this->session->add_data($data);
    }

    /**
     * Returns this controller's session input for a given key
     * 
     * @param  string  $key  optional, if null, will return an array of all data
     * @return mixed
     */
    public function stored($key = null)
    {
        return $this->session->get_data($key);
    }

    /**
     * Reports whether or not any of the given request input data is different for the given keys
     * 
     * @param  stdClass  $request_input
     * @param  array     $keys             keys to check for change
     * @return bool
     */
    public function stored_has_changed($request_input, $keys = [])
    {
        foreach ($keys as $key) {
            if ($this->session->has_data($key)) {
                if ($request_input->$key !== $this->stored($key)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Removes all data from the current session that is associated with views after the given view
     * 
     * @param  string  $view
     * @return void
     */
    public function clear_store_after_view($view)
    {
        $reset = false;

        // iterate through this controller's view keys
        foreach ($this->view_keys() as $view_key) {
            // if this is the key, flag to remove all data from this point on
            if ($view_key == $view) {
                $reset = true;
                continue;
            }

            // if resetting, remove all values for each data key
            if ($reset) {
                foreach ($this->view_data_keys($view_key) as $view_data_key) {
                    $this->session->forget_data($view_data_key);
                }
            }
        }
    }

    ////////////////////////////////////////////
    ///
    ///  HELPERS
    /// 
    ////////////////////////////////////////////

    /**
     * Sets the controllers properties upon instantiation
     * 
     * @param array $payload
     */
    private function set_props($payload = []) {
        $this->context = null;
        $this->props = (object)[];

        foreach ($payload as $key => $value) {
            switch ($key) {
                case 'context':
                    $this->context = $value;
                    break;
                
                default:
                    $this->props->$key = $value;
                    break;
            }
        }
    }

    /**
     * Returns the static controller implementation's "session key"
     *
     * Note: this is the controller's class name
     * 
     * @return string
     */
    private function get_store_key()
    {
        $parts = explode('\\', get_called_class());

        return end($parts);
    }

    /**
     * Returns the static controller's short name
     * 
     * @return string
     */
    public static function get_controller_short_name()
    {
        return str_replace('_controller', '', explode('\\', static::class)[2]);
    }

    public function dd($thing) { var_dump($thing);die; }

}