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

require_once('../../config.php');

require_login();

$page_url = '/blocks/quickmail/configuration.php';

$page_params = [
    // if courseid == 0, we can assume this is "admin scope" (as opposed to "course scope")
    'courseid' => optional_param('courseid', 0, PARAM_INT),
    // 'reset' => optional_param('reset', 0, PARAM_INT);
];

try {
    // instantiate a quickmail plugin instance
    $quickmail = block_quickmail_plugin::make($page_params);

    ////////////////////////////////////////
    /// CONSTRUCT PAGE
    ////////////////////////////////////////

    $PAGE->set_context($quickmail->context);
    
    if ($quickmail->is_in_scope('course'))
        $PAGE->set_course($quickmail->course);

    $PAGE->set_pagetype('block-quickmail');
    $PAGE->set_pagelayout('standard');
    $PAGE->set_title(block_quickmail_plugin::_s('pluginname') . ': ' . block_quickmail_plugin::_s('config'));
    $PAGE->set_url($page_url, $page_params);

    $PAGE->navbar->add(block_quickmail_plugin::_s('pluginname'));
    $PAGE->navbar->add(block_quickmail_plugin::_s('config'));
    $PAGE->set_heading(block_quickmail_plugin::_s('pluginname') . ': ' . block_quickmail_plugin::_s('config'));

    ////////////////////////////////////////
    /// RENDER PAGE
    ////////////////////////////////////////

    $output = $PAGE->get_renderer('block_quickmail');

    echo $output->header();
    
    // echo $output->message_nav_links($quickmail);

    // instantiate "course configuration" form
    $mform = $quickmail->make_form('course_configuration_form', $page_params);

    // form was cancelled
    if ($mform->is_cancelled()) {
        
        redirect($quickmail->get_back_url());

    // form was successfully posted
    } else if ($request = $mform->get_data()) {

        // if updating settings
        if (property_exists($request, 'save')) {
            $params = block_quickmail_plugin::validate_update_course_configuration_request($request);

            $quickmail->update_course_configuration($params);
        
            echo $OUTPUT->notification(get_string('changessaved'), 'notifysuccess');
        }

        // or, if resetting settings
        else if (property_exists($request, 'reset')) {
            $quickmail->reset_course_configuration();

            redirect($quickmail->get_back_url(), block_quickmail_plugin::_s('reset_success_message'), null, \core\output\notification::NOTIFY_SUCCESS);
        }
    }
    
    // display the configuration form
    echo $output->course_configuration_component($quickmail, [
        'course_configuration_form' => $mform,
    ]);

    echo $output->footer();

} catch (\block_quickmail\exceptions\critical_exception $e) {
    
    // show the moodle error page
    $e->render_moodle_error();
} catch (\block_quickmail\exceptions\validation_exception $e) {
    
    // show the moodle error page
    $e->render_moodle_error();
} catch (\block_quickmail\exceptions\authorization_exception $e) {
    
    // show the moodle error page
    $e->render_moodle_error();
}

function dd($thing) {
    var_dump($thing);die;
}