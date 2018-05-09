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

$page_url = '/blocks/quickmail/alternate.php';

$page_params = [
    'courseid' => optional_param('courseid', 0, PARAM_INT),
    'confirmid' => optional_param('confirmid', 0, PARAM_INT),
    'resendid' => optional_param('resendid', 0, PARAM_INT),
    'token' => optional_param('token', '', PARAM_TEXT),
];

////////////////////////////////////////
/// AUTHENTICATION
////////////////////////////////////////

require_login();

// if we're scoping to a specific course
if ($page_params['courseid']) {
    // if we're scoping to the site level course
    if ($page_params['courseid'] == SITEID) {
        // throw an exception if user does not have site-level capability for this block
        block_quickmail_plugin::require_user_has_course_message_access($USER, $page_params['courseid']);
    
    // otherwise, we're scoping to a course
    } else {
        // throw an exception if user does not have capability of having alternates
        block_quickmail_plugin::require_user_capability('allowalternate', $USER, context_course::instance($page_params['courseid']));
    }
}

$user_context = context_user::instance($USER->id);
$PAGE->set_context($user_context);
$PAGE->set_url(new moodle_url($page_url, $page_params));

////////////////////////////////////////
/// CONSTRUCT PAGE
////////////////////////////////////////

$PAGE->set_pagetype('block-quickmail');
$PAGE->set_pagelayout('standard');
$PAGE->set_title(block_quickmail_string::get('pluginname') . ': ' . block_quickmail_string::get('alternate'));
$PAGE->navbar->add(block_quickmail_string::get('pluginname'));
$PAGE->navbar->add(block_quickmail_string::get('alternate'));
$PAGE->set_heading(block_quickmail_string::get('pluginname') . ': ' . block_quickmail_string::get('alternate'));
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/blocks/quickmail/style.css'));
$PAGE->requires->js_call_amd('block_quickmail/alternate-index', 'init');

$renderer = $PAGE->get_renderer('block_quickmail');

////////////////////////////////////////
/// INSTANTIATE FORM
////////////////////////////////////////

$manage_alternates_form = \block_quickmail\forms\manage_alternates_form::make(
    $user_context, 
    $USER, 
    $page_params['courseid']
);

////////////////////////////////////////
/// HANDLE REQUEST
////////////////////////////////////////

$request = block_quickmail_request::for_route('alternate')->with_form($manage_alternates_form);

// if a POST was submitted, attempt to take appropriate actions
try {
    // CONFIRM
    if ($page_params['confirmid'] && $page_params['token']) {
        
        // attempt to confirm the alternate email for this user
        $alternate_email = \block_quickmail\services\alternate\alternate_manager::confirm_alternate_for_user(
            $page_params['confirmid'], 
            $page_params['token'], 
            $USER
        );

        $request->redirect_as_success(block_quickmail_string::get('alternate_activated', $alternate_email->get('email')), $page_url, ['courseid' => $page_params['courseid']]);

    // RESEND
    } else if ($page_params['resendid']) {

        // attempt to resend the confirmation email
        \block_quickmail\services\alternate\alternate_manager::resend_confirmation_email_for_user($page_params['resendid'], $USER);

        // redirect and notify of success
        $request->redirect_as_success(block_quickmail_string::get('alternate_confirmation_email_resent'), $page_url, ['courseid' => $page_params['courseid']]);

    // DELETE
    } else if ($request->to_delete_alternate()) {

        // attempt to delete the alternate email
        \block_quickmail\services\alternate\alternate_manager::delete_alternate_email_for_user($request->data->delete_alternate_id, $USER);

        // redirect and notify of success
        $request->redirect_as_success(block_quickmail_string::get('alternate_deleted'), $page_url, ['courseid' => $page_params['courseid']]);

    // CREATE
    } else if ($request->to_create_alternate()) {
        
        // attempt to create the alternate and send a confirmation email
        \block_quickmail\services\alternate\alternate_manager::create_alternate_for_user($USER, $page_params['courseid'], [
            'availability' => $request->data->availability,
            'firstname' => $request->data->firstname,
            'lastname' => $request->data->lastname,
            'email' => $request->data->email,
        ]);

        // redirect and notify of success
        $request->redirect_as_success(block_quickmail_string::get('alternate_created'), $page_url, ['courseid' => $page_params['courseid']]);
    }
} catch (\block_quickmail\exceptions\validation_exception $e) {
    $manage_alternates_form->set_error_exception($e);
} catch (\block_quickmail\exceptions\critical_exception $e) {
    print_error('critical_error', 'block_quickmail');
}

// get all alternate emails belonging to this user
$alternate_emails = block_quickmail\persistents\alternate_email::get_all_for_user($USER->id);

// get the rendered index
$rendered_alternate_index = $renderer->alternate_index_component([
    'alternate_emails' => $alternate_emails,
    'user' => $USER,
    'course_id' => $page_params['courseid'],
]);

// get the rendered form
$rendered_manage_alternates_form = $renderer->manage_alternates_component([
    'context' => $user_context,
    'user' => $USER,
    'course_id' => $page_params['courseid'],
    'manage_alternates_form' => $manage_alternates_form,
]);


////////////////////////////////////////
/// RENDER PAGE
////////////////////////////////////////

echo $OUTPUT->header();
$manage_alternates_form->render_error_notification();
echo $rendered_alternate_index;
echo $rendered_manage_alternates_form;
echo $OUTPUT->footer();