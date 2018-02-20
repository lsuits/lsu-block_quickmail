<?php

require_once('../../config.php');
require_once 'lib.php';

$page_url = '/blocks/quickmail/sent.php';

$page_params = [
    'courseid' => optional_param('courseid', 0, PARAM_INT),
    'sort' => optional_param('sort', '', PARAM_TEXT), // (field name)
    'dir' => optional_param('dir', '', PARAM_TEXT), // asc|desc
    'page' => optional_param('page', 1, PARAM_INT),
    'per_page' => 10, // adjust as necessary, maybe turn into real param?
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
$PAGE->set_title(block_quickmail_plugin::_s('pluginname') . ': ' . block_quickmail_plugin::_s('view_sent'));
$PAGE->navbar->add(block_quickmail_plugin::_s('pluginname'));
$PAGE->navbar->add(block_quickmail_plugin::_s('view_sent'));
$PAGE->set_heading(block_quickmail_plugin::_s('pluginname') . ': ' . block_quickmail_plugin::_s('view_sent'));
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/blocks/quickmail/style.css'));
$PAGE->requires->js_call_amd('block_quickmail/sent-index', 'init');

$renderer = $PAGE->get_renderer('block_quickmail');

// get all sent messages belonging to this user (and course)
$sent_messages = block_quickmail\repos\sent_repo::get_for_user(
    $USER->id, 
    $page_params['courseid'], 
    $page_params['sort'], 
    $page_params['dir']
);

$paginated = block_quickmail\repos\pagination\paginator::get_paginated(
    $sent_messages, 
    $page_params['page'], 
    $page_params['per_page'],
    $_SERVER['REQUEST_URI']
);

///////////////////////////////////////////////////////////////////

$paginated = block_quickmail\repos\sent_repo::get_for_user(
    $USER->id, 
    $page_params['courseid'], 
    [
        'sort' => $page_params['sort'], 
        'dir' => $page_params['dir'],
        'paginate' => true,
        'page' => $page_params['page'], 
        'per_page' => $page_params['per_page'],
        'uri' => $_SERVER['REQUEST_URI']
    ]
);

$rendered_sent_message_index = $renderer->sent_message_index_component([
    'paginated' => $paginated,
    'user' => $USER,
    'course_id' => $page_params['courseid'],
    'sort_by' => $page_params['sort'],
    'sort_dir' => $page_params['dir'],
]);

echo $OUTPUT->header();
echo $rendered_sent_message_index;
echo $OUTPUT->footer();