<?php

namespace block_quickmail\repos\interfaces;

interface role_repo_interface {

    public static function get_course_selectable_roles($course, $course_context = null);

}