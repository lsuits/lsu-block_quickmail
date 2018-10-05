<?php

namespace block_quickmail\controllers;

use block_quickmail\controllers\support\base_controller;
use block_quickmail\controllers\support\controller_request;
use block_quickmail\repos\notification_repo;
use block_quickmail_string;
use block_quickmail_config;
use block_quickmail_plugin;
use block_quickmail\notifier\models\notification_model_helper;
use block_quickmail\notifier\notification_condition;
use block_quickmail\validators\edit_notification_form_validator;

class edit_notification_controller extends base_controller {

    public static $base_uri = '/blocks/quickmail/edit_notification.php';

    public static $views = [
        'edit_notification' => [],
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
        return [
            'courseid' => $this->props->course->id,
            'id' => $this->props->notification_id
        ];
    }

    /**
     * Edit notification
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function edit_notification(controller_request $request)
    {
        // grab the notification which must belong to this course and user
        if ( ! $notification = notification_repo::get_notification_for_course_user_or_null($this->props->notification_id, $this->props->course->id, $this->props->user->id)) {
            // redirect back to index with error
            $request->redirect_as_error(block_quickmail_string::get('notification_not_found'), static::$base_uri, $this->get_form_url_params());
        }

        // get this notification's type interface
        $notification_type_interface = $notification->get_notification_type_interface();

        $form = $this->make_form('edit_notification\edit_notification_form', [
            'context' => $this->context,
            'notification' => $notification,
            'notification_type' => $notification->get('type'),
            'is_one_time_event' => $notification->get('type') == 'event'
                ? $notification_type_interface->is_one_time_event()
                : false,
            'notification_object_type' => notification_model_helper::get_object_type_for_model($notification->get('type'), $notification_type_interface->get('model')),
            'notification_type_interface' => $notification_type_interface,
            'schedule' => $notification_type_interface->is_schedulable() ? $notification_type_interface->get_schedule() : null,
            'required_condition_keys' => notification_condition::get_required_condition_keys($notification->get('type'), str_replace('-', '_', $notification_type_interface->get('model'))),
            'assigned_conditions' => notification_condition::decode_condition_string($notification->get('conditions')),
            'editor_options' => block_quickmail_config::get_editor_options($this->context),
            'allow_mentor_copy' => block_quickmail_plugin::user_can_send('compose', $this->props->user, $this->context, false),
            'course_config_array' => block_quickmail_config::get('', $this->props->course),
        ]);

        // route the form submission, if any
        if ($form->is_validated_next()) {
            // further validation on the update notification input
            $validator = new edit_notification_form_validator($request->input, [
                'notification_type' => $notification->get('type'),
                'substitution_code_classes' => ['user', 'course'], // @TODO : make this work!!
                'required_condition_keys' => notification_condition::get_required_condition_keys($notification->get('type'), str_replace('-', '_', $notification_type_interface->get('model'))),
            ]);
            $validator->validate();

            // if no errors, post update
            if ( ! $validator->has_errors()) {
                return $this->post($request, 'edit_notification', 'next');
            }

            // otherwise, save errors for render below
            $this->form_errors = $validator->errors;

        } else if ($form->is_cancelled()) {
            // redirect back to notification list
            $request->redirect_to_url('/blocks/quickmail/notifications.php', ['courseid' => $this->props->course->id]);
        }

        $this->render_form($form);
    }

    /**
     * Handles post of edit notification form
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function post_edit_notification_next(controller_request $request)
    {
        // grab the notification which must belong to this course and user
        if ( ! $notification = notification_repo::get_notification_for_course_user_or_null($this->props->notification_id, $this->props->course->id, $this->props->user->id)) {
            // redirect back to index with error
            $request->redirect_as_error(block_quickmail_string::get('notification_not_found'), '/blocks/quickmail/notifications.php', ['courseid' => $this->props->course->id]);
        }

        // attempt to update the notification
        try {
            $notification->update_by_user($this->props->user, (array) $request->input);
        } catch (\Exception $e) {
            $request->redirect_as_error($e->getMessage(), '/blocks/quickmail/edit_notification.php', ['courseid' => $this->props->course->id, 'id' => $notification->get('id')]);
        }

        $request->redirect_as_success(block_quickmail_string::get('notification_updated'), '/blocks/quickmail/notifications.php', ['courseid' => $this->props->course->id]);

    }

}