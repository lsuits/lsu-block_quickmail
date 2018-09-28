<?php

namespace block_quickmail\controllers;

use block_quickmail\controllers\support\base_controller;
use block_quickmail\controllers\support\controller_request;
use block_quickmail\repos\notification_repo;
use block_quickmail\persistents\notification;
use block_quickmail_string;

class notification_index_controller extends base_controller {

    public static $base_uri = '/blocks/quickmail/notifications.php';

    public static $views = [
        'notification_index' => [],
    ];

    public static $actions = [
        'disable',
        'enable',
        'delete',
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

    /**
     * Manage draft messages
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function notification_index(controller_request $request)
    {
        // get all notifications belonging to this course user
        $notifications = notification_repo::get_all_for_course($this->props->course->id, $this->props->user->id, [
            'sort' => $this->props->page_params['sort'], 
            'dir' => $this->props->page_params['dir'],
            'paginate' => true,
            'page' => $this->props->page_params['page'], 
            'per_page' => $this->props->page_params['per_page'],
            'uri' => $_SERVER['REQUEST_URI']
        ]);

        $this->render_component('notification_index', [
            'notifications' => $notifications->data,
            'courseid' => $this->props->course->id,
            'user' => $this->props->user,
            'pagination' => $notifications->pagination,
            'sort_by' => $this->props->page_params['sort'],
            'sort_dir' => $this->props->page_params['dir'],
        ]);
    }

    /**
     * Disable notification action
     * 
     * @param  controller_request  $request
     * @return void
     */
    public function action_disable(controller_request $request)
    {
        $this->handle_status_action($request, 'disable');
    }

    /**
     * Enable notification action
     * 
     * @param  controller_request  $request
     * @return void
     */
    public function action_enable(controller_request $request)
    {
        $this->handle_status_action($request, 'enable');
    }

    /**
     * Delete notification action
     * 
     * @param  controller_request  $request
     * @return void
     */
    public function action_delete(controller_request $request)
    {
        if ($notification = notification::find_or_null($this->props->page_params['notificationid'])) {
            $notification->delete_self();
        }

        // redirect back to index as success
        $request->redirect_as_success(block_quickmail_string::get('notification_deleted'), static::$base_uri, $this->get_form_url_params());
    }

    /**
     * Handles the given status edit action
     * 
     * @param  controller_request $request
     * @param  string             $type       enable|disable
     * @return void
     */
    private function handle_status_action(controller_request $request, $type)
    {
        // validate action
        if ( ! in_array($type, ['enable', 'disable'])) {
            // redirect back to index with error
            $request->redirect_as_error('Invalid action!', static::$base_uri, $this->get_form_url_params());
        }

        // grab the notification which must belong to this course and user
        if ( ! $notification = notification_repo::get_notification_for_course_user_or_null($this->props->page_params['notificationid'], $this->props->course->id, $this->props->user->id)) {
            // redirect back to index with error
            $request->redirect_as_error(block_quickmail_string::get('notification_not_found'), static::$base_uri, $this->get_form_url_params());
        }

        // handle the action
        if ($type == 'enable') {
            $notification->enable();
        } else {
            $notification->disable();
        }

        // redirect back to index as success
        $request->redirect_as_success(block_quickmail_string::get('notification_updated'), static::$base_uri, $this->get_form_url_params());
    }

}