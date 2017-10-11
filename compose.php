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
 * @copyright  2008-2017 Louisiana State University
 * @copyright  2008-2017 Adam Zapletal, Chad Mazilly, Philip Cali, Robert Russo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
// require_once('../../enrol/externallib.php');
// require_once('../../lib/weblib.php');


$page_url = '/blocks/quickmail/compose.php';

$page_params = [
    'courseid' => optional_param('courseid', 0, PARAM_INT),
    // 'draftid' => optional_param('draftid', 0, PARAM_INT),
];

require_login($page_params['courseid']);

$page_context = block_quickmail_plugin::resolve_context($page_params['courseid']);

// throw exception if user not authorized
// TODO: catch this and redirect
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
$PAGE->requires->css(new moodle_url($CFG->wwwroot . "/blocks/quickmail/style.css"));
$PAGE->requires->jquery();
// $PAGE->requires->js_call_amd('block_quickmail/compose-message', 'init', ['courseid' => $page_params['courseid']]);
// $PAGE->requires->js('/blocks/quickmail/js/selection.js'); // Get rid of this

// TODO: get draft here, if any

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
    $COURSE
    // $draft_message,
);

////////////////////////////////////////
/// HANDLE COMPOSE FORM SUBMISSION (if any)
////////////////////////////////////////

// instantiate "compose message" request
$compose_request = \block_quickmail\requests\compose_message_request::make_compose_request($compose_form);

// if cancelling form
if ($compose_request->was_cancelled()) {
    
    // redirect back to course
    $compose_request->redirect_back_to_course();

// if sending message
} else if ($compose_request->to_send_message()) {

    dd('send!');

    dd(\block_quickmail_plugin::get_output_channel());

    // instantiate sender here, and do the thing

    // validate request and send messages
    // $sender_response = block_quickmail_sender::send_composed($compose_request);
    // dd($sender_response);
    // after send redirect to history

// if saving draft
} else if ($compose_request->to_save_draft()) {
    dd('save draft!');
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

function dd($thing) {
    var_dump($thing);die;
}