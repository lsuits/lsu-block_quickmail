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
    'id' => required_param('id', PARAM_INT), // template id
];

////////////////////////////////////////
/// AUTHENTICATION
////////////////////////////////////////

require_login();

$system_context = context_system::instance();

block_quickmail_plugin::require_user_capability('managetemplates', $USER, $system_context);

$PAGE->set_context($system_context);
$PAGE->set_url(new moodle_url('/blocks/quickmail/template_preview.php', $page_params));

$template = \block_quickmail\persistents\template::find_template_or_null($page_params['id']);

////////////////////////////////////////
/// CONSTRUCT PAGE
////////////////////////////////////////

$PAGE->set_pagetype('block-quickmail');
$PAGE->set_pagelayout('standard');
$PAGE->set_title(block_quickmail_string::get('pluginname') . ': ' . block_quickmail_string::get('template_preview'));
$PAGE->navbar->add(block_quickmail_string::get('templates'), new moodle_url('/blocks/quickmail/templates.php', ['id' => $page_params['id']]));
$PAGE->navbar->add($template->get('title'));
$PAGE->set_heading(block_quickmail_string::get('pluginname') . ': ' . block_quickmail_string::get('template_preview'));

$body = '<h1>Lorem Ipsum</h1><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas sed pellentesque nisi. In vel finibus sem. Aliquam semper tristique varius. Nunc et nunc ut velit egestas lacinia. Donec facilisis viverra massa vitae dignissim. In vel dui eget eros sagittis vehicula eget non metus. Donec tincidunt et erat nec aliquam. Nam egestas efficitur leo, vitae efficitur orci rutrum sit amet. Vivamus eget eleifend odio. Maecenas sem ante, tempus eget ligula id, porttitor imperdiet purus.</p><p>Integer cursus a arcu a scelerisque. Fusce non fringilla purus, non malesuada eros. Ut cursus vitae lorem vitae porta. Vivamus ac facilisis ante. Sed lacinia venenatis enim ut laoreet. Vestibulum in imperdiet arcu. Vestibulum feugiat odio ac sapien fringilla tincidunt nec nec nisi. Ut imperdiet felis cursus molestie feugiat. Integer eleifend, lorem a gravida mollis, metus quam eleifend leo, quis pretium mi ligula sed magna. Duis venenatis mi vel sapien pulvinar, sed laoreet purus porttitor. Nunc varius porttitor placerat.</p><p>Proin sapien quam, aliquam nec dictum in, vestibulum sit amet orci. In lobortis felis sed pellentesque auctor. Nam ultrices porta viverra. Sed non maximus orci. Proin non lacus faucibus, dictum velit non, euismod nulla. Morbi eu accumsan risus. Mauris sapien felis, placerat condimentum venenatis vitae, efficitur nec turpis. Duis quis mi laoreet, accumsan dui a, tristique orci. Quisque diam mi, iaculis vel nulla et, tincidunt lobortis dui. Suspendisse aliquet cursus arcu, a varius felis. Aenean faucibus varius placerat. Curabitur ligula augue, gravida sit amet mattis non, iaculis in nisl. Integer neque nisl, vulputate sed ipsum sit amet, blandit facilisis lacus.</p>';


echo $OUTPUT->header();
echo $template->get_formatted($body);
echo $OUTPUT->footer();