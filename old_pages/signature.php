<?php

require_once('../../config.php');

$page_url = '/blocks/quickmail/signatures.php';

$page_params = [
    'id' => optional_param('id', 0, PARAM_INT), // signature id, if any
    'courseid' => optional_param('courseid', 0, PARAM_INT), // course id, if any, for redirection
];

require_login();

$page_context = block_quickmail_plugin::resolve_context('system');

////////////////////////////////////////
/// CONSTRUCT PAGE
////////////////////////////////////////

$PAGE->set_context($page_context);
$PAGE->set_pagetype('block-quickmail');
$PAGE->set_pagelayout('standard');
$PAGE->set_title(block_quickmail_plugin::_s('pluginname') . ': ' . block_quickmail_plugin::_s('manage_signatures'));
$PAGE->set_url(new moodle_url($page_url, $page_params));
$PAGE->navbar->add(block_quickmail_plugin::_s('pluginname'));
$PAGE->navbar->add(block_quickmail_plugin::_s('signatures'));
$PAGE->set_heading(block_quickmail_plugin::_s('pluginname') . ': ' . block_quickmail_plugin::_s('manage_signatures'));
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/blocks/quickmail/style.css'));
$PAGE->requires->js_call_amd('block_quickmail/manage-signatures', 'init', ['courseid' => $page_params['courseid']]);

// find the requested signature, if any, which must belong to the auth user
if ( ! $signature = block_quickmail\persistents\signature::find_user_signature_or_null($page_params['id'], $USER->id)) {
    // if signature could not be found for user, reset the given signature param for the page
    $page_params['id'] = 0;
}

////////////////////////////////////////
/// INSTANTIATE PAGE RENDERER
////////////////////////////////////////
$renderer = $PAGE->get_renderer('block_quickmail');

////////////////////////////////////////
/// INSTANTIATE FORM
////////////////////////////////////////
$manage_signatures_form = block_quickmail_form::make_manage_signatures_form(
    $page_context, 
    $USER, 
    $signature, 
    $page_params['courseid']
);

////////////////////////////////////////
/// INSTANTIATE REQUEST
////////////////////////////////////////
$signature_request = \block_quickmail\requests\signature_request::make($manage_signatures_form);

////////////////////////////////////////
/// HANDLE CANCEL REQUEST
////////////////////////////////////////
if ($signature_request->was_cancelled()) {
    
    // redirect back to appropriate page
    $signature_request->redirect_back();

////////////////////////////////////////
/// HANDLE DELETE REQUEST
////////////////////////////////////////
} else if ($signature_request->to_delete_signature()) {

    // soft delete the signature, flagging a new default if necessary
    $signature->soft_delete();

    // redirect back to the user's edit default signature (if any) page
    $signature_request->redirect_to_edit_users_default_signature('warning', $USER, \block_quickmail_plugin::_s('user_signature_deleted'));

////////////////////////////////////////
/// HANDLE SAVE REQUEST
////////////////////////////////////////
} else if ($signature_request->to_save_signature()) {

    try {
        // if no id (signature) was submitted, create a new signature
        if ( ! $page_params['id']) {
            // create a new signature
            $signature = new block_quickmail\persistents\signature(0, $signature_request->get_request_data_object());
            $signature->create();

        // otherwise, update the signature
        } else {
            // update the current signature
            $signature->from_record($signature_request->get_request_data_object());
            $signature->update();
        }
    } catch (\core\invalid_persistent_exception $e) {
        // if validation error, redirect back to signature attempting to be modified, or "create new" if none
        $signature_request->redirect_to_edit_signature_id('error', $page_params['id'], $e->a);
    } 

    // handle the text editor persistence stuff...
    handle_post_signature_save_or_update($page_context, $signature, $signature_request);

    // redirect to this signature edit page, notifying user of update
    $signature_request->redirect_to_edit_signature_id('success', $signature->get('id'), get_string('changessaved'));
}

// get the rendered form
$rendered_signature_form = $renderer->manage_signatures_component([
    'context' => $page_context,
    'signature' => $signature,
    'user' => $USER,
    'manage_signatures_form' => $manage_signatures_form,
]);

echo $OUTPUT->header();

// display the manage signature form
echo $rendered_signature_form;

echo $OUTPUT->footer();

/**
 * Handles the persistence and display of text editor content after updating a signature
 * 
 * @param  object             $context
 * @param  signature          $signature
 * @param  signature_request  $signature_request
 * @return void
 */
function handle_post_signature_save_or_update($context, $signature, $signature_request) {
    $record = $signature->to_record();
    $record->signatureformat = (int) $signature_request->form->user->mailformat;
    $record->signature_editor = $signature_request->form_data->signature_editor;

    file_postupdate_standard_editor(
        $record,
        'signature', 
        \block_quickmail_config::get_editor_options($context),
        $context, 
        \block_quickmail_plugin::$name, 
        'signature_editor',
        $signature->get('id')
    );
}