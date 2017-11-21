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
$PAGE->requires->js_call_amd('block_quickmail/draft-index', 'init');

////////////////////////////////////////
/// INSTANTIATE PAGE RENDERER
////////////////////////////////////////
$renderer = $PAGE->get_renderer('block_quickmail');

// get all (unsent) message drafts belonging to this user
$all_draft_messages = block_quickmail\persistents\message::get_all_unsent_drafts_for_user($USER->id);

// get draft messages for course selection
$draft_messages = filter_drafts_for_course_selection($all_draft_messages, $page_params['courseid']);

// get the rendered index
$rendered_draft_message_index = $renderer->draft_message_index_component([
    'draft_messages' => $draft_messages,
    'user' => $USER,
    'course_id' => $page_params['courseid'],
]);

echo $OUTPUT->header();

// display the draft message index table
echo $rendered_draft_message_index;

echo $OUTPUT->footer();

/**
 * Returns an array of draft messages for a specific course given an array and course id
 * 
 * @param  array  $all_draft_messages
 * @param  int    $course_id
 * @return array
 */
function filter_drafts_for_course_selection($all_draft_messages, $course_id) {
    if ($course_id) {
        // if a course is selected, filter out any non-selected-course drafts
        $draft_messages = array_filter($all_draft_messages, function($draft) use ($course_id) {
            return $draft->get('course_id') == $course_id;
        });
    } else {
        // otherwise, include all draft messages
        $draft_messages = $all_draft_messages;
    }

    return $draft_messages;
}

////////////////////////////////////////////////////////////
function dd($thing) {
    var_dump($thing);die;
}