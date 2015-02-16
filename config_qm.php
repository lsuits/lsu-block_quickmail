<?php

// Written at Louisiana State University

require_once '../../config.php';
require_once 'lib.php';
require_once 'config_qm_form.php';

require_login();

$courseid = required_param('courseid', PARAM_INT);
$reset = optional_param('reset', 0, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('no_course', 'block_quickmail', '', $courseid);
}

$context = context_course::instance($courseid);

require_capability('block/quickmail:canconfig', $context);

$blockname = quickmail::_s('pluginname');
$header = quickmail::_s('config');

$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_url('/blocks/quickmail/config_qm.php', array('courseid' => $courseid));
$PAGE->set_title($blockname . ': '. $header);
$PAGE->set_heading($blockname. ': '. $header);
$PAGE->navbar->add($blockname);
$PAGE->navbar->add($header);
$PAGE->set_pagetype(quickmail::PAGE_TYPE);

$changed = false;

if ($reset) {
    $changed = true;
    quickmail::default_config($courseid);
}

$roles = role_fix_names(get_all_roles($context), $context, ROLENAME_ALIAS, true);

$form = new config_form(null, array(
    'courseid' => $courseid,
    'roles' => $roles
));

if ($data = $form->get_data()) {
    $config = get_object_vars($data);

    unset($config['save'], $config['courseid']);

    $config['roleselection'] = implode(',', $config['roleselection']);

    quickmail::save_config($courseid, $config);
    $changed = true;
}

$config = quickmail::load_config($courseid);
$config['roleselection'] = explode(',', $config['roleselection']);

$form->set_data($config);

echo $OUTPUT->header();
echo $OUTPUT->heading($header);

echo $OUTPUT->box_start();

if ($changed) {
    echo $OUTPUT->notification(get_string('changessaved'), 'notifysuccess');
}

$form->display();
echo $OUTPUT->box_end();

echo $OUTPUT->footer();
