<?php

// Written at Louisiana State University

require_once('../../config.php');
require_once('lib.php');
require_once('signature_form.php');

require_login();

$courseid = optional_param('courseid', null, PARAM_INT);
$sigid = optional_param('id', 0, PARAM_INT);

if ($courseid and !$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('no_course', 'block_quickmail', '', $courseid);
}

$context = empty($courseid) ?
    get_context_instance(CONTEXT_SYSTEM) :
    get_context_instance(CONTEXT_COURSE, $courseid);

if ($data = data_submitted()) {
    if (isset($data->cancel)) {
        $to = $courseid ? '/course/view.php?id='.$courseid : '/my';
        redirect(new moodle_url($to));
    }

    if (empty($data->title) or empty($data->signature_editor['text'])) {
        $warnings[] = quickmail::_s('required');
    }

    if (empty($warnings)) {
        $data->signature = $data->signature_editor['text'];

        if (empty($data->default_flag)) {
            $data->default_flag = 0;
        }

        $params = array('userid' => $USER->id, 'default_flag' => 1);
        $default = $DB->get_record('block_quickmail_signatures', $params);

        if ($default and !empty($data->default_flag)) {
            $default->default_flag = 0;
            $DB->update_record('block_quickmail_signatures', $default);
        }

        if (!$default) {
            $data->default_flag = 1;
        }

        if (empty($data->id)) {
            $data->id = null;
            $data->id = $DB->insert_record('block_quickmail_signatures', $data);
        } else {
            $DB->update_record('block_quickmail_signatures', $data);
        }

        $url = new moodle_url('signature.php', array('id' => $data->id, 'courseid' => $course->id));
        redirect($url);
    }
}

$params = array('userid' => $USER->id);
$dbsigs = $DB->get_records('block_quickmail_signatures', $params);

$sigs = array_map(function($sig) {
    $sig->signature_editor = array('text' => $sig->signature, 'format' => 1);
    return $sig;
},$dbsigs);

$blockname = quickmail::_s('pluginname');
$header = quickmail::_s('signature');

$title = "{$blockname}: {$header}";

$PAGE->set_context($context);

if ($course) {
    $PAGE->set_course($course);
    $PAGE->set_url('/course/view.php', array('id' => $courseid));
    if (get_config('moodle', 'block_quickmail_courselayout')) {
        $PAGE->set_pagelayout('course');
    }
}

$PAGE->navbar->add($blockname);
$PAGE->navbar->add($header);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_pagetype($blockname);

echo $OUTPUT->header();
echo $OUTPUT->heading($header);

$first = array(0 => 'New '.quickmail::_s('sig'));
$only_names = function ($sig) {
    return ($sig->default_flag) ? $sig->title . ' (Default)': $sig->title;
};
$sig_options = $first + array_map($only_names, $sigs);

echo $OUTPUT->single_select('signature.php?courseid='.$courseid, 'id', $sig_options, $sigid);

$sig = (!empty($sigid) and isset($sigs[$sigid])) ? $sigs[$sigid] : new stdClass;
$sig->courseid = $courseid;

$form = new signature_form();

$form->set_data($sig);
$form->display();

echo $OUTPUT->footer();
