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
    // CANCEL
    if ($request->is_form_cancellation()) {
        
        // if no course id was provided, redirect back to "my page"
        if (empty($page_params['courseid'])) {
            $request->redirect_as_info('Form cancelled!!', '/my');
        // otherwise, redirect back to course page
        } else {
            $request->redirect_as_info('Form cancelled!!', '/course/view.php', ['id' => $page_params['courseid']]);
        }

    // DELETE
    } else if ($request->to_delete_signature()) {

        // soft delete the signature, flagging a new default if necessary
        $signature->soft_delete();

        // redirect back to the user's edit default signature (if any) page, preserving the courseid parameter
        $request->redirect_to_user_default_signature('warning', $USER, $page_params['courseid'], \block_quickmail_plugin::_s('user_signature_deleted'));

    // SAVE / UPDATE
    } else if ($request->to_save_signature()) {
        
        // if we're not focused on an existing signature
        if ( ! $page_params['id']) {
            // create a new one
            $signature = block_quickmail\persistents\signature::create_new([
                'user_id' => $USER->id,
                'title' => $request->data->title,
                'signature' => $request->data->signature,
                'default_flag' => $request->data->default_flag,
            ]);
        
        } else {
            
            // update the current signature
            $signature->from_record($request->data);
            $signature->update();
        }

        // handle the text editor persistence stuff...
        block_quickmail\persistents\signature::handle_post_save_or_update($page_context, $signature, $request);

        // redirect back to signature index
        $request->redirect_as_type(
            'success', 
            get_string('changessaved'), 
            '/blocks/quickmail/signatures.php', 
            ['id' => $signature->get('id'), 'courseid' => $page_params['courseid']]
        );
    }
} catch (\core\invalid_persistent_exception $e) {
    $manage_signatures_form->set_error_exception($e);
} catch (\block_quickmail\exceptions\validation_exception $e) {
    $manage_signatures_form->set_error_exception($e);
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

////////////////////////////////////////
/// RENDER PAGE
////////////////////////////////////////

echo $OUTPUT->header();
$manage_signatures_form->render_error_notification();
echo $rendered_signature_form;
echo $OUTPUT->footer();
