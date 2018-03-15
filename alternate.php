<?php

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
$page_context = context_user::instance($USER->id);
$PAGE->set_context($page_context);
$PAGE->set_url(new moodle_url($page_url, $page_params));
block_quickmail_plugin::require_user_capability('allowalternate', $page_context);

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
    $page_context, 
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

        $request->redirect_as_success(block_quickmail_string::get('entry_activated', $alternate_email->get('email')), $page_url, ['courseid' => $page_params['courseid']]);

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
    'context' => $page_context,
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