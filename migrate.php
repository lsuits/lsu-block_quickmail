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

define('NO_OUTPUT_BUFFERING', true);

require_once('../../config.php');
require_once 'lib.php';

////////////////////////////////////////
/// AUTHENTICATION
////////////////////////////////////////

require_login();

// must be a site admin to do this!!
if ( ! is_siteadmin()) {
    throw new moodle_exception('cannotuseadmin', 'error');
}

$system_context = context_system::instance();
$PAGE->set_context($system_context);
$PAGE->set_url(new moodle_url('/blocks/quickmail/migrate.php'));

////////////////////////////////////////
/// CONSTRUCT PAGE
////////////////////////////////////////

$PAGE->set_pagetype('block-quickmail');
$PAGE->set_pagelayout('standard');
$PAGE->set_title(block_quickmail_string::get('pluginname') . ': ' . block_quickmail_string::get('migrate'));
$PAGE->navbar->add(block_quickmail_string::get('pluginname'));
$PAGE->navbar->add(block_quickmail_string::get('migrate'));
$PAGE->set_heading(block_quickmail_string::get('pluginname') . ': ' . block_quickmail_string::get('migrate'));

echo $OUTPUT->header();

global $DB;

// if migrate task is not enabled...
    // this tool allows you to migrate any historical data from Quickmail v1 to Quickmail v2
    // if you want to do this, please enable the "task" via the admin menu

// else
    echo '<h3>Migration progress...</h3>';
    
    foreach (['drafts', 'log'] as $type) {
        $table = 'block_quickmail_' . $type;

        // pull progress data
        $total = $DB->count_records($table);
        $done = $DB->count_records($table, ['has_migrated' => true]);

        // display as progress bar
        $bar = new progress_bar($type . 'bar', 500, true);
        $label = ucfirst($type) . ' (' . number_format($done) . ' / ' . number_format($total) . ')';
        $bar->update($done, $total, $label);
    }

echo $OUTPUT->footer();