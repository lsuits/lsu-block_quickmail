<?php

// Written at Louisiana State University

require_once('../../config.php');
require_once('lib.php');

require_login();

$courseid = required_param('courseid', PARAM_INT);
$type = optional_param('type', 'log', PARAM_ALPHA);
$typeid = optional_param('typeid', 0, PARAM_INT);
$action = optional_param('action', null, PARAM_ALPHA);
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$userid = optional_param('userid', $USER->id, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('no_course', 'block_quickmail', '', $courseid);
}

$context = get_context_instance(CONTEXT_COURSE, $courseid);

// Has to be in on of these
if (!in_array($type, array('log', 'drafts'))) {
    print_error('not_valid', 'block_quickmail', '', $type);
}

$canimpersonate = has_capability('block/quickmail:canimpersonate', $context);
if (!$canimpersonate and $userid != $USER->id) {
    print_error('not_valid_user', 'block_quickmail');
}

$config = quickmail::load_config($courseid);

$valid_actions = array('delete', 'confirm');

$can_send = has_capability('block/quickmail:cansend', $context);

$proper_permission = ($can_send or !empty($config['allowstudents']));

//managers can delete by capability 'candelete'; 
//those with 'cansend' (incl students, if $config['allowstudents']) can only delete drafts; 
$can_delete = (has_capability('block/quickmail:candelete', $context) or ($can_send and $type == 'drafts') or ($proper_permission and $type == 'drafts'));

// Stops students from tempering with history
if (!$proper_permission or (!$can_delete and in_array($action, $valid_actions))) {
    print_error('no_permission', 'block_quickmail');
}

if (isset($action) and !in_array($action, $valid_actions)) {
    print_error('not_valid_action', 'block_quickmail', '', $action);
}

if (isset($action) and empty($typeid)) {
    print_error('not_valid_typeid', 'block_quickmail', '', $action);
}

$blockname = quickmail::_s('pluginname');
$header = quickmail::_s($type);

$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->navbar->add($blockname);
$PAGE->navbar->add($header);
$PAGE->set_title($blockname . ': ' . $header);
$PAGE->set_heading($blockname . ': ' . $header);
$PAGE->set_url('/course/view.php', array('id' => $courseid));
$PAGE->set_pagetype($blockname);

$dbtable = 'block_quickmail_' . $type;

$params = array('userid' => $userid, 'courseid' => $courseid);
$count = $DB->count_records($dbtable, $params);

switch ($action) {
    case "confirm":
        if (quickmail::cleanup($dbtable, $context->id, $typeid)) {
            $url = new moodle_url('/blocks/quickmail/emaillog.php', array(
                'courseid' => $courseid,
                'type' => $type
            ));
            redirect($url);
        } else
            print_error('delete_failed', 'block_quickmail', '', $typeid);
    case "delete":
        $html = quickmail::delete_dialog($courseid, $type, $typeid);
        break;
    default:
        $html = quickmail::list_entries($courseid, $type, $page, $perpage, $userid, $count, $can_delete);
}

$html.= html_writer::link(
    new moodle_url(
            '/blocks/quickmail/email.php', 
            array('courseid' => $courseid)),
    quickmail::_s('composenew')
);

if($canimpersonate and $USER->id != $userid) {
    $user = $DB->get_record('user', array('id' => $userid));
    $header .= ' for '. fullname($user);
}

echo $OUTPUT->header();
echo $OUTPUT->heading($header);

if($canimpersonate) {
    $sql = "SELECT DISTINCT(l.userid), u.firstname, u.lastname
                FROM {block_quickmail_$type} l,
                     {user} u
                WHERE u.id = l.userid AND courseid = ? ORDER BY u.lastname";
    $users = $DB->get_records_sql($sql, array($courseid));

    $user_options = array_map(function($user) { return fullname($user); }, $users);

    $url = new moodle_url('emaillog.php', array(
        'courseid' => $courseid,
        'type' => $type
    ));

    $default_option = array('' => quickmail::_s('select_users'));

    echo $OUTPUT->single_select($url, 'userid', $user_options, $userid, $default_option);
}

if(empty($count)) {
    echo $OUTPUT->notification(quickmail::_s('no_'.$type));

    echo $OUTPUT->continue_button('/blocks/quickmail/email.php?courseid='.$courseid);

    echo $OUTPUT->footer();
    exit;
}

echo $html;

echo $OUTPUT->footer();
