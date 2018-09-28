<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    block_quickmail
 * @copyright  2008 onwards Louisiana State University
 * @copyright  2008 onwards Chad Mazilly, Robert Russo, Jason Peak, Dave Elliott, Adam Zapletal, Philip Cali
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once 'lib.php';

$page_params = [
    'courseid' => required_param('courseid', PARAM_INT),
    'sort' => optional_param('sort', 'sent', PARAM_TEXT), // (field name)  - name, type, nextrun, enabled
    'dir' => optional_param('dir', 'desc', PARAM_TEXT), // asc|desc
    'page' => optional_param('page', 1, PARAM_INT),
    'per_page' => 10, // adjust as necessary, maybe turn into real param?
    'action' => optional_param('action', '', PARAM_TEXT), // disable|enable
    'notificationid' => optional_param('notificationid', 0, PARAM_INT),
];

$course = get_course($page_params['courseid']);

////////////////////////////////////////
/// AUTHENTICATION
////////////////////////////////////////

require_login();
$course_context = context_course::instance($course->id);
$PAGE->set_context($course_context);
$PAGE->set_url(new moodle_url('/blocks/quickmail/notifications.php', $page_params));

// throw an exception if user does not have capability to create notifications
block_quickmail_plugin::require_user_can_create_notifications($USER, $course_context);

////////////////////////////////////////
/// CONSTRUCT PAGE
////////////////////////////////////////

$PAGE->set_pagetype('block-quickmail');
$PAGE->set_pagelayout('standard');
$PAGE->set_title(block_quickmail_string::get('pluginname') . ': ' . block_quickmail_string::get('notifications'));
$PAGE->set_heading(block_quickmail_string::get('pluginname') . ': ' . block_quickmail_string::get('notifications'));
$PAGE->navbar->add(block_quickmail_string::get('pluginname'), new moodle_url('/blocks/quickmail/qm.php', array('courseid' => $course->id)));
$PAGE->navbar->add(block_quickmail_string::get('notifications'));
$PAGE->navbar->add($course->shortname);
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/blocks/quickmail/style.css'));
$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url('/blocks/quickmail/js/notification-index.js'));

block_quickmail\controllers\notification_index_controller::handle($PAGE, [
    'context' => $course_context,
    'user' => $USER,
    'course' => $course,
    'page_params' => $page_params
], $page_params['action']);
