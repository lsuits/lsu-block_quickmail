<?php

namespace block_quickmail\repos\interfaces;

use block_quickmail\repos\repo;
use context_course;
use block_quickmail\repos\role_repo;
use block_quickmail\repos\group_repo;

interface user_repo_interface {

    public static function get_course_user_selectable_users($course, $user, $course_context = null);
    public static function get_course_users($course_context, $active_only = true, $user_fields = null, $group_id = 0);
    public static function get_course_group_users($course_context, $group_id, $active_only = true, $user_fields = null);
    public static function get_course_role_users($course_context, $role_id, $active_only = true, $user_fields = null);
    public static function get_unique_course_user_ids_from_selected_entities($course, $user, $included_entity_ids = [], $excluded_entity_ids = []);
    public static function get_mentors_of_user($user);

}