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
    'courseid' => SITEID,
    'draftid' => optional_param('draftid', 0, PARAM_INT),
    'page' => optional_param('page', 0, PARAM_INT),
    'per_page' => optional_param('per_page', 20, PARAM_INT),
    'sort_by' => optional_param('sort_by', 'lastname', PARAM_ALPHA),
    'sort_dir' => optional_param('sort_dir', 'asc', PARAM_ALPHA)
];

////////////////////////////////////////
/// AUTHENTICATION
////////////////////////////////////////

require_login();
$system_context = context_system::instance();
$PAGE->set_context($system_context);
$PAGE->set_url(new moodle_url('/blocks/quickmail/broadcast.php', $page_params));

// throw an exception if user does not have capability to broadcast messages
block_quickmail_plugin::require_user_can_send('broadcast', $USER, $system_context);

// get (site) course
$course = get_course($page_params['courseid']);

////////////////////////////////////////
/// CONSTRUCT PAGE
////////////////////////////////////////

$PAGE->set_pagetype('block-quickmail');
$PAGE->set_pagelayout('standard');
$PAGE->set_title(block_quickmail_string::get('pluginname') . ': ' . block_quickmail_string::get('broadcast'));
$PAGE->navbar->add(block_quickmail_string::get('pluginname'), new moodle_url('/blocks/quickmail/qm.php', array('courseid' => $course->id)));
$PAGE->navbar->add(block_quickmail_string::get('broadcast'));
$PAGE->set_heading(block_quickmail_string::get('pluginname') . ': ' . block_quickmail_string::get('broadcast'));
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
/// INSTANTIATE USER FILTER FOR RECIPIENT FILTERING
////////////////////////////////////////

$broadcast_recipient_filter = block_quickmail_broadcast_recipient_filter::make($page_params, $draft_message);

////////////////////////////////////////
/// FILE ATTACHMENT HANDLING
////////////////////////////////////////

// get the attachments draft area id
// $attachments_draftitem_id = file_get_submitted_draft_itemid('attachments');

// // prepare the draft area with any existing, relevant files
// file_prepare_draft_area(
//     $attachments_draftitem_id, 
//     $system_context->id, 
//     'block_quickmail', 
//     'attachments', 
//     $page_params['draftid'] ?: null, 
//     block_quickmail_config::get_filemanager_options()
// );

////////////////////////////////////////
/// INSTANTIATE FORM
////////////////////////////////////////

$broadcast_form = \block_quickmail\forms\broadcast_message_form::make(
    $system_context, 
    $USER, 
    $course,
    $draft_message
);

////////////////////////////////////////
/// HANDLE REQUEST
////////////////////////////////////////

$request = block_quickmail_request::for_route('broadcast')->with_form($broadcast_form);

// if a POST was submitted, attempt to take appropriate actions
try {
    // CANCEL
    if ($request->is_form_cancellation()) {
        
        // clear any recipient user filtering session data
        $broadcast_recipient_filter->clear_session();

        // redirect back to course page
        $request->redirect_to_url('/my');

    // SEND
    } else if ($request->to_send_message()) {

        $send_as_task = \block_quickmail_config::block('send_as_tasks');
        
        $message = \block_quickmail\messenger\messenger::broadcast($USER, $course, $broadcast_form->get_data(), $broadcast_recipient_filter, $draft_message, $send_as_task);
        
        // clear any recipient user filtering session data
        $broadcast_recipient_filter->clear_session();
        
        // resolve redirect message
        if ($message->is_sent_message()) {
            $redirect_message = 'message_sent_now';
        } else if ($message->is_queued_message()) {
            $redirect_message = 'message_queued';
        } else {
            $redirect_message = 'message_sent_asap';
        }

        $request->redirect_as_success(block_quickmail_string::get($redirect_message, $course->fullname), '/my');

    // SAVE DRAFT
    } else if ($request->to_save_draft()) {

        // attempt to save draft, handle exceptions
        $message = \block_quickmail\messenger\messenger::save_broadcast_draft($USER, $course, $broadcast_form->get_data(), $broadcast_recipient_filter, $draft_message);
        
        // clear any recipient user filtering session data
        $broadcast_recipient_filter->clear_session();

        $request->redirect_as_info(block_quickmail_string::get('redirect_back_to_course_from_message_after_save', $course->fullname), '/my');
    }
} catch (\block_quickmail\exceptions\validation_exception $e) {
    $broadcast_form->set_error_exception($e);
} catch (\block_quickmail\exceptions\critical_exception $e) {
    print_error('critical_error', 'block_quickmail');
}

////////////////////////////////////////
/// RENDER PAGE
////////////////////////////////////////

$rendered_broadcast_form = $renderer->broadcast_message_component([
    'context' => $system_context,
    'user' => $USER,
    'course' => $course,
    'broadcast_form' => $broadcast_form,
]);

$rendered_broadcast_recipient_filter_results = $renderer->broadcast_recipient_filter_results_component([
    'broadcast_recipient_filter' => $broadcast_recipient_filter
]);

echo $OUTPUT->header();
$broadcast_form->render_error_notification();

// BEGIN RENDERING USER FILTER/RESULTS
$broadcast_recipient_filter->render_add();
$broadcast_recipient_filter->render_active();

if ($broadcast_recipient_filter->get_result_user_count()) {
    // PAGINATION BAR (if appropriate)
    if ($broadcast_recipient_filter->get_result_user_count() > $page_params['per_page']) {
        $broadcast_recipient_filter->render_paging_bar();
    }

    // TABLE OF DISPLAY USERS
    echo $rendered_broadcast_recipient_filter_results;

    // PAGINATION BAR (if appropriate)
    if ($broadcast_recipient_filter->get_result_user_count() > $page_params['per_page']) {
        $broadcast_recipient_filter->render_paging_bar();
    }
}
// END RENDERING USER FILTER/RESULTS

echo $rendered_broadcast_form;
echo $OUTPUT->footer();
