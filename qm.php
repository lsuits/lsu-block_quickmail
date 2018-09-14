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
    'courseid' => required_param('courseid', PARAM_INT)
];
$courseid = $page_params['courseid'];
$PAGE->set_url('/blocks/quickmail/qm.php', array('courseid' => $courseid));

////////////////////////////////////////
/// AUTHENTICATION
////////////////////////////////////////
require_login($courseid, false);
$course = get_course($courseid);
$course_context = context_course::instance($course->id);
$PAGE->set_context($course_context);
$PAGE->set_url(new moodle_url('/blocks/quickmail/compose.php', $page_params));

// throw an exception if user does not have capability to compose messages
block_quickmail_plugin::require_user_can_send('compose', $USER, $course_context);

////////////////////////////////////////
/// CONSTRUCT PAGE
////////////////////////////////////////

$PAGE->set_pagetype('block-quickmail');
$PAGE->set_pagelayout('standard');
$PAGE->set_title(block_quickmail_string::get('pluginname'));
$PAGE->navbar->add(block_quickmail_string::get('pluginname'), new moodle_url('/blocks/quickmail/qm.php', array('courseid' => $courseid)));
$PAGE->set_heading(block_quickmail_string::get('pluginname'));
$PAGE->requires->css(new moodle_url('/blocks/quickmail/style.css'));

//$renderer = $PAGE->get_renderer('block_quickmail');

echo $OUTPUT->header();
echo '<div class="qm_links">';
echo '<a class="qml compose" href="' . new moodle_url("/blocks/quickmail/compose.php", array("courseid" => $courseid)) . '">compose</a> ';
echo '<a class="qml draft" href="' . new moodle_url("/blocks/quickmail/drafts.php", array("courseid" => $courseid)) . '">drafts</a> ';
echo '<a class="qml queued" href="' . new moodle_url("/blocks/quickmail/queued.php", array("courseid" => $courseid)) . '">view scheduled</a> ';
echo '</div><div class="qm_links">';
echo '<a class="qml sent" href="' . new moodle_url("/blocks/quickmail/sent.php", array("courseid" => $courseid)) . '">view sent messages</a> ';
echo '<a class="qml signatures" href="' . new moodle_url("/blocks/quickmail/signatures.php", array("courseid" => $courseid)) . '">my signatures</a> ';
echo '<a class="qml alternate" href="' . new moodle_url("/blocks/quickmail/alternate.php", array("courseid" => $courseid)) . '">alternate emails</a> ';
echo '</div><div class="qm_links">';
echo '<a class="qml configuration" href="' . new moodle_url("/blocks/quickmail/configuration.php", array("courseid" => $courseid)) . '">configuration</a> ';
echo '<a class="qml notifications" href="' . new moodle_url("/blocks/quickmail/notifications.php", array("courseid" => $courseid)) . '">notifications</a> ';
echo '<a class="qml create_notification" href="' . new moodle_url("/blocks/quickmail/create_notification.php", array("courseid" => $courseid)) . '">create notification</a> ';
echo '</div>';
echo $OUTPUT->footer();

