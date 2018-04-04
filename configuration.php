<?php

require_once('../../config.php');
require_once 'lib.php';

$page_url = '/blocks/quickmail/configuration.php';

$page_params = [
    'courseid' => required_param('courseid', PARAM_INT),
];

$course = get_course($page_params['courseid']);

////////////////////////////////////////
/// AUTHENTICATION
////////////////////////////////////////

require_login();
$page_context = context_course::instance($course->id);
$PAGE->set_context($page_context);
$PAGE->set_url(new moodle_url($page_url, $page_params));
block_quickmail_plugin::require_user_capability('canconfig', $page_context);

////////////////////////////////////////
/// CONSTRUCT PAGE
////////////////////////////////////////

$PAGE->set_pagetype('block-quickmail');
$PAGE->set_pagelayout('standard');
$PAGE->set_title(block_quickmail_string::get('pluginname') . ': ' . get_string('configuration'));
$PAGE->navbar->add(block_quickmail_string::get('pluginname'));
$PAGE->navbar->add(get_string('configuration'));
$PAGE->set_heading(block_quickmail_string::get('pluginname') . ': ' . get_string('configuration'));
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/blocks/quickmail/style.css'));
$PAGE->requires->js_call_amd('block_quickmail/course-config', 'init', ['courseid' => $page_params['courseid']]);

$renderer = $PAGE->get_renderer('block_quickmail');

////////////////////////////////////////
/// INSTANTIATE FORM
////////////////////////////////////////

$course_config_form = \block_quickmail\forms\course_config_form::make(
    $page_context, 
    $USER, 
    $course
);

////////////////////////////////////////
/// HANDLE REQUEST
////////////////////////////////////////

$request = block_quickmail_request::for_route('config')->with_form($course_config_form);

// if a POST was submitted, attempt to take appropriate actions
try {
    // CANCEL
    if ($request->is_form_cancellation()) {
        
        // redirect back to appropriate page
        $request->redirect_to_url('/course/view.php', ['id' => $course->id]);

    // RESTORE
    } else if ($request->to_restore_defaults()) {
        
        // delete this course's config settings
        block_quickmail_config::delete_course_config($course);

        // redirect back to the course configuration page, notifying user of update
        $request->redirect_as_success(get_string('changessaved'), '/blocks/quickmail/configuration.php', ['courseid' => $course->id]);

    // SAVE / UPDATE
    } else if ($request->to_save()) {

        // replace this course's config settings with those that were submitted
        block_quickmail_config::update_course_config($course, \block_quickmail\requests\config_request::get_transformed($request->form->get_data()));

        // redirect back to the course configuration page, notifying user of update
        $request->redirect_as_success(get_string('changessaved'), '/blocks/quickmail/configuration.php', ['courseid' => $course->id]);
    }
} catch (\block_quickmail\exceptions\validation_exception $e) {
    $course_config_form->set_error_exception($e);
} catch (\block_quickmail\exceptions\critical_exception $e) {
    print_error('critical_error', 'block_quickmail');
}

// get the rendered form
$rendered_config_form = $renderer->course_config_component([
    'context' => $page_context,
    'course' => $course,
    'user' => $USER,
    'course_config_form' => $course_config_form,
]);

////////////////////////////////////////
/// RENDER PAGE
////////////////////////////////////////

echo $OUTPUT->header();
$course_config_form->render_error_notification();
echo $rendered_config_form;
echo $OUTPUT->footer();