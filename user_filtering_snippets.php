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