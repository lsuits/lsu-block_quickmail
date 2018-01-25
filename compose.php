<?php

require_once('../../config.php');
require_once 'lib.php';

$page_url = '/blocks/quickmail/compose.php';

$page_params = [
    'courseid' => required_param('courseid', PARAM_INT),
    'draftid' => optional_param('draftid', 0, PARAM_INT),
];

$course = get_course($page_params['courseid']);

////////////////////////////////////////
/// AUTHENTICATION
////////////////////////////////////////

$page_context = context_course::instance($course->id);
$PAGE->set_context($page_context);
$PAGE->set_url(new moodle_url($page_url, $page_params));

require_course_login($course, false);
require_capability('block/quickmail:cansend', $page_context);

////////////////////////////////////////
/// CONSTRUCT PAGE
////////////////////////////////////////

$PAGE->set_pagetype('block-quickmail');
$PAGE->set_pagelayout('standard');
$PAGE->set_title(block_quickmail_plugin::_s('pluginname') . ': ' . block_quickmail_plugin::_s('compose'));
$PAGE->navbar->add(block_quickmail_plugin::_s('pluginname'));
$PAGE->navbar->add(block_quickmail_plugin::_s('compose'));
$PAGE->set_heading(block_quickmail_plugin::_s('pluginname') . ': ' . block_quickmail_plugin::_s('compose'));
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/blocks/quickmail/style.css'));
// $PAGE->requires->js_call_amd('block_quickmail/compose-message', 'init', ['courseid' => $course->id]);
// $PAGE->requires->js('/blocks/quickmail/js/selection.js'); // Get rid of this

$renderer = $PAGE->get_renderer('block_quickmail');

// if a draft id was passed
if ($page_params['draftid']) {
    
    // attempt to fetch the draft which must belong to this course and user
    $draft_message = $draft_message = block_quickmail\persistents\message::find_user_course_draft_or_null($page_params['draftid'], $USER->id, $course->id);

    if (empty($draft_message)) {
        $page_params['draftid'] = 0;
    } else {
        // make sure this draft message has not already been sent
        if ($draft_message->is_sent_message()) {
            // reset the passed param to 0
            // @TODO - notify user that message was already sent??
            $draft_message = null;
            $page_params['draftid'] = 0;
        }
    }

} else {
    $draft_message = null;
}

////////////////////////////////////////
/// FILE ATTACHMENT HANDLING
////////////////////////////////////////

// get the attachments draft area id
$attachments_draftitem_id = file_get_submitted_draft_itemid('attachments');

// prepare the draft area with any existing, relevant files
file_prepare_draft_area($attachments_draftitem_id, $page_context->id, 'block_quickmail', 'attachments', $page_params['draftid'] ?: null, block_quickmail_config::get_filemanager_options());

////////////////////////////////////////
/// INSTANTIATE FORM
////////////////////////////////////////

$compose_form = block_quickmail_form::make_compose_message_form(
    $page_context, 
    $USER, 
    $course,
    $draft_message
);

////////////////////////////////////////
/// HANDLE REQUEST
////////////////////////////////////////

$request = block_quickmail_request::for_route('compose')->with_form($compose_form);

if ($request->is_form_cancellation()) {
    
    // redirect back to course page
    $request->redirect_to_url(
        new moodle_url('/course/view.php', ['id' => $course->id]),
        block_quickmail_plugin::_s('redirect_back_to_course_from_message_after_cancel', $course->fullname)
    );

} else if ($request->to_send_message()) {

    // attempt to send, handle exceptions
    try {
        \block_quickmail\messenger\messenger::compose($USER, $course, $compose_form->get_data(), $draft_message, false);  // <---------- remove the last parameter for production!!!!
        
        // \block_quickmail\messenger\messenger::send_by_compose_request($compose_message_request);

        // redirect back to course page
        // @TODO - after send redirect to history (?)
        $request->redirect_to_url(
            new moodle_url('/course/view.php', ['id' => $course->id]),
            block_quickmail_plugin::_s('redirect_back_to_course_from_message_after_send', $course->fullname)
        );
    } catch (\block_quickmail\exceptions\validation_exception $e) {
        render_validation_notifications($e);
    } catch (\block_quickmail\exceptions\critical_exception $e) {
        print_error('critical_error', 'block_quickmail');
    }

} else if ($request->to_save_draft()) {

    // attempt to save draft, handle exceptions
    try {
        $draft_message = \block_quickmail\drafter\drafter::save_by_compose_request($compose_message_request);
    } catch (\block_quickmail\drafter\exceptions\drafter_authentication_exception $e) {
        print_error('no_permission', 'block_quickmail');
    } catch (\block_quickmail\drafter\exceptions\drafter_validation_exception $e) {
        render_validation_notifications($e);
    } catch (\block_quickmail\drafter\exceptions\drafter_critical_exception $e) {
        print_error('critical_error', 'block_quickmail');
    }

    // redirect back to course page
    // @TODO - after send redirect to compose (?)
    $request->redirect_to_url(
        new moodle_url('/course/view.php', ['id' => $course->id]),
        block_quickmail_plugin::_s('redirect_back_to_course_from_message_after_save', $course->fullname)
    );

}

////////////////////////////////////////
/// RENDER PAGE
////////////////////////////////////////

$rendered_compose_form = $renderer->compose_message_component([
    'context' => $page_context,
    'user' => $USER,
    'course' => $COURSE,
    'compose_form' => $compose_form,
]);

echo $OUTPUT->header();
echo $rendered_compose_form;
echo $OUTPUT->footer();


/**
 * Instantiates a core moodle error notification with list-style error messages from the given exception
 * 
 * @param  \Exception $exception
 * @return void
 */
function render_validation_notifications($exception) {
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