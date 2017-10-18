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

$page_url = '/blocks/quickmail/alternate.php';

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

block_quickmail_plugin::check_user_permission('allowalternate', $page_context);
    
////////////////////////////////////////
/// CONSTRUCT PAGE
////////////////////////////////////////

$PAGE->set_context($page_context);
$PAGE->set_course($course);
$PAGE->set_pagetype('block-quickmail');
$PAGE->set_pagelayout('standard');
$PAGE->set_title(block_quickmail_plugin::_s('pluginname') . ': ' . block_quickmail_plugin::_s('alternate'));
$PAGE->set_url(new moodle_url($page_url, $page_params));
$PAGE->navbar->add(block_quickmail_plugin::_s('pluginname'));
$PAGE->navbar->add(block_quickmail_plugin::_s('alternate'));
$PAGE->set_heading(block_quickmail_plugin::_s('pluginname') . ': ' . block_quickmail_plugin::_s('alternate'));
$PAGE->requires->css(new moodle_url($CFG->wwwroot . "/blocks/quickmail/style.css"));
// $PAGE->requires->js_call_amd('block_quickmail/course-config', 'init', ['courseid' => $page_params['courseid']]);
$PAGE->requires->js_call_amd('block_quickmail/alternate-index', 'init');

////////////////////////////////////////
/// INSTANTIATE PAGE RENDERER
////////////////////////////////////////
$renderer = $PAGE->get_renderer('block_quickmail');

////////////////////////////////////////
/// INSTANTIATE DELETE ALTERNATE FORM
////////////////////////////////////////
$manage_alternates_form = block_quickmail_form::make_manage_alternates_form(
    $page_context, 
    $USER, 
    $course 
);

////////////////////////////////////////
/// HANDLE ALTERNATE FORM SUBMISSION (if any)
////////////////////////////////////////

// instantiate "alternate" request
$alternate_request = \block_quickmail\requests\alternate_request::make($manage_alternates_form);

if ($alternate_request->to_delete_alternate()) {
    // delete alternate...
    dd('delete!');
    // dd($alternate_request->delete_alternate_id);
} else if ($alternate_request->to_create_alternate()) {
    dd($alternate_request->get_create_request_data_object());
}

// get all alternate emails belonging to this user
$alternate_emails = block_quickmail\persistents\alternate_email::get_all_for_user($USER->id);

// get the rendered index
$rendered_alternate_index = $renderer->alternate_index_component([
    'alternate_emails' => $alternate_emails,
    'user' => $USER,
    'course' => $course,
]);

// get the rendered form
$rendered_manage_alternates_form = $renderer->manage_alternates_component([
    'context' => $page_context,
    'user' => $USER,
    'course' => $course,
    'manage_alternates_form' => $manage_alternates_form,
]);


echo $OUTPUT->header();

// display the alternate email index table
echo $rendered_alternate_index;

// render the hidden form
echo $rendered_manage_alternates_form;

echo $OUTPUT->footer();


function dd($thing) {
    var_dump($thing);die;
}