<?php

namespace block_quickmail\controllers\support;

use block_quickmail\controllers\support\controller_request;
use block_quickmail\controllers\support\controller_form;
use block_quickmail\controllers\support\controller_component;
use block_quickmail\controllers\support\controller_session;
use moodle_url;

class base_controller {

    public static $forms_path = 'block_quickmail\controllers\forms';
    
    public static $views = [];

    public $context;
    public $props;
    public $session;

    public function __construct(&$page, $data = []) {
        $this->set_props($data);
        $this->session = new controller_session($this->get_store_key());
        // $page->navbar->add('add a nav node here...');
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
     * Handles a request to the static controller implementation
     * 
     * @param  object  &$page  the PAGE global, for manipulation of nav, etc.
     * @param  array   $data   optional, additional data to be included in controller
     * @return mixed
     */
    public static function handle(&$page, $data = [])
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

        // determine which view we are calling
        // if view name is empty, set to first view
        $view_name = $request->view_name ?: self::get_default_view();

        // call the view
        return $controller->call_view($view_name, $request);
    }

    /**
     * Returns rendered HTML for the given form as a component
     * 
     * @param  controller_form  $form
     * @param  array            $params   optional, any additional data to be passed to the renderer
     * @return string
     */
    public function render(controller_form $form, $params = [])
    {
        global $PAGE;
        
        $renderer = $PAGE->get_renderer('block_quickmail');

        $rendered = $renderer->controller_component(new controller_component($form, $params));

        // @TODO: include general notifications in the rendered output
        // $compose_form->render_error_notification();
        
        echo $rendered;
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

    /**
     * Calls the given "view name" method on the static controller implementation which should subsequently render the view
     *
     * Additionally renders the page header and footer
     * 
     * @param  string              $name
     * @param  controller_request  $request
     * @return string
     */
    private function call_view($name, controller_request $request)
    {
        global $OUTPUT;

        if ( ! method_exists($this, $name)) {
            throw new \Exception('controller view "' . $name . '"does not exist!');
        }

        echo $OUTPUT->header();
        
        call_user_func([$this, $name], $request);

        echo $OUTPUT->footer();
    }

    /**
     * Instantiates and return a controller_form instance of the given name
     *
     * Note: this will automatically include the current session input data as a "_customdata" prop on the form with key "input"
     * 
     * @param  string  $name  a form class name path (\controllers\forms = base path)
     * @param  array   $data  any additional data to be passed to the form
     * @return controller_form
     */
    public function make_form($name, $data = [])
    {
        $class = implode('\\', [self::$forms_path, $name]);

        return new $class($this->get_form_url(), $this->get_form_custom_data($name, $data), 'post', '', null, true, null);
    }

    /**
     * Returns the target url for controller_form's including any optional parameters set in the static controller implementation
     * 
     * @return string
     */
    private function get_form_url()
    {
        global $CFG;

        $moodle_url = new moodle_url($CFG->wwwroot . static::$base_uri, $this->get_form_url_params());

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
            'input' => $this->session->get_data()
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
     * Returns the default view name of the static controller implementation
     *
     * Note: this is the first view name in the controller implementation's list array
     * 
     * @return string
     */
    public static function get_default_view()
    {
        return reset(static::$views);
    }

    /**
     * Select notification type and name
     * 
     * @param  controller_request  $request
     * @param  string  $view_name
     * @return mixed
     */
    public function direct_to_view(controller_request $request, $view_name)
    {
        return $this->$view_name($request);
    }



    public function store_input($input, $keeps = [])
    {
        $data = array_filter((array) $input, function ($v, $k) use ($keeps) {
            return in_array($k, $keeps);
        }, ARRAY_FILTER_USE_BOTH);

        $this->session->add_data($data);
    }


    public function dump_store()
    {
        var_dump($this->session->get_data());
    }



    public static function get_views()
    {
        return static::$views;
    }

    public function dd($thing) { var_dump($thing);die; }

}