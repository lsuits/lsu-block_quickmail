<?php

// Written at Louisiana State University

defined('MOODLE_INTERNAL') || die;

if($ADMIN->fulltree) {
    require_once $CFG->dirroot . '/blocks/quickmail/lib.php';

    $select = array(-1 => get_string('never'), 0 => get_string('no'), 1 => get_string('yes'));

    $allow = quickmail::_s('allowstudents');
    $allowdesc = quickmail::_s('allowstudentsdesc');
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

    $select_roles = quickmail::_s('select_roles');
    $settings->add(
        new admin_setting_configmultiselect('block_quickmail_roleselection',
            $select_roles, $select_roles,
            array_keys($defaults),
            array_map($only_names, $roles)
        )
    );

    $settings->add(
        new admin_setting_configselect('block_quickmail_receipt',
        quickmail::_s('receipt'), quickmail::_s('receipt_help'),
        0, $select
        )
    );

    $options = array(
        0 => get_string('none'),
        'idnumber' => get_string('idnumber'),
        'shortname' => get_string('shortname')
    );

    $settings->add(
        new admin_setting_configselect('block_quickmail_prepend_class',
            quickmail::_s('prepend_class'), quickmail::_s('prepend_class_desc'),
            0, $options
        )
    );

    $groupoptions = array(
        'strictferpa' => get_string('strictferpa', 'block_quickmail'),
        'courseferpa' => get_string('courseferpa', 'block_quickmail'),
        'noferpa' => get_string('noferpa', 'block_quickmail')
    );

    $settings->add(
        new admin_setting_configselect('block_quickmail_ferpa',
            quickmail::_s('ferpa'), quickmail::_s('ferpa_desc'),
            'strictferpa', $groupoptions
        )
    );

    $settings->add(
        new admin_setting_configcheckbox('block_quickmail_downloads',
            quickmail::_s('downloads'), quickmail::_s('downloads_desc'),
            1
        )
    );

    $settings->add(
        new admin_setting_configcheckbox('block_quickmail_addionalemail',
            quickmail::_s('addionalemail'), quickmail::_s('addionalemail_desc'),
            0
        )
    );

}
