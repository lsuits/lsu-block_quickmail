<?php

namespace block_quickmail\repos\interfaces;

interface course_repo_interface {

    public static function get_user_course_array($user, $active_only = false);

}