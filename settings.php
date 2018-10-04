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

global $CFG, $DB;

if (block_quickmail\migrator\migrator::old_tables_exist()) {
    $msp = get_string('pluginname', 'block_quickmail') . ' ' . get_string('migrate', 'block_quickmail');
    $ADMIN->add('blocksettings', new admin_externalpage('blockquickmail', $msp, new moodle_url('/blocks/quickmail/migrate.php')));
}

if($ADMIN->fulltree) {
    $never_no_or_yes_options = [
        -1 => get_string('never'), 
        0 => get_string('no'), 
        1 => get_string('yes')
    ];

    $no_or_yes_options = [
        0 => get_string('no'), 
        1 => get_string('yes')
    ];

    $no_yes_or_force_options = [
        0 => get_string('no'), 
        1 => get_string('yes'),
        2 => get_string('force'), 
    ];

    ///////////////////////////////////////////////////////////
    ///
    ///  ALLOW STUDENTS TO SEND QUICKMAIL MESSAGES?
    ///  
    ///////////////////////////////////////////////////////////
    
    $settings->add(
        new admin_setting_configselect(
            'block_quickmail_allowstudents',
            block_quickmail_string::get('allowstudents'), 
            block_quickmail_string::get('allowstudents_desc'), 
            0, // <-- default
            $never_no_or_yes_options
        )
    );

    ///////////////////////////////////////////////////////////
    ///
    ///  ROLE SELECTION 
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
        return $role->name; 
    }, $roles);

    $settings->add(
        new admin_setting_configmultiselect(
            'block_quickmail_roleselection',
            block_quickmail_string::get('selectable_roles'), 
            block_quickmail_string::get('selectable_roles_desc'),
            $default_roles_keys, // <-- default
            $block_quickmail_roleselection_options
        )
    );

    ///////////////////////////////////////////////////////////
    ///
    ///  SEND MESSAGES AS BACKGROUND TASKS
    ///  
    ///////////////////////////////////////////////////////////

    $settings->add(
        new admin_setting_configselect(
            'block_quickmail_send_as_tasks',
            block_quickmail_string::get('send_as_tasks'), 
            block_quickmail_string::get('send_as_tasks_help'),
            1,  // <-- default
            $no_or_yes_options
        )
    );

    ///////////////////////////////////////////////////////////
    ///
    ///  SEND NOW RECIPIENT THRESHOLD
    ///  
    ///////////////////////////////////////////////////////////
    
    $settings->add(
        new admin_setting_configtext(
            'block_quickmail_send_now_threshold',
            block_quickmail_string::get('send_now_threshold'), 
            block_quickmail_string::get('send_now_threshold_desc'),
            50 // <-- default
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
            block_quickmail_string::get('receipt'), 
            block_quickmail_string::get('receipt_help'),
            0,  // <-- default
            $no_or_yes_options
        )
    );

    ///////////////////////////////////////////////////////////
    ///
    ///  ALLOW SENDER TO CC MENTORS OF RECIPIENTS ?
    ///  
    ///////////////////////////////////////////////////////////

    $settings->add(
        new admin_setting_configselect(
            'block_quickmail_allow_mentor_copy',
            block_quickmail_string::get('allow_mentor_copy'), 
            block_quickmail_string::get('allow_mentor_copy_help'),
            0,  // <-- default
            $no_yes_or_force_options
        )
    );

    ///////////////////////////////////////////////////////////
    ///
    ///  EMAIL PROFILE FIELDS
    ///  
    ///////////////////////////////////////////////////////////

    $settings->add(
        new admin_setting_configmultiselect(
            'block_quickmail_email_profile_fields',
            block_quickmail_string::get('email_profile_fields'), 
            block_quickmail_string::get('email_profile_fields_desc'),
            [], // <-- default
            block_quickmail_plugin::get_user_profile_field_array()
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
        'shortname' => get_string('shortnamecourse'),
        'fullname' => get_string('fullname')
    ];

    $settings->add(
        new admin_setting_configselect(
            'block_quickmail_prepend_class',
            block_quickmail_string::get('prepend_class'), 
            block_quickmail_string::get('prepend_class_desc'),
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
        'strictferpa' => block_quickmail_string::get('strictferpa'),
        'courseferpa' => block_quickmail_string::get('courseferpa'),
        'noferpa' => block_quickmail_string::get('noferpa')
    ];

    $settings->add(
        new admin_setting_configselect(
            'block_quickmail_ferpa',
            block_quickmail_string::get('ferpa'), 
            block_quickmail_string::get('ferpa_desc'),
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
            block_quickmail_string::get('downloads'), 
            block_quickmail_string::get('downloads_desc'),
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
            block_quickmail_string::get('additionalemail'), 
            block_quickmail_string::get('additionalemail_desc'),
            0   // <-- default
        )
    );

    ///////////////////////////////////////////////////////////
    ///
    ///  MESSAGING "CHANNEL" OPTIONS
    ///  
    ///////////////////////////////////////////////////////////

    $block_quickmail_message_types_available_options = [
        'all' => block_quickmail_string::get('message_type_available_all'),
        'email' => block_quickmail_string::get('message_type_available_email')
    ];

    // allow messaging as an option only if messaging is enabled
    if ( ! empty($CFG->messaging)) {
        $block_quickmail_message_types_available_options['message'] = block_quickmail_string::get('message_type_available_message');
    }

    $settings->add(
        new admin_setting_configselect(
            'block_quickmail_message_types_available', 
            block_quickmail_string::get('message_types_available'), 
            block_quickmail_string::get('message_types_available_desc'),
            'all',  // <-- default
            $block_quickmail_message_types_available_options
        )
    );

    ///////////////////////////////////////////////////////////
    ///
    ///  ENABLE NOTIFICATIONS ?
    ///  
    ///////////////////////////////////////////////////////////

    $settings->add(
        new admin_setting_configselect(
            'block_quickmail_notifications_enabled',
            block_quickmail_string::get('notifications_enabled'), 
            block_quickmail_string::get('notifications_enabled_desc'),
            0,  // <-- default
            $no_or_yes_options
        )
    );

    ///////////////////////////////////////////////////////////
    ///
    ///  MIGRATION CHUNK SIZE
    ///  
    ///////////////////////////////////////////////////////////
    
    $settings->add(
        new admin_setting_configtext(
            'block_quickmail_migration_chunk_size',
            block_quickmail_string::get('migration_chunk_size'), 
            block_quickmail_string::get('migration_chunk_size_desc'),
            1000 // <-- default
        )
    );

}
