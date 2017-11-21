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
 * @copyright  2008-2017 Louisiana State University
 * @copyright  2008-2017 Adam Zapletal, Chad Mazilly, Philip Cali, Robert Russo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$page_url = '/blocks/quickmail/configuration.php';

$page_params = [
    'courseid' => required_param('courseid', PARAM_INT),
];

require_login();

try {
    // get page context and course
    list($page_context, $course) = block_quickmail_plugin::resolve_context('course', $page_params['courseid']);
} catch (Exception $e) {
    print_error('no_course', 'block_quickmail', '', $page_params['courseid']);
}

block_quickmail_plugin::check_user_permission('canconfig', $page_context);
    
////////////////////////////////////////
/// CONSTRUCT PAGE
////////////////////////////////////////

$PAGE->set_context($page_context);
$PAGE->set_course($course);
$PAGE->set_pagetype('block-quickmail');
$PAGE->set_pagelayout('standard');
$PAGE->set_title(block_quickmail_plugin::_s('pluginname') . ': ' . block_quickmail_plugin::_s('config'));
$PAGE->set_url(new moodle_url($page_url, $page_params));
$PAGE->navbar->add(block_quickmail_plugin::_s('pluginname'));
$PAGE->navbar->add(block_quickmail_plugin::_s('config'));
$PAGE->set_heading(block_quickmail_plugin::_s('pluginname') . ': ' . block_quickmail_plugin::_s('config'));
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/blocks/quickmail/style.css'));
$PAGE->requires->js_call_amd('block_quickmail/course-config', 'init', ['courseid' => $page_params['courseid']]);

////////////////////////////////////////
/// INSTANTIATE PAGE RENDERER
////////////////////////////////////////
$renderer = $PAGE->get_renderer('block_quickmail');

////////////////////////////////////////
/// INSTANTIATE CONFIGURATION FORM
////////////////////////////////////////
$course_config_form = block_quickmail_form::make_course_config_form(
    $page_context, 
    $USER, 
    $course
);

////////////////////////////////////////
/// HANDLE SIGNATURE FORM SUBMISSION (if any)
////////////////////////////////////////

// instantiate "course configuration" request
$course_config_request = \block_quickmail\requests\course_config_request::make($course_config_form);

// if cancelling form
if ($course_config_request->was_cancelled()) {
    
    // redirect back to appropriate page
    $course_config_request->redirect_back();

// if requesting to restore defaults
} else if ($course_config_request->to_restore_defaults()) {
    
    // delete this course's config settings
    block_quickmail_plugin::delete_course_config($course->id);

    // redirect to this signature edit page, notifying user of update
    $course_config_request->redirect_to_course_config_page('success', $course->id, get_string('changessaved'));

// if saving signature
} else if ($course_config_request->to_save_configuration()) {

    // replace this course's config settings with those that were submitted
    block_quickmail_plugin::update_course_config($course->id, $course_config_request->get_request_data_object());

    // redirect to this signature edit page, notifying user of update
    $course_config_request->redirect_to_course_config_page('success', $course->id, get_string('changessaved'));
}

// get the rendered form
$rendered_config_form = $renderer->course_config_component([
    'context' => $page_context,
    'course' => $course,
    'user' => $USER,
    'course_config_form' => $course_config_form,
]);

echo $OUTPUT->header();

// display the manage signature form
echo $rendered_config_form;

echo $OUTPUT->footer();