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
 * @copyright  2008-2017 Louisiana State University
 * @copyright  2008-2017 Adam Zapletal, Chad Mazilly, Philip Cali, Robert Russo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once '../../config.php';
require_once 'lib.php';
require_once 'alt_lib.php';
require_once 'alt_form.php';

$courseid = required_param('courseid', PARAM_INT);
$action = optional_param('action', 'view', PARAM_TEXT);
$id = optional_param('id', null, PARAM_INT);
$flash = optional_param('flash', 0, PARAM_INT);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

$context = context_course::instance($courseid);
// Permission
require_login($course);
require_capability('block/quickmail:allowalternate', $context);

$blockname = quickmail::_s('pluginname');
$heading = quickmail::_s('alternate');
$title = "$blockname: $heading";

$url = new moodle_url('/blocks/quickmail/alternate.php', array('courseid' => $courseid));

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_course($course);

$PAGE->navbar->add($blockname);
$PAGE->navbar->add($heading);

$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_pagetype(quickmail::PAGE_TYPE);
$PAGE->set_pagelayout('standard');

if (!method_exists('quickmail_alternate', $action)) {
    // Always fallback on view
    $action = 'view';
}

$body = quickmail_alternate::$action($course, $id);

echo $OUTPUT->header();
echo $OUTPUT->heading($heading);

if ($flash) {
    echo $OUTPUT->notification(get_string('changessaved'), 'notifysuccess');
}

echo $body;

echo $OUTPUT->footer();
