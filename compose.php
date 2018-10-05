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

$page_params = [
    'courseid' => required_param('courseid', PARAM_INT),
    'draftid' => optional_param('draftid', 0, PARAM_INT),
];

$course = get_course($page_params['courseid']);

////////////////////////////////////////
/// AUTHENTICATION
////////////////////////////////////////

require_course_login($course, false);
$course_context = context_course::instance($course->id);
$PAGE->set_context($course_context);
$PAGE->set_url(new moodle_url('/blocks/quickmail/compose.php', $page_params));
$PAGE->requires->js_call_amd('block_quickmail/compose', 'init');

// throw an exception if user does not have capability to compose messages
block_quickmail_plugin::require_user_can_send('compose', $USER, $course_context);

////////////////////////////////////////
/// GET COURSE USER/ROLE/GROUP DATA FOR SELECTION
////////////////////////////////////////

$course_user_data = block_quickmail_plugin::get_compose_message_recipients(
    $course, 
    $USER,
    block_quickmail_plugin::user_prefers_multiselect_recips($USER),
    $course_context
);

////////////////////////////////////////
/// CONSTRUCT PAGE
////////////////////////////////////////

$PAGE->set_pagetype('block-quickmail');
$PAGE->set_pagelayout('standard');
$PAGE->set_title(block_quickmail_string::get('pluginname') . ': ' . block_quickmail_string::get('compose'));
$PAGE->navbar->add(block_quickmail_string::get('pluginname'), new moodle_url('/blocks/quickmail/qm.php', array('courseid' => $course->id)));
$PAGE->navbar->add(block_quickmail_string::get('compose'));
$PAGE->set_heading(block_quickmail_string::get('pluginname') . ': ' . block_quickmail_string::get('compose'));
$PAGE->requires->css(new moodle_url('/blocks/quickmail/style.css'));

$renderer = $PAGE->get_renderer('block_quickmail');

// if a draft id was passed
if ($page_params['draftid']) {
    // attempt to fetch the draft which must belong to this course and user
    $draft_message = block_quickmail\repos\draft_repo::find_for_user_course_or_null($page_params['draftid'], $USER->id, $course->id);

    // if no valid draft message was found, reset param
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
file_prepare_draft_area(
    $attachments_draftitem_id, 
    $course_context->id, 
    'block_quickmail', 
    'attachments', 
    $page_params['draftid'] ?: null, 
    block_quickmail_config::get_filemanager_options()
);

////////////////////////////////////////
/// INSTANTIATE FORM
////////////////////////////////////////

$compose_form = \block_quickmail\forms\compose_message_form::make(
    $course_context, 
    $USER, 
    $course,
    $course_user_data,
    $draft_message
);

////////////////////////////////////////
/// HANDLE REQUEST
////////////////////////////////////////

$request = block_quickmail_request::for_route('compose')->with_form($compose_form);

// if a POST was submitted, attempt to take appropriate actions
try {
    // CANCEL
    if ($request->is_form_cancellation()) {
        
        // redirect back to course page
        $request->redirect_to_url('/course/view.php', ['id' => $course->id]);

    // SEND
    } else if ($request->to_send_message()) {

        $send_as_task = \block_quickmail_config::block('send_as_tasks');

        $message = \block_quickmail\messenger\messenger::compose($USER, $course, $compose_form->get_data(), $draft_message, $send_as_task);

        // resolve redirect message
        if ($message->is_sent_message()) {
            $redirect_message = 'message_sent_now';
        } else if ($message->is_queued_message()) {
            $redirect_message = 'message_queued';
        } else {
            $redirect_message = 'message_sent_asap';
        }

        // redirect back to course page with message
        $request->redirect_as_success(block_quickmail_string::get($redirect_message, $course->fullname), '/course/view.php', ['id' => $course->id]);

    // SAVE DRAFT
    } else if ($request->to_save_draft()) {

        // attempt to save draft, handle exceptions
        $message = \block_quickmail\messenger\messenger::save_compose_draft($USER, $course, $compose_form->get_data(), $draft_message);

        // redirect back to course page
        $request->redirect_as_info(block_quickmail_string::get('redirect_back_to_course_from_message_after_save', $course->fullname), '/course/view.php', ['id' => $course->id]);
    }
} catch (\block_quickmail\exceptions\validation_exception $e) {
    $compose_form->set_error_exception($e);
} catch (\block_quickmail\exceptions\critical_exception $e) {
    print_error('critical_error', 'block_quickmail');
}

////////////////////////////////////////
/// RENDER PAGE
////////////////////////////////////////

$rendered_compose_form = $renderer->compose_message_component([
    'context' => $course_context,
    'user' => $USER,
    'course' => $course,
    'compose_form' => $compose_form,
]);

echo $OUTPUT->header();
$compose_form->render_error_notification();
echo $rendered_compose_form;
echo $OUTPUT->footer();
