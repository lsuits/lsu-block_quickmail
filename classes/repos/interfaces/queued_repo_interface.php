<?php

namespace block_quickmail\repos\interfaces;

interface queued_repo_interface {
	
	public static function find_or_null($message_id);
	public static function find_for_user_or_null($message_id = 0, $user_id = 0);
	public static function find_for_user_course_or_null($message_id = 0, $user_id = 0, $course_id = 0);
	public static function get_for_user($user_id, $course_id = 0, $params = []);
	public static function get_all_messages_to_send();

}