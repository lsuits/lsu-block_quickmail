<?php

namespace block_quickmail\repos\interfaces;

interface group_repo_interface {

    public static function get_course_user_selectable_groups($course, $user, $course_context = null);
    public static function get_course_user_groups($course, $user, $course_context = null);

}