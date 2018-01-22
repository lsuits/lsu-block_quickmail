<?php

// page params for ui filter
$filterparams = $typeid > 0 ? array('courseid'=>$courseid, 'type'=>$type, 'typeid'=>$typeid) : null;

// Get Our users
$fields = array(
    'realname'      => 1,
    'lastname'      => 1,
    'firstname'     => 1,
    'email'         => 1,
    'city'          => 1,
    'country'       => 1,
    'confirmed'     => 1,
    'suspended'     => 1,
    'profile'       => 1,
    'courserole'    => 0,
    'systemrole'    => 0,
    'username'      => 0,
    'cohort'        => 1,
    'firstaccess'   => 1,
    'lastaccess'    => 0,
    'neveraccessed' => 1,
    'timemodified'  => 1,
    'nevermodified' => 1,
    'auth'          => 1,
    'mnethostid'    => 1,
    'language'      => 1,
    'firstnamephonetic' => 1,
    'lastnamephonetic' => 1,
    'middlename' => 1,
    'alternatename' => 1
);

$ufiltering         = new user_filtering($fields, null, $filterparams);
list($sql, $params) = $ufiltering->get_sql_filter();
$usersearchcount    = get_users(false, '', true, null, '', '', '', '', '', 
                '*', $sql, $params);

if($fmid == 1){
    $sql = 'id IN (' . $log_message->failuserids . ')';
}

$display_users  = empty($sql) ? array() :
    get_users_listing($sort, $direction, $page*$perpage, 
    $perpage, '', '', '', $sql, $params);

$users          = empty($sql) ? array() :
    get_users_listing($sort, $direction, 0, 
    0, '', '', '', $sql, $params);

// Start work   
if($fmid != 1){
    $ufiltering->display_add();
    $ufiltering->display_active();
}

$paging_bar = !$sql ? '' :
    $OUTPUT->paging_bar($usersearchcount, $page, $perpage,
        new moodle_url('/blocks/quickmail/admin_email.php', array(
            'sort' => $sort,
            'dir' => $direction,
            'perpage' => $perpage
        )
    ));

if(!empty($sql)) {
    echo $OUTPUT->heading("Found $usersearchcount User(s)");
}

echo $paging_bar;

if(!empty($display_users)) {
    $columns = array('firstname', 'lastname', 'email', 'city', 'lastaccess');
    foreach($columns as $column) {
        $direction = ($sort == $column and $direction == "ASC") ? "DESC" : "ASC";
        $$column = html_writer::link('admin_email.php?sort='.$column.'&dir='.
            $direction, get_string($column));
    }
    $table = new html_table();

    $table->head = array("$firstname / $lastname", $email, $city, $lastaccess); 
    $table->data = array_map(function($user) {
        $fullname = fullname($user);
        $email    = $user->email;
        $city     = $user->city;
        $lastaccess_time = isset($user->lastaccess) ? 
            format_time(time() - $user->lastaccess) : get_string('never');
        return array($fullname, $email, $city, $lastaccess_time);
    }, $display_users);
    echo html_writer::table($table);
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// BEGIN: TESTING USER SELECTION
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// NEED THESE TO BUILD THE SELECTION FORM?!?

// 'selected' => $selected,
// 'users' => $users,
// 'roles' => $roles,
// 'groups' => $groups,
// 'users_to_roles' => $users_to_roles,
// 'users_to_groups' => $users_to_groups,

// // get roles in this course
// $course_roles = get_roles_used_in_context($page_context);

// // get configured roles that quickmail accepts
// // $filter_roles => $quickmail_accepted_roles
// $quickmail_accepted_roles = $DB->get_records_select('role', sprintf('id IN (%s)', block_quickmail_config::_c('roleselection', $course)));

// // get the common roles between configuration and this course data
// $roles = array_uintersect($course_roles, $quickmail_accepted_roles, function($a, $b) {
//     return strcmp($a->shortname, $b->shortname);
// });

// // get all groups on this course
// // $allgroups => $course_groups
// $course_groups = groups_get_all_groups($course->id);

// // initialize master capability status to true
// $mastercap = true;
// $groups = $allgroups;

// dd($course_groups);

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// END: TESTING USER SELECTION
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



// dd($compose_message_request->form_data);

// dd($compose_message_request->form->draft_message);

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// BEGIN TRYING TO DEAL WITH ATTACHMENTS / MESSAGE FILES
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// // dd(file_get_submitted_draft_itemid('message_editor'));

// // get the user context for file uploads
// $usercontext = context_user::instance($USER->id);

// $attachments_item_id = file_get_submitted_draft_itemid('attachments');

// $thing = file_prepare_draft_area(
//     $attachments_item_id, 
//     $usercontext->id, 
//     'block_quickmail', 
//     'message_attachments', /////
//     $draft_message ? $draft_message->get('id') : null, 
//     block_quickmail_config::get_filemanager_options()
// );

// // dd($thing);

// // get a file storage instance
// $fs = get_file_storage();

// // get the uploaded file objects (only)
// $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $compose_message_request->attachments_item_id, 'id');

// // filter out any directory references
// $files = array_filter($files, function($file) {
//     return ! $file->is_directory() and $file->get_filename() != '.';
// });

// $full_filepaths = array_map(function ($file) { 
//     return $file->get_filepath() . $file->get_filename(); 
// }, $files);

// // dd($full_filepaths);

// // dd(implode(',', $full_filepaths));

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// END: TRYING TO DEAL WITH ATTACHMENTS / MESSAGE FILES
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////