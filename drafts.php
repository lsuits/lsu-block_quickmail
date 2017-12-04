<?php

require_once('../../config.php');
// require_once('../../enrol/externallib.php');
// require_once('../../lib/weblib.php');

$page_url = '/blocks/quickmail/drafts.php';

$page_params = [
    'courseid' => optional_param('courseid', 0, PARAM_INT), // course id, if any, for filtering
    'duplicateid' => optional_param('duplicateid', 0, PARAM_INT),
];

require_login();

$page_context = block_quickmail_plugin::resolve_context('system');

////////////////////////////////////////
/// CONSTRUCT PAGE
////////////////////////////////////////

$PAGE->set_context($page_context);
$PAGE->set_pagetype('block-quickmail');
$PAGE->set_pagelayout('standard');
$PAGE->set_title(block_quickmail_plugin::_s('pluginname') . ': ' . block_quickmail_plugin::_s('drafts'));
$PAGE->set_url(new moodle_url($page_url, $page_params));
$PAGE->navbar->add(block_quickmail_plugin::_s('pluginname'));
$PAGE->navbar->add(block_quickmail_plugin::_s('drafts'));
$PAGE->set_heading(block_quickmail_plugin::_s('pluginname') . ': ' . block_quickmail_plugin::_s('drafts'));
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/blocks/quickmail/style.css'));
$PAGE->requires->js_call_amd('block_quickmail/draft-index', 'init');

// handle draft duplication, if necessary
if ($page_params['duplicateid']) {
    try {
        // attempt to duplicate the draft
        \block_quickmail\drafter\drafter::duplicate_draft_for_user_id($page_params['duplicateid'], $USER->id);

        // redirect back to this page
        redirect(new \moodle_url('/blocks/quickmail/drafts.php', ['courseid' => $page_params['courseid']]), 'Your draft has been successfully duplicated.');
    } catch (\block_quickmail\drafter\exceptions\drafter_authentication_exception $e) {
        print_error('no_permission', 'block_quickmail');
    } catch (\block_quickmail\drafter\exceptions\drafter_critical_exception $e) {
        print_error('critical_error', 'block_quickmail');
    }
}

////////////////////////////////////////
/// INSTANTIATE PAGE RENDERER
////////////////////////////////////////
$renderer = $PAGE->get_renderer('block_quickmail');

////////////////////////////////////////
/// INSTANTIATE MANAGE DRAFTS FORM
////////////////////////////////////////
$manage_drafts_form = block_quickmail_form::make_manage_drafts_form(
    $page_context, 
    $USER, 
    $page_params['courseid']
);

////////////////////////////////////////
/// INSTANTIATE REQUEST
////////////////////////////////////////
$draft_request = \block_quickmail\requests\draft_request::make(
    $manage_drafts_form
);

////////////////////////////////////////
/// HANDLE DELETE REQUEST
////////////////////////////////////////
if ($draft_request->to_delete_draft()) {
    // attempt to fetch the draft message
    if ( ! $draft_message = block_quickmail\persistents\message::find_user_draft_or_null($draft_request->delete_draft_id, $USER->id)) {
        // redirect and notify of error
        $draft_request->redirect_as_type('error', block_quickmail_plugin::_s('draft_no_record'), $page_url, ['courseid' => $draft_request->course_id], 2);
    }

    // attempt to soft delete draft
    $draft_message->soft_delete();
}

// get all (unsent) message drafts belonging to this user
$draft_messages = block_quickmail\persistents\message::get_all_unsent_drafts_for_user($USER->id);

// get the rendered index
$rendered_draft_message_index = $renderer->draft_message_index_component([
    'draft_messages' => $draft_messages,
    'user' => $USER,
    'course_id' => $page_params['courseid'],
]);

// get the rendered form
$rendered_manage_drafts_form = $renderer->manage_drafts_component([
    'manage_drafts_form' => $manage_drafts_form,
]);

echo $OUTPUT->header();

// display the draft message index table
echo $rendered_draft_message_index;

// render the hidden form
echo $rendered_manage_drafts_form;

echo $OUTPUT->footer();

////////////////////////////////////////////////////////////
function dd($thing) {
    var_dump($thing);die;
}