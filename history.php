<?php

require_once('../../config.php');
// require_once('../../enrol/externallib.php');
// require_once('../../lib/weblib.php');

$page_url = '/blocks/quickmail/history.php';

$page_params = [
    'courseid' => optional_param('courseid', 0, PARAM_INT), // course id, if any, for filtering
];

require_login();

$page_context = block_quickmail_plugin::resolve_context('system');

////////////////////////////////////////
/// CONSTRUCT PAGE
////////////////////////////////////////

$PAGE->set_context($page_context);
$PAGE->set_pagetype('block-quickmail');
$PAGE->set_pagelayout('standard');
$PAGE->set_title(block_quickmail_plugin::_s('pluginname') . ': ' . block_quickmail_plugin::_s('history'));
$PAGE->set_url(new moodle_url($page_url, $page_params));
$PAGE->navbar->add(block_quickmail_plugin::_s('pluginname'));
$PAGE->navbar->add(block_quickmail_plugin::_s('history'));
$PAGE->set_heading(block_quickmail_plugin::_s('pluginname') . ': ' . block_quickmail_plugin::_s('history'));
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/blocks/quickmail/style.css'));
$PAGE->requires->js_call_amd('block_quickmail/historical-index', 'init');

////////////////////////////////////////
/// INSTANTIATE PAGE RENDERER
////////////////////////////////////////
$renderer = $PAGE->get_renderer('block_quickmail');

// get all "historical" (queued or sent) messages belonging to this user
$historical_messages = block_quickmail\persistents\message::get_all_historical_for_user($USER->id);

// get the rendered index
$rendered_historical_message_index = $renderer->historical_message_index_component([
    'historical_messages' => $historical_messages,
    'user' => $USER,
    'course_id' => $page_params['courseid'],
]);

echo $OUTPUT->header();

// display the historical message index table
echo $rendered_historical_message_index;

echo $OUTPUT->footer();

////////////////////////////////////////////////////////////
function dd($thing) {
    var_dump($thing);die;
}