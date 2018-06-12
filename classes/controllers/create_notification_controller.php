<?php

namespace block_quickmail\controllers;

use block_quickmail\controllers\support\base_controller;
use block_quickmail\controllers\support\controller_request;
use block_quickmail_string;
use block_quickmail\notifier\models\notification_model_helper;

class create_notification_controller extends base_controller {

    public static $views = [
        'select_type',
        'select_model',
        'select_object',
        'set_conditions',
        'create_schedule',
        'set_event_details',
        'create_message',
        'review',
    ];

    // dataz..
    // notification_type
    // notification_name
    // notification_model
    // condition_time_unit
    // condition_time_relation
    // condition_time_amount
    // condition_grade_greater_than
    // condition_grade_less_than

    public static $base_uri = '/blocks/quickmail/create_notification.php';

    /**
     * Select notification type and name
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function select_type(controller_request $request)
    {
        $form = $this->make_form('create_notification\select_type_form');

        if ($form->is_validated()) {
            return $this->post($request, 'select_type');
        }

        $this->render($form, [
            // 'heading' => 'a heading could go here!'
        ]);
    }

    /**
     * Handles post of select_type view
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function post_select_type(controller_request $request)
    {
        // persist inputs in session
        $this->store($request->input, [
            'notification_type',
            'notification_name',
        ]);

        // go to select model
        return $this->view($request, 'select_model');
    }

    /**
     * Select notification model type
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function select_model(controller_request $request)
    {
        // include model selection/keys available for the selected notification type
        $form = $this->make_form('create_notification\select_model_form', [
            'available_model_selection' => notification_model_helper::get_available_model_selection_by_type($this->input('notification_type')),
            'available_model_keys' => notification_model_helper::get_available_model_keys_by_type($this->input('notification_type'))
        ]);

        if ($form->is_validated()) {
            return $this->post($request, 'select_model');
        }

        $this->render($form, [
            'heading' => block_quickmail_string::get('select_notification_model')
        ]);
    }

    /**
     * Handles post of select_model view
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function post_select_model(controller_request $request)
    {
        // persist inputs in session
        $this->store($request->input, [
            'notification_model',
        ]);

        // if the selected model requires an object other than "user" or "course" (which are already available)
        if (notification_model_helper::model_requires_object($this->input('notification_type'), $this->input('notification_model'))) {
            return $this->view($request, 'select_object');
        }

        // no object required...
        
        // if the selected model requires conditions to be set
        if (notification_model_helper::model_requires_conditions($this->input('notification_type'), $this->input('notification_model'))) {
            // go to set conditions view
            return $this->view($request, 'set_conditions');
        }
        
        // no conditions required...
        
        switch ($this->input('notification_type')) {
            case 'reminder':
                // go to create schedule
                return $this->view($request, 'create_schedule');
                break;

            case 'event':
                // go to set event details
                return $this->view($request, 'set_event_details');
                break;
            
            default:
                // otherwise, something is broken :/ this should not happen unless session is cleared
                // send back to start
                return $this->view($request, 'select_type');
                break;
        }
    }

    // select_object

    /**
     * Set conditions for this notification
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function set_conditions(controller_request $request)
    {
        // include which condition keys should be required for the selected notification type/model
        $form = $this->make_form('create_notification\set_conditions_form', [
            'condition_keys' => notification_model_helper::get_condition_keys_for_model($this->input('notification_type'), $this->input('notification_model')),
        ]);

        if ($form->is_validated()) {
            return $this->post($request, 'set_conditions');
        }

        $this->render($form, [
            'heading' => block_quickmail_string::get('set_notification_conditions')
        ]);
    }

    /**
     * Handles post of set_conditions view
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function post_set_conditions(controller_request $request)
    {
        // persist inputs in session
        $this->store($request->input, [
            'condition_time_unit',
            'condition_time_relation',
            'condition_time_amount',
            'condition_grade_greater_than',
            'condition_grade_less_than',
        ]);

        switch ($this->input('notification_type')) {
            case 'reminder':
                // go to create schedule
                return $this->view($request, 'create_schedule');
                break;

            case 'event':
                // go to set event details
                return $this->view($request, 'set_event_details');
                break;
            
            default:
                // otherwise, something is broken :/ this should not happen unless session is cleared
                // send back to start
                return $this->view($request, 'select_type');
                break;
        }
    }

    /**
     * Create schedule for this notification
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function create_schedule(controller_request $request)
    {
        $form = $this->make_form('create_notification\create_schedule_form');

        if ($form->is_validated()) {
            return $this->post($request, 'create_schedule');
        }

        $this->render($form, [
            'heading' => block_quickmail_string::get('set_notification_schedule')
        ]);
    }




    
    
    /**
     * Returns the base URL which this controller's forms will target
     *
     * NOTE: this overrides the base controller method
     * 
     * @return array
     */
    public function get_form_url_params()
    {
        return ['courseid' => $this->props->course->id];
    }
    
}