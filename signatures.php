<?php

require_once('../../config.php');
require_once 'lib.php';

$page_url = '/blocks/quickmail/signatures.php';

$page_params = [
    'id' => optional_param('id', 0, PARAM_INT), // signature id, if any
    'courseid' => optional_param('courseid', 0, PARAM_INT), // course id, if any, for redirection
];

////////////////////////////////////////
/// AUTHENTICATION
////////////////////////////////////////

require_login();
$page_context = context_system::instance();
$PAGE->set_context($page_context);
$PAGE->set_url(new moodle_url($page_url, $page_params));
block_quickmail_plugin::require_user_capability('cansend', $page_context);

////////////////////////////////////////
/// CONSTRUCT PAGE
////////////////////////////////////////

$PAGE->set_pagetype('block-quickmail');
$PAGE->set_pagelayout('standard');
$PAGE->set_title(block_quickmail_plugin::_s('pluginname') . ': ' . block_quickmail_plugin::_s('manage_signatures'));
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

$manage_signatures_form = \block_quickmail\forms\manage_signatures_form::make(
    $page_context, 
    $USER, 
    $signature, 
    $page_params['courseid']
);

////////////////////////////////////////
/// HANDLE REQUEST
////////////////////////////////////////

$request = block_quickmail_request::for_route('signature')->with_form($manage_signatures_form);

// if a POST was submitted, attempt to take appropriate actions
try {
    if ($request->is_form_cancellation()) {
        
        dd('cancel');

        // if no course id was provided, redirect back to "my page"
        // if (empty($page_params['courseid'])) {
        //     $this->redirect_as_type('info', 'Form cancelled!!', '/my', [], 2);

        // // otherwise, redirect back to course page
        // } else {
        //     $this->redirect_as_type('info', 'Form cancelled!!', '/course/view.php', ['id' => $page_params['courseid']], 2);
        // }

    } else if ($request->to_delete_signature()) {

        dd('delete!');

    } else if ($request->to_save_signature()) {
        // if no id (signature) was submitted, create a new signature
        if ( ! $page_params['id']) {
            // create a new signature
            $signature = block_quickmail\persistents\signature::create_new([
                'user_id' => $USER->id,
                'title' => $request->data->title,
                'signature' => $request->data->signature,
                'default_flag' => $request->data->default_flag,
            ]);
        // otherwise, update the signature
        } else {
            // update the current signature
            $signature->from_record($request->data);
            $signature->update();
        }
    }
} catch (\block_quickmail\exceptions\validation_exception $e) {
    render_validation_notifications($e);
} catch (\block_quickmail\exceptions\critical_exception $e) {
    print_error('critical_error', 'block_quickmail');
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
// function handle_post_signature_save_or_update($context, $signature, $signature_request) {
//     $record = $signature->to_record();
//     $record->signatureformat = (int) $signature_request->form->user->mailformat;
//     $record->signature_editor = $signature_request->form_data->signature_editor;

//     file_postupdate_standard_editor(
//         $record,
//         'signature', 
//         \block_quickmail_config::get_editor_options($context),
//         $context, 
//         \block_quickmail_plugin::$name, 
//         'signature_editor',
//         $signature->get('id')
//     );
// }

function dd($thing)
{
    var_dump($thing);die;
}