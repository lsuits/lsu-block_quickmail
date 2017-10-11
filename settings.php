<?php

defined('MOODLE_INTERNAL') || die;

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

if($ADMIN->fulltree) {
    // require_once $CFG->dirroot . '/blocks/quickmail/lib.php';

    $select = array(-1 => get_string('never'), 0 => get_string('no'), 1 => get_string('yes'));

    $allow = block_quickmail_plugin::_s('allowstudents');
    $allowdesc = block_quickmail_plugin::_s('allowstudentsdesc');
    $settings->add(
        new admin_setting_configselect('block_quickmail_allowstudents',
            $allow, $allowdesc, 0, $select
        )
    );

    $roles = $DB->get_records('role', null, 'sortorder ASC');

    $default_sns = array('editingteacher', 'teacher', 'student');
    $defaults = array_filter($roles, function ($role) use ($default_sns) {
        return in_array($role->shortname, $default_sns);
    });

    $only_names = function ($role) { return $role->shortname; };

    $select_roles = block_quickmail_plugin::_s('select_roles');
    $settings->add(
        new admin_setting_configmultiselect('block_quickmail_roleselection',
            $select_roles, $select_roles,
            array_keys($defaults),
            array_map($only_names, $roles)
        )
    );

    $settings->add(
        new admin_setting_configselect('block_quickmail_receipt',
        block_quickmail_plugin::_s('receipt'), block_quickmail_plugin::_s('receipt_help'),
        0, $select
        )
    );

    $prependoptions = array(
        0 => get_string('none'),
        'idnumber' => get_string('idnumber'),
        'shortname' => get_string('shortname')
    );

    $settings->add(
        new admin_setting_configselect('block_quickmail_prepend_class',
            block_quickmail_plugin::_s('prepend_class'), block_quickmail_plugin::_s('prepend_class_desc'),
            0, $prependoptions
        )
    );

    $ferpaoptions = array(
        'strictferpa' => block_quickmail_plugin::_s('strictferpa'),
        'courseferpa' => block_quickmail_plugin::_s('courseferpa'),
        'noferpa' => block_quickmail_plugin::_s('noferpa')
    );

    $settings->add(
        new admin_setting_configselect('block_quickmail_ferpa',
            block_quickmail_plugin::_s('ferpa'), block_quickmail_plugin::_s('ferpa_desc'),
            'strictferpa', $ferpaoptions
        )
    );

    $settings->add(
        new admin_setting_configcheckbox('block_quickmail_downloads',
            block_quickmail_plugin::_s('downloads'), block_quickmail_plugin::_s('downloads_desc'),
            1
        )
    );

    $settings->add(
        new admin_setting_configcheckbox('block_quickmail_addionalemail',
            block_quickmail_plugin::_s('addionalemail'), block_quickmail_plugin::_s('addionalemail_desc'),
            0
        )
    );

    $outputchanneloptions = array(
        'message' => block_quickmail_plugin::_s('output_as_message'),
        'email' => block_quickmail_plugin::_s('output_as_email')
    );

    $settings->add(
        new admin_setting_configselect('block_quickmail_output_channel',
            block_quickmail_plugin::_s('output_channel'), block_quickmail_plugin::_s('output_channel_desc'),
            'message', $outputchanneloptions
        )
    );

}
