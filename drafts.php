<?php

require_once('../../config.php');
// require_once('../../enrol/externallib.php');
// require_once('../../lib/weblib.php');

$page_url = '/blocks/quickmail/drafts.php';

$page_params = [
    'courseid' => optional_param('courseid', 0, PARAM_INT), // course id, if any, for initial filtering
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
// $PAGE->requires->js_call_amd('block_quickmail/manage-signatures', 'init', ['courseid' => $page_params['courseid']]);

////////////////////////////////////////
/// INSTANTIATE PAGE RENDERER
////////////////////////////////////////
$renderer = $PAGE->get_renderer('block_quickmail');

// get all (unsent) message drafts belonging to this user
$draft_messages = block_quickmail\persistents\message::get_all_unsent_drafts_for_user($USER->id, $page_params['courseid']);

// get all courses in which this user has saved drafts
// TODO - fix this to make it real!!!
$user_course_array = [
    '0' => 'All Courses',
    '3' => 'Pizza 101'
];

// get the rendered index
$rendered_draft_message_index = $renderer->draft_message_index_component([
    'draft_messages' => $draft_messages,
    'user' => $USER,
    'course_id' => $page_params['courseid'],
    'user_course_array' => $user_course_array
]);

echo $OUTPUT->header();

// display the draft message index table
echo $rendered_draft_message_index;

echo $OUTPUT->footer();

////////////////////////////////////////////////////////////
function dd($thing) {
    var_dump($thing);die;
}