<?php

namespace block_quickmail\controllers;

use block_quickmail\controllers\support\base_controller;
use block_quickmail\controllers\support\controller_request;
use block_quickmail_string;
use block_quickmail\notifier\models\notification_model_helper;

class create_notification_controller extends base_controller {

    public static $base_uri = '/blocks/quickmail/create_notification.php';

    public static $view_data = [
        'select_type' => [
            'notification_type',
            'notification_name',
        ],
        'select_model' => [
            'notification_model',
        ],
        'select_object' => [
            'notification_object_id',
        ],
        'set_conditions' => [
            'condition_time_unit',
            'condition_time_relation',
            'condition_time_amount',
            'condition_grade_greater_than',
            'condition_grade_less_than',
        ],
        'create_schedule' => [
            'schedule_time_amount',
            'schedule_time_unit',
            'schedule_begin_at',
            'schedule_end_at',
            'schedule_max_per_interval',
        ],
        'set_event_details' => [
            'event_delay_time_unit',
            'event_delay_time_amount',
        ],
        'create_message' => [
            'message_type',
            'message_alternate_email_id',
            'message_subject',
            'message_signature_id',
            'message_body',
            'message_send_to_mentors',
            'message_no_reply',
        ],
        'review' => [
            'notification_is_enabled'
        ],
    ];

    /**
     * Returns the query string which this controller's forms will append to target URLs
     *
     * NOTE: this overrides the base controller method
     * 
     * @return array
     */
    public function get_form_url_params()
    {
        return ['courseid' => $this->props->course->id];
    }

    //////////////////////////////////
    ///
    ///  SELECT TYPE
    /// 
    //////////////////////////////////

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
            return $this->post($request, 'select_type', 'next');
        }

        $this->render($form, [
            // 'heading' => 'a heading could go here!'
        ]);
    }

    /**
     * Handles post of select_type form, next action
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function post_select_type_next(controller_request $request)
    {
        // if notification_type has changed
        if ($this->stored_has_changed($request->input, ['notification_type'])) {
            // reset data for all subsequent views
            $this->clear_store_after_view('select_type');
        }

        // persist inputs in session
        $this->store($request->input, $this->view_data_keys('select_type'));

        // go to select model
        return $this->view($request, 'select_model');
    }

    //////////////////////////////////
    ///
    ///  SELECT MODEL
    /// 
    //////////////////////////////////

    /**
     * Select notification model type
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function select_model(controller_request $request)
    {
        // include model keys available for the selected notification type
        $form = $this->make_form('create_notification\select_model_form', [
            'available_model_keys' => notification_model_helper::get_available_model_keys_by_type($this->stored('notification_type'))
        ]);

        // route the form submission, if any
        if ($form->is_validated_next()) {
            return $this->post($request, 'select_model', 'next');
        } else if ($form->is_submitted_back()) {
            return $this->post($request, 'select_model', 'back');
        }

        $this->render($form, [
            'heading' => block_quickmail_string::get('select_notification_model', block_quickmail_string::get('notification_type_' . $this->stored('notification_type')))
        ]);
    }

    /**
     * Handles post of select_model form, next action
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function post_select_model_next(controller_request $request)
    {
        // persist inputs in session
        $this->store($request->input, $this->view_data_keys('select_model'));

        // if the selected model requires an object other than "user" or "course" (which are already available)
        if (notification_model_helper::model_requires_object($this->stored('notification_type'), $this->stored('notification_model'))) {
            return $this->view($request, 'select_object');
        }

        // no object required...
        
        // if the selected model requires conditions to be set
        if (notification_model_helper::model_requires_conditions($this->stored('notification_type'), $this->stored('notification_model'))) {
            // go to set conditions view
            return $this->view($request, 'set_conditions');
        }
        
        // no conditions required...
        
        switch ($this->stored('notification_type')) {
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
     * Handles post of select_model form, back action
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function post_select_model_back(controller_request $request)
    {
        return $this->view($request, 'select_type');
    }

    //////////////////////////////////
    ///
    ///  SELECT OBJECT
    /// 
    //////////////////////////////////

    // select_object

    //////////////////////////////////
    ///
    ///  SET CONDITIONS
    /// 
    //////////////////////////////////

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
            'condition_keys' => notification_model_helper::get_condition_keys_for_model($this->stored('notification_type'), $this->stored('notification_model')),
        ]);

        // route the form submission, if any
        if ($form->is_validated_next()) {
            return $this->post($request, 'set_conditions', 'next');
        } else if ($form->is_submitted_back()) {
            return $this->post($request, 'set_conditions', 'back');
        }

        $this->render($form, [
            'heading' => block_quickmail_string::get('set_notification_conditions', (object) [
                'model' => block_quickmail_string::get('notification_model_reminder_' . $this->stored('notification_model')),
                'type' => block_quickmail_string::get('notification_type_' . $this->stored('notification_type'))
            ])
        ]);
    }

    /**
     * Handles post of set_conditions form, next action
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function post_set_conditions_next(controller_request $request)
    {
        // persist inputs in session
        $this->store($request->input, $this->view_data_keys('set_conditions'));

        switch ($this->stored('notification_type')) {
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
     * Handles post of set_conditions form, back action
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function post_set_conditions_back(controller_request $request)
    {
        // if the selected model requires object to be set
        if (notification_model_helper::model_requires_object($this->stored('notification_type'), $this->stored('notification_model'))) {
            // go to select object view
            return $this->view($request, 'select_object');
        }
        
        return $this->view($request, 'select_model');
    }

    //////////////////////////////////
    ///
    ///  CREATE SCHEDULE
    /// 
    //////////////////////////////////

    /**
     * Create schedule for this notification
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function create_schedule(controller_request $request)
    {
        $form = $this->make_form('create_notification\create_schedule_form');

        // route the form submission, if any
        if ($form->is_validated_next()) {
            return $this->post($request, 'create_schedule', 'next');
        } else if ($form->is_submitted_back()) {
            return $this->post($request, 'create_schedule', 'back');
        }

        $this->render($form, [
            'heading' => block_quickmail_string::get('set_notification_schedule', (object) [
                'model' => block_quickmail_string::get('notification_model_reminder_' . $this->stored('notification_model')),
                'type' => block_quickmail_string::get('notification_type_' . $this->stored('notification_type'))
            ])
        ]);
    }

    /**
     * Handles post of create_schedule form, next action
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function post_create_schedule_next(controller_request $request)
    {
        // persist inputs in session
        $this->store($request->input, $this->view_data_keys('create_schedule'));

        return $this->view($request, 'create_message');
    }

    /**
     * Handles post of create_schedule form, back action
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function post_create_schedule_back(controller_request $request)
    {
        // persist inputs in session
        $this->store($request->input, $this->view_data_keys('create_schedule'));

        return $this->view($request, 'create_message');
    }
    
}