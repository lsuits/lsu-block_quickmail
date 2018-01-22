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

    $never_no_or_yes_options = [
        -1 => get_string('never'), 
        0 => get_string('no'), 
        1 => get_string('yes')
    ];

    $no_or_yes_options = [
        0 => get_string('no'), 
        1 => get_string('yes')
    ];

    ///////////////////////////////////////////////////////////
    ///
    ///  ALLOW STUDENTS TO USE QUICKMAIL ?
    ///  
    ///////////////////////////////////////////////////////////
    
    $settings->add(
        new admin_setting_configselect(
            'block_quickmail_allowstudents',
            block_quickmail_plugin::_s('allowstudents'), 
            block_quickmail_plugin::_s('allowstudentsdesc'), 
            0, // <-- default
            $never_no_or_yes_options
        )
    );

    ///////////////////////////////////////////////////////////
    ///
    ///  ROLE FILTERING
    ///  
    ///////////////////////////////////////////////////////////

    // get all roles
    $roles = $DB->get_records('role', null, 'sortorder ASC');

    // set default role selections by shortname
    $default_role_names = [
        'editingteacher', 
        'teacher', 
        'student'
    ];
    
    // get actual default roles
    $default_roles_keys = array_keys(array_filter($roles, function ($role) use ($default_role_names) {
        return in_array($role->shortname, $default_role_names);
    }));

    // build a $value=>$label array of options
    $block_quickmail_roleselection_options = array_map(function ($role) { 
        return $role->shortname; 
    }, $roles);

    $settings->add(
        new admin_setting_configmultiselect(
            'block_quickmail_roleselection',
            block_quickmail_plugin::_s('select_roles'), 
            block_quickmail_plugin::_s('select_roles'),
            $default_roles_keys, // <-- default
            $block_quickmail_roleselection_options
        )
    );

    ///////////////////////////////////////////////////////////
    ///
    ///  SENDER RECEIVES A COPY ?
    ///  
    ///////////////////////////////////////////////////////////

    $settings->add(
        new admin_setting_configselect(
            'block_quickmail_receipt',
            block_quickmail_plugin::_s('receipt'), 
            block_quickmail_plugin::_s('receipt_help'),
            0,  // <-- default
            $no_or_yes_options
        )
    );

    ///////////////////////////////////////////////////////////
    ///
    ///  SUBJECT PREPEND OPTIONS
    ///  
    ///////////////////////////////////////////////////////////

    $block_quickmail_prepend_class_options = [
        0 => get_string('none'),
        'idnumber' => get_string('idnumber'),
        'shortname' => get_string('shortname'),
        'fullname' => get_string ('fullname')
    ];

    $settings->add(
        new admin_setting_configselect(
            'block_quickmail_prepend_class',
            block_quickmail_plugin::_s('prepend_class'), 
            block_quickmail_plugin::_s('prepend_class_desc'),
            0,  // <-- default
            $block_quickmail_prepend_class_options
        )
    );

    ///////////////////////////////////////////////////////////
    ///
    ///  FERPA OPTIONS
    ///  
    ///////////////////////////////////////////////////////////

    $block_quickmail_ferpa_options = [
        'strictferpa' => block_quickmail_plugin::_s('strictferpa'),
        'courseferpa' => block_quickmail_plugin::_s('courseferpa'),
        'noferpa' => block_quickmail_plugin::_s('noferpa')
    ];

    $settings->add(
        new admin_setting_configselect(
            'block_quickmail_ferpa',
            block_quickmail_plugin::_s('ferpa'), 
            block_quickmail_plugin::_s('ferpa_desc'),
            'strictferpa',  // <-- default
            $block_quickmail_ferpa_options
        )
    );

    ///////////////////////////////////////////////////////////
    ///
    ///  ATTACHMENT DOWNLOAD OPTIONS
    ///  
    ///////////////////////////////////////////////////////////

    $settings->add(
        new admin_setting_configcheckbox(
            'block_quickmail_downloads', 
            block_quickmail_plugin::_s('downloads'), 
            block_quickmail_plugin::_s('downloads_desc'),
            1  // <-- default
        )
    );

    ///////////////////////////////////////////////////////////
    ///
    ///  ALLOW ADDITIONAL EXTERNAL EMAILS TO BE SENT TO ?
    ///  
    ///////////////////////////////////////////////////////////

    $settings->add(
        new admin_setting_configcheckbox(
            'block_quickmail_additionalemail', 
            block_quickmail_plugin::_s('additionalemail'), 
            block_quickmail_plugin::_s('additionalemail_desc'),
            0   // <-- default
        )
    );

    ///////////////////////////////////////////////////////////
    ///
    ///  MESSAGING "CHANNEL" OPTIONS
    ///  
    ///////////////////////////////////////////////////////////

    $block_quickmail_output_channels_available_options = [
        'all' => block_quickmail_plugin::_s('output_channel_available_all'),
        'message' => block_quickmail_plugin::_s('output_channel_available_message'),
        'email' => block_quickmail_plugin::_s('output_channel_available_email')
    ];

    $settings->add(
        new admin_setting_configselect(
            'block_quickmail_output_channels_available', 
            block_quickmail_plugin::_s('output_channels_available'), 
            block_quickmail_plugin::_s('output_channels_available_desc'),
            'all',  // <-- default
            $block_quickmail_output_channels_available_options
        )
    );

    ///////////////////////////////////////////////////////////
    ///
    ///  CUSTOM USER DATA INJECTION
    ///  
    ///////////////////////////////////////////////////////////

    // get all supported user fields (currently hard-coded)
    // TODO: change to allow for more fields?
    $supported_user_fields = block_quickmail_config::get_supported_user_fields();

    $settings->add(
        new admin_setting_configmultiselect(
            'block_quickmail_allowed_user_fields',
            block_quickmail_plugin::_s('select_allowed_user_fields'), 
            block_quickmail_plugin::_s('select_allowed_user_fields_desc'),
            [], // <-- default
            array_combine($supported_user_fields, $supported_user_fields)
        )
    );

}
