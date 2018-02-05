<?php

require_once('../../config.php');
require_once 'lib.php';

$page_url = '/blocks/quickmail/alternate.php';

$page_params = [
    'courseid' => required_param('courseid', PARAM_INT),
    'confirmid' => optional_param('confirmid', 0, PARAM_INT),
    'resendid' => optional_param('resendid', 0, PARAM_INT),
    'token' => optional_param('token', '', PARAM_TEXT),
];

$course = get_course($page_params['courseid']);

////////////////////////////////////////
/// AUTHENTICATION
////////////////////////////////////////

require_login();
$page_context = context_course::instance($course->id);
$PAGE->set_context($page_context);
$PAGE->set_url(new moodle_url($page_url, $page_params));
block_quickmail_plugin::require_user_capability('allowalternate', $page_context);

////////////////////////////////////////
/// CONSTRUCT PAGE
////////////////////////////////////////

$PAGE->set_pagetype('block-quickmail');
$PAGE->set_pagelayout('standard');
$PAGE->set_title(block_quickmail_plugin::_s('pluginname') . ': ' . block_quickmail_plugin::_s('alternate'));
$PAGE->navbar->add(block_quickmail_plugin::_s('pluginname'));
$PAGE->navbar->add(block_quickmail_plugin::_s('alternate'));
$PAGE->set_heading(block_quickmail_plugin::_s('pluginname') . ': ' . block_quickmail_plugin::_s('alternate'));
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/blocks/quickmail/style.css'));
$PAGE->requires->js_call_amd('block_quickmail/alternate-index', 'init');

$renderer = $PAGE->get_renderer('block_quickmail');

////////////////////////////////////////
/// INSTANTIATE FORM
////////////////////////////////////////

$manage_alternates_form = \block_quickmail\forms\manage_alternates_form::make(
    $page_context, 
    $USER, 
    $course
);

////////////////////////////////////////
/// HANDLE REQUEST
////////////////////////////////////////

$request = block_quickmail_request::for_route('alternate')->with_form($manage_alternates_form);

// if a POST was submitted, attempt to take appropriate actions
try {
    // CONFIRM
    if ($request->to_confirm_alternate()) {
        
        dd('confirm');

        try {
            // attempt to confirm the alternate email using the page parameters
            $alternate_email = block_quickmail\persistents\alternate_email::confirm($USER, $page_params);

            // redirect and notify of success
            $request->redirect_as_type('success', block_quickmail_plugin::_s('entry_activated', $alternate_email->get('email')), $page_url, ['courseid' => $course->id], 2);
        } catch (\Exception $e) {
            // redirect and notify of error
            $request->redirect_as_type('error', $e->getMessage(), $page_url, ['courseid' => $course->id], 2);
        }

    // RESEND
    } else if ($request->to_resend_alternate()) {

        dd('resend');

        // attempt to fetch the alternate
        if ( ! $alternate_email = block_quickmail\persistents\alternate_email::find_or_null($request->resend_confirm_id)) {
            // redirect and notify of error
            $request->redirect_as_type('error', block_quickmail_plugin::_s('alternate_no_record'), $page_url, ['courseid' => $course->id], 2);
        }

        // make sure the requestor is this alternate's setup user
        if ($USER->id !== $alternate_email->get('setup_user_id')) {
            // redirect and notify of error
            $request->redirect_as_type('error', block_quickmail_plugin::_s('alternate_no_record'), $page_url, ['courseid' => $course->id], 2);
        }

        // attempt to resend confirmation email
        $alternate_email->send_confirmation_email($course->id);

        // redirect and notify of success
        $request->redirect_as_type('success', block_quickmail_plugin::_s('alternate_confirmation_email_resent'), $page_url, ['courseid' => $course->id], 2);

    // DELETE
    } else if ($request->to_delete_alternate()) {

        dd('delete');

        // attempt to fetch the alternate
        if ( ! $alternate_email = block_quickmail\persistents\alternate_email::find_or_null($request->delete_alternate_id)) {
            // redirect and notify of error
            $request->redirect_as_type('error', block_quickmail_plugin::_s('alternate_no_record'), $page_url, ['courseid' => $course->id], 2);
        }

        // attempt to soft delete alternate
        $alternate_email->soft_delete();

        // redirect and notify of success
        $request->redirect_as_type('success', block_quickmail_plugin::_s('alternate_deleted'), $page_url, ['courseid' => $course->id], 2);

    // CREATE
    } else if ($request->to_create_alternate()) {
        
        dd('create');
        
        // create the new alternate email
        $alternate_email = new block_quickmail\persistents\alternate_email(0, $request->get_create_request_data_object());
        $alternate_email->create();

        // refresh the persistent just in case
        $alternate_email->read();

        // generate a random token, and send confirmation email to user
        $alternate_email->send_confirmation_email($page_params['courseid']);

        // redirect and notify of success
        $request->redirect_as_type('success', block_quickmail_plugin::_s('alternate_created'), $page_url, ['courseid' => $course->id], 2);
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
    'course' => $course,
]);

// get the rendered form
$rendered_manage_alternates_form = $renderer->manage_alternates_component([
    'context' => $page_context,
    'user' => $USER,
    'course' => $course,
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

function dd($thing) {
    var_dump($thing);die;
}