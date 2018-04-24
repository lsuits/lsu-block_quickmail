<?php

namespace block_quickmail\repos\interfaces;

interface sent_repo_interface {

    public static function get_for_user($user_id, $course_id = 0, $params = []);

}