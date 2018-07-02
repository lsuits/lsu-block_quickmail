<?php

namespace block_quickmail\controllers;

use block_quickmail\controllers\support\base_controller;
use block_quickmail\controllers\support\controller_request;
use block_quickmail_plugin;
use block_quickmail_config;
use block_quickmail_string;
use block_quickmail\notifier\models\notification_model_helper;
use block_quickmail\notifier\notification_condition_summary;
use block_quickmail\notifier\notification_schedule_summary;
use block_quickmail\persistents\alternate_email;
use block_quickmail\persistents\signature;
use block_quickmail\persistents\reminder_notification;
use block_quickmail\persistents\event_notification;

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
            'message_alternate_email_id',
            'message_subject',
            'message_body',
            'message_type',
            'message_signature_id',
            'message_send_to_mentors',
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

        // route the form submission, if any
        if ($form->is_validated_next()) {
            return $this->post($request, 'select_type', 'next');
        } else if ($form->is_cancelled()) {
            return $this->cancel();
        }

        $this->render($form);
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
        } else if ($form->is_cancelled()) {
            return $this->cancel();
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
                'model' => block_quickmail_string::get('notification_model_' . $this->stored('notification_type') . '_' . $this->stored('notification_model')),
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
            // grab the manipulated moodle post to handle timestamp conversion
            $form_data = $form->get_data();

            return $this->post($request, 'create_schedule', 'next', [
                'schedule_begin_at' => ! empty($form_data->schedule_begin_at) ? $form_data->schedule_begin_at : '',
                'schedule_end_at' => ! empty($form_data->schedule_end_at) ? $form_data->schedule_end_at : '',
            ]);
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
        // if the selected model requires conditions to be set
        if (notification_model_helper::model_requires_conditions($this->stored('notification_type'), $this->stored('notification_model'))) {
            // go to set conditions view
            return $this->view($request, 'set_conditions');
        }

        // if the selected model requires object to be set
        if (notification_model_helper::model_requires_object($this->stored('notification_type'), $this->stored('notification_model'))) {
            // go to select object view
            return $this->view($request, 'select_object');
        }
        
        return $this->view($request, 'select_model');
    }

    //////////////////////////////////
    ///
    ///  CREATE MESSAGE
    /// 
    //////////////////////////////////

    /**
     * Create message details for this notification
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function create_message(controller_request $request)
    {
        // get the user's default signature id, if any, defaulting to 0
        if ($signature = signature::get_default_signature_for_user($this->props->user->id)) {
            $user_default_signature_id = $signature->get('id');
        } else {
            $user_default_signature_id = 0;
        }

        $form = $this->make_form('create_notification\create_message_form', [
            'editor_options' => block_quickmail_config::get_editor_options($this->context),
            // get config variables for this course, defaulting to block level
            'course_config_array' => block_quickmail_config::get('', $this->props->course),
            // get the user's available alternate emails for this course
            'user_alternate_email_array' => alternate_email::get_flat_array_for_course_user($this->props->course->id, $this->props->user),
            // get the user's current signatures as array (id => title)
            'user_signature_array' => signature::get_flat_array_for_user($this->props->user->id),
            'user_default_signature_id' => $user_default_signature_id,
            // only allow users with hard set capabilities (not students) to copy mentors
            'allow_mentor_copy' => block_quickmail_plugin::user_can_send('compose', $this->props->user, $this->context, false)
        ]);

        // route the form submission, if any
        if ($form->is_validated_next()) {
            return $this->post($request, 'create_message', 'next');
        } else if ($form->is_submitted_back()) {
            return $this->post($request, 'create_message', 'back');
        }

        $this->render($form, [
            'heading' => block_quickmail_string::get('create_notification_message', (object) [
                'model' => block_quickmail_string::get('notification_model_' . $this->stored('notification_type') . '_' . $this->stored('notification_model')),
                'type' => block_quickmail_string::get('notification_type_' . $this->stored('notification_type'))
            ])
        ]);
    }

    /**
     * Handles post of create_message form, next action
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function post_create_message_next(controller_request $request)
    {
        // persist inputs in session
        $this->store($request->input, $this->view_data_keys('create_message'), [
            'message_body' => $request->input->message_body['text']
        ]);

        return $this->view($request, 'review');
    }

    /**
     * Handles post of create_message form, back action
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function post_create_message_back(controller_request $request)
    {
        if ($this->stored('notification_type') == 'reminder') {
            // go to set conditions view
            return $this->view($request, 'create_schedule');
        }

        if ($this->stored('notification_type') == 'reminder') {
            // go to set conditions view
            return $this->view($request, 'set_event_details');
        }

        return $this->view($request, 'select_model');
    }

    //////////////////////////////////
    ///
    ///  REVIEW
    /// 
    //////////////////////////////////

    /**
     * Show summary page for this notification yet to be created
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function review(controller_request $request)
    {
        // build condition params
        $condition_params = [];
        foreach ($this->view_data_keys('set_conditions') as $key) {
            $condition_params[str_replace('condition_', '', $key)] = $this->stored($key);
        }

        $form = $this->make_form('create_notification\review_form', [
            'condition_summary' => notification_condition_summary::get_model_condition_summary($this->stored('notification_type'), $this->stored('notification_model'), $condition_params),
            'schedule_summary' => notification_schedule_summary::get_from_params([
                'time_amount' => $this->stored('schedule_time_amount'),
                'time_unit' => $this->stored('schedule_time_unit'),
                'begin_at' => $this->stored('schedule_begin_at'),
                'end_at' => $this->stored('schedule_end_at'),
            ])
        ]);

        // list of form submission actions that may be handled in addition to "back" or "next"
        $actions = [
            'edit_select_type',
            'edit_set_conditions',
            'edit_create_schedule',
            'edit_create_message',
        ];

        // route the form submission, if any
        if ($form->is_submitted_action('edit_select_type', $actions)) {
            return $this->post($request, 'review', 'edit_select_type');
        } else if ($form->is_submitted_action('edit_set_conditions', $actions)) {
            return $this->post($request, 'review', 'edit_set_conditions');
        } else if ($form->is_submitted_action('edit_create_schedule', $actions)) {
            return $this->post($request, 'review', 'edit_create_schedule');
        } else if ($form->is_submitted_action('edit_create_message', $actions)) {
            return $this->post($request, 'review', 'edit_create_message');
        } else if ($form->is_validated_next()) {
            return $this->post($request, 'review', 'next');
        }

        $this->render($form, [
            'heading' => block_quickmail_string::get('notification_review')
        ]);
    }

    /**
     * Handles post of review form, edit_select_type action
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function post_review_edit_select_type(controller_request $request)
    {
        return $this->view($request, 'select_type');
    }

    /**
     * Handles post of review form, edit_set_conditions action
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function post_review_edit_set_conditions(controller_request $request)
    {
        return $this->view($request, 'set_conditions');
    }

    /**
     * Handles post of review form, edit_create_schedule action
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function post_review_edit_create_schedule(controller_request $request)
    {
        return $this->view($request, 'create_schedule');
    }

    /**
     * Handles post of review form, edit_create_message action
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function post_review_edit_create_message(controller_request $request)
    {
        return $this->view($request, 'create_message');
    }

    /**
     * Handles post of review form, next action (final submit)
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function post_review_next(controller_request $request)
    {
        // persist inputs in session
        $this->store($request->input, $this->view_data_keys('review'));

        try {
            // create a notification
            $this->stored('notification_type') == 'reminder'
                ? $this->create_reminder_notification()
                : $this->create_event_notification();

        } catch (\Exception $e) {
            var_dump($e->getMessage());die;
        }

        $request->redirect_as_success('Notification created!', '/course/view.php', ['id' => $this->props->course->id]);
    }

    /**
     * Creates and returns a reminder notification model from this controller's session data
     * 
     * @return reminder_notification
     */
    private function create_reminder_notification()
    {
        return reminder_notification::create_type(
            str_replace('_', '-', $this->stored('notification_model')), 
            $this->props->course, // need to pull the object here!
            $this->props->course, 
            $this->props->user, 
            array_merge($this->get_notification_params(), [
                'schedule_unit' => $this->stored('schedule_time_unit'),
                'schedule_amount' => (int) $this->stored('schedule_time_amount'),
                'schedule_begin_at' => (int) $this->stored('schedule_begin_at'),
                'schedule_end_at' => (int) $this->stored('schedule_end_at'),
                'max_per_interval' => (int) $this->stored('schedule_max_per_interval'),
            ])
        );
    }

    /**
     * Creates and returns an event notification model from this controller's session data
     * 
     * @return event_notification
     */
    private function create_event_notification()
    {
        return event_notification::create_type(
            str_replace('_', '-', $this->stored('notification_model')), 
            $this->props->course, 
            $this->props->course, // need to pull the object here!
            $this->props->user, 
            array_merge($this->get_notification_params(), [
                // 'time_delay' => $this->stored('event_time_delay'),
            ])
        );
    }

    /**
     * Returns an array of general notification creation params from this controller's session data
     * 
     * @return array
     */
    private function get_notification_params()
    {
        return [
            'name' => $this->stored('notification_name'),
            'message_type' => $this->stored('message_type'),
            'subject' => $this->stored('message_subject'),
            'body' => $this->stored('message_body'),
            'is_enabled' => (int) $this->stored('notification_is_enabled'),
            'alternate_email_id' => (int) $this->stored('message_alternate_email_id'),
            'signature_id' => (int) $this->stored('message_signature_id'),
            'editor_format' => 1,
            'send_receipt' => 0,
            'send_to_mentors' => (int) $this->stored('message_send_to_mentors'),
            'no_reply' => 1,
            'condition_time_unit' => $this->stored('condition_time_unit'),
            'condition_time_amount' => (int) $this->stored('condition_time_amount'),
            'condition_time_relation' => $this->stored('condition_time_relation'),
            'condition_grade_greater_than' => $this->stored('condition_grade_greater_than'),
            'condition_grade_less_than' => $this->stored('condition_grade_less_than'),
        ];
    }
    
}