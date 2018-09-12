<?php

namespace block_quickmail\controllers;

use block_quickmail\controllers\support\base_controller;
use block_quickmail\controllers\support\controller_request;
use block_quickmail_config;
use block_quickmail\repos\course_repo;
use block_quickmail\repos\sent_repo;
use block_quickmail\persistents\message;

class sent_message_index_controller extends base_controller {

    public static $base_uri = '/blocks/quickmail/sent.php';

    public static $views = [
        'sent_message_index' => [],
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
        return ['courseid' => $this->props->course_id];
    }

    /**
     * Manage sent messages
     * 
     * @param  controller_request  $request
     * @return mixed
     */
    public function sent_message_index(controller_request $request)
    {
        // get all (queued) messages belonging to this user and course
        $messages = sent_repo::get_for_user($this->props->user->id, $this->props->course_id, [
            'sort' => $this->props->page_params['sort'], 
            'dir' => $this->props->page_params['dir'],
            'paginate' => true,
            'page' => $this->props->page_params['page'], 
            'per_page' => $this->props->page_params['per_page'],
            'uri' => $_SERVER['REQUEST_URI']
        ]);

        // filter out messages not in this course
        $filtered_messages = message::filter_messages_by_course($messages->data, $this->props->course_id);

        // get this user's courses
        $user_course_array = course_repo::get_user_course_array($this->props->user);

        $this->render_component('sent_message_index', [
            'messages' => $filtered_messages,
            'user_course_array' => $user_course_array,
            'course_id' => $this->props->course_id,
            'user' => $this->props->user,
            'pagination' => $messages->pagination,
            'sort_by' => $this->props->page_params['sort'],
            'sort_dir' => $this->props->page_params['dir'],
        ]);
    }

}