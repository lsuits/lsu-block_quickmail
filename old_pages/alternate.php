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
    'confirmid' => optional_param('confirmid', 0, PARAM_INT),
    'resendid' => optional_param('resendid', 0, PARAM_INT),
    'token' => optional_param('token', '', PARAM_TEXT),
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
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/blocks/quickmail/style.css'));
// $PAGE->requires->js_call_amd('block_quickmail/course-config', 'init', ['courseid' => $page_params['courseid']]);
$PAGE->requires->js_call_amd('block_quickmail/alternate-index', 'init');

////////////////////////////////////////
/// INSTANTIATE PAGE RENDERER
////////////////////////////////////////
$renderer = $PAGE->get_renderer('block_quickmail');

////////////////////////////////////////
/// INSTANTIATE FORM
////////////////////////////////////////
$manage_alternates_form = block_quickmail_form::make_manage_alternates_form(
    $page_context, 
    $USER, 
    $course 
);

////////////////////////////////////////
/// INSTANTIATE REQUEST
////////////////////////////////////////
$alternate_request = \block_quickmail\requests\alternate_request::make(
    $manage_alternates_form,
    $page_params
);

////////////////////////////////////////
/// HANDLE CONFIRM REQUEST
////////////////////////////////////////
if ($alternate_request->to_confirm_alternate()) {
    
    try {
        // attempt to confirm the alternate email using the page parameters
        $alternate_email = block_quickmail\persistents\alternate_email::confirm($USER, $page_params);

        // redirect and notify of success
        $alternate_request->redirect_as_type('success', block_quickmail_plugin::_s('entry_activated', $alternate_email->get('email')), $page_url, ['courseid' => $course->id], 2);
    } catch (\Exception $e) {
        // redirect and notify of error
        $alternate_request->redirect_as_type('error', $e->getMessage(), $page_url, ['courseid' => $course->id], 2);
    }

////////////////////////////////////////
/// HANDLE RESEND REQUEST
////////////////////////////////////////
} else if ($alternate_request->to_resend_alternate()) {

    // attempt to fetch the alternate
    if ( ! $alternate_email = block_quickmail\persistents\alternate_email::find_or_null($alternate_request->resend_confirm_id)) {
        // redirect and notify of error
        $alternate_request->redirect_as_type('error', block_quickmail_plugin::_s('alternate_no_record'), $page_url, ['courseid' => $course->id], 2);
    }

    // make sure the requestor is this alternate's setup user
    if ($USER->id !== $alternate_email->get('setup_user_id')) {
        // redirect and notify of error
        $alternate_request->redirect_as_type('error', block_quickmail_plugin::_s('alternate_no_record'), $page_url, ['courseid' => $course->id], 2);
    }

    // attempt to resend confirmation email
    $alternate_email->send_confirmation_email($course->id);

    // redirect and notify of success
    $alternate_request->redirect_as_type('success', block_quickmail_plugin::_s('alternate_confirmation_email_resent'), $page_url, ['courseid' => $course->id], 2);

////////////////////////////////////////
/// HANDLE DELETE REQUEST
////////////////////////////////////////
} else if ($alternate_request->to_delete_alternate()) {
    // attempt to fetch the alternate
    if ( ! $alternate_email = block_quickmail\persistents\alternate_email::find_or_null($alternate_request->delete_alternate_id)) {
        // redirect and notify of error
        $alternate_request->redirect_as_type('error', block_quickmail_plugin::_s('alternate_no_record'), $page_url, ['courseid' => $course->id], 2);
    }

    // attempt to soft delete alternate
    $alternate_email->soft_delete();

    // redirect and notify of success
    $alternate_request->redirect_as_type('success', block_quickmail_plugin::_s('alternate_deleted'), $page_url, ['courseid' => $course->id], 2);

////////////////////////////////////////
/// HANDLE CREATE REQUEST
////////////////////////////////////////
} else if ($alternate_request->to_create_alternate()) {
    // create the new alternate email
    $alternate_email = new block_quickmail\persistents\alternate_email(0, $alternate_request->get_create_request_data_object());
    $alternate_email->create();

    // refresh the persistent just in case
    $alternate_email->read();

    // generate a random token, and send confirmation email to user
    $alternate_email->send_confirmation_email($page_params['courseid']);

    // redirect and notify of success
    $alternate_request->redirect_as_type('success', block_quickmail_plugin::_s('alternate_created'), $page_url, ['courseid' => $course->id], 2);
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