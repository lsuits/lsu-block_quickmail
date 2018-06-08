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
        // 'select_name',
        // 'select_size',
        // 'select_color',
        'review',
    ];

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

        // if form was submitted and is validated
        if ($form->is_validated()) {
            // persist inputs in session
            $this->store_input($request->input, [
                'notification_type',
                'notification_name',
            ]);

            // go to select model
            return $this->direct_to_view($request, 'select_model');
        }

        $this->render($form, [
            // 'heading' => 'a heading could go here!'
        ]);
    }

    /**
     * Select notification model type
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function select_model(controller_request $request)
    {
        $form = $this->make_form('create_notification\select_model_form');

        // if form was submitted and is validated
        if ($form->is_validated()) {
            // persist inputs in session
            $this->store_input($request->input, [
                'notification_model',
            ]);

            $object_type = notification_model_helper::get_object_type_for_model($this->session->get_data('notification_type'), $this->session->get_data('notification_model'));

            // if the selected notification_model does not require an object other than user or course
            if (in_array($object_type, ['user', 'course'])) {
                return $this->direct_to_view($request, 'set_conditions');
            } else {
                return $this->direct_to_view($request, 'select_object');
            }
        }

        $this->render($form, [
            'heading' => block_quickmail_string::get('select_notification_model')
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