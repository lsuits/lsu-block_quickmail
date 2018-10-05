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
    'id' => optional_param('id', 0, PARAM_INT), // signature id, if any
    'courseid' => optional_param('courseid', 0, PARAM_INT), // course id, if any, for redirection
];

////////////////////////////////////////
/// AUTHENTICATION
////////////////////////////////////////

require_login();

// if we're scoping to a specific course
if ($page_params['courseid']) {
    // check that the user can message in this course
    block_quickmail_plugin::require_user_has_course_message_access($USER, $page_params['courseid']);
}

$system_context = context_system::instance();
$PAGE->set_context($system_context);
$PAGE->set_url(new moodle_url('/blocks/quickmail/signatures.php', $page_params));

////////////////////////////////////////
/// CONSTRUCT PAGE
////////////////////////////////////////

$PAGE->set_pagetype('block-quickmail');
$PAGE->set_pagelayout('standard');
$PAGE->set_title(block_quickmail_string::get('pluginname') . ': ' . block_quickmail_string::get('manage_signatures'));
$PAGE->navbar->add(block_quickmail_string::get('pluginname'), new moodle_url('/blocks/quickmail/qm.php', array('courseid' => $page_params['courseid'])));
$PAGE->navbar->add(block_quickmail_string::get('signatures'));
$PAGE->set_heading(block_quickmail_string::get('pluginname') . ': ' . block_quickmail_string::get('manage_signatures'));
$PAGE->requires->css(new moodle_url('/blocks/quickmail/style.css'));
$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url('/blocks/quickmail/js/signature-form.js'));
$PAGE->requires->data_for_js('signaturedata', ['courseid' => $page_params['courseid']]);

block_quickmail\controllers\signature_index_controller::handle($PAGE, [
    'context' => $system_context,
    'user' => $USER,
    'course_id' => $page_params['courseid'],
    'signature_id' => $page_params['id'],
]);
