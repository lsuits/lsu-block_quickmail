<?php

require_once('../../config.php');
// require_once('../../enrol/externallib.php');
// require_once('../../lib/weblib.php');

$page_url = '/blocks/quickmail/compose.php';

$page_params = [
    'courseid' => required_param('courseid', PARAM_INT),
    'draftid' => optional_param('draftid', 0, PARAM_INT),
];

require_login($page_params['courseid']);

try {
    // get page context and course
    list($page_context, $course) = block_quickmail_plugin::resolve_context('course', $page_params['courseid']);
} catch (Exception $e) {
    print_error('no_course', 'block_quickmail', '', $page_params['courseid']);
}

block_quickmail_plugin::check_user_permission('cansend', $page_context);

////////////////////////////////////////
/// CONSTRUCT PAGE
////////////////////////////////////////

$PAGE->set_context($page_context);
$PAGE->set_pagetype('block-quickmail');
$PAGE->set_pagelayout('standard');
$PAGE->set_title(block_quickmail_plugin::_s('pluginname') . ': ' . block_quickmail_plugin::_s('compose'));
$PAGE->set_url(new moodle_url($page_url, $page_params));
$PAGE->navbar->add(block_quickmail_plugin::_s('pluginname'));
$PAGE->navbar->add(block_quickmail_plugin::_s('compose'));
$PAGE->set_heading(block_quickmail_plugin::_s('pluginname') . ': ' . block_quickmail_plugin::_s('compose'));
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/blocks/quickmail/style.css'));
// $PAGE->requires->js_call_amd('block_quickmail/compose-message', 'init', ['courseid' => $page_params['courseid']]);
// $PAGE->requires->js('/blocks/quickmail/js/selection.js'); // Get rid of this

// if a draft id was passed
if ($page_params['draftid']) {
    // attempt to fetch the draft which must belong to this course and user
    if ( ! $draft_message = block_quickmail\persistents\message::find_user_course_draft_or_null($page_params['draftid'], $USER->id, $page_params['courseid'])) {
        // if draft message could not be found, reset the passed param to 0
        $page_params['draftid'] = 0;
    }
} else {
    $draft_message = null;
}

// NEED THESE TO BUILD THE SELECTION FORM?!?

// 'selected' => $selected,
// 'users' => $users,
// 'roles' => $roles,
// 'groups' => $groups,
// 'users_to_roles' => $users_to_roles,
// 'users_to_groups' => $users_to_groups,

////////////////////////////////////////
/// INSTANTIATE PAGE RENDERER
////////////////////////////////////////
$renderer = $PAGE->get_renderer('block_quickmail');

////////////////////////////////////////
/// INSTANTIATE COMPOSE FORM
////////////////////////////////////////
$compose_form = block_quickmail_form::make_compose_message_form(
    $page_context, 
    $USER, 
    $course,
    $draft_message
);

////////////////////////////////////////
/// HANDLE COMPOSE FORM SUBMISSION (if any)
////////////////////////////////////////

// instantiate "compose message" request
$compose_message_request = \block_quickmail\requests\compose_message_request::make_compose_request($compose_form);

// if cancelling form
if ($compose_message_request->was_cancelled()) {
    
    // redirect back to course
    $compose_message_request->redirect_back_to_course_after_cancel();

// if sending message
} else if ($compose_message_request->to_send_message()) {

    // attempt to send, handle exceptions
    try {
        $messenger_response = \block_quickmail\messenger\messenger::send_by_compose_request($compose_message_request);
    } catch (\block_quickmail\messenger\exceptions\messenger_authentication_exception $e) {
        print_error('no_permission', 'block_quickmail');
    } catch (\block_quickmail\messenger\exceptions\messenger_validation_exception $e) {
        render_validation_nofitications($e);
    } catch (\block_quickmail\messenger\exceptions\messenger_critical_exception $e) {
        print_error('critical_error', 'block_quickmail');
    }

    // $messenger_response
    $compose_message_request->redirect_back_to_course_after_send();
    
    // @TODO - after send redirect to history

// if saving draft
} else if ($compose_message_request->to_save_draft()) {
    // attempt to save draft, handle exceptions
    try {
        $draft_message = \block_quickmail\drafter\drafter::save_by_compose_request($compose_message_request);
    } catch (\block_quickmail\drafter\exceptions\drafter_authentication_exception $e) {
        print_error('no_permission', 'block_quickmail');
    } catch (\block_quickmail\drafter\exceptions\drafter_validation_exception $e) {
        render_validation_nofitications($e);
    } catch (\block_quickmail\drafter\exceptions\drafter_critical_exception $e) {
        print_error('critical_error', 'block_quickmail');
    }

    // $draft_message
    $compose_message_request->redirect_back_to_course_after_save();

    // @TODO - after send redirect to compose
}

// get the rendered form
$rendered_compose_form = $renderer->compose_message_component([
    'context' => $page_context,
    'user' => $USER,
    'course' => $COURSE,
    'compose_form' => $compose_form,
]);

echo $OUTPUT->header();

// display the compose form
echo $rendered_compose_form;

echo $OUTPUT->footer();

/**
 * Instantiates a core moodle error notification with list-style error messages from the given exception
 * 
 * @param  \Exception $exception
 * @return void
 */
function render_validation_nofitications($exception) {
    if (count($exception->errors)) {
        $html = '<ul>';
        
        foreach ($exception->errors as $error) {
            $html .= '<li>' . $error . '</li>';
        }

        $html .= '</ul>';
        
        \core\notification::error($html);
    }
}

////////////////////////////////////////////////////////////
function dd($thing) {
    var_dump($thing);die;
}