<?php

defined('MOODLE_INTERNAL') || die;

if($ADMIN->fulltree) {
    require_once $CFG->dirroot . '/blocks/quickmail/lib.php';

    $select = array(0 => get_string('no'), 1 => get_string('yes'));

    $allow = quickmail::_s('allowstudents');
    $settings->add(
        new admin_setting_configselect('block_quickmail_allowstudents',
            $allow, $allow, 0, $select
        )
    );

    $roles = $DB->get_records('role', null, 'sortorder ASC');

    $default_sns = array('editingteacher', 'teacher', 'student');
    $defaults = array_filter($roles, function ($role) use ($default_sns) {
        return in_array($role->shortname, $default_sns);
    });

    $only_names = function ($role) { return $role->name; };

    $select_roles = quickmail::_s('select_roles');
    $settings->add(
        new admin_setting_configmultiselect('block_quickmail_roleselection',
            $select_roles, $select_roles,
            array_keys($defaults),
            array_map($only_names, $roles)
        )
    );
}
