<?php

// Written at Louisiana State University

require_once('../../config.php');
require_once('../../enrol/externallib.php');
require_once('lib.php');
require_once('email_form.php');
require_once('../../lib/weblib.php');

require_login();

$courseid = required_param('courseid', PARAM_INT);
$type = optional_param('type', '', PARAM_ALPHA);
$typeid = optional_param('typeid', 0, PARAM_INT);
$sigid = optional_param('sigid', 0, PARAM_INT);
$messageIDresend = optional_param('fmid', 0, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('no_course', 'block_quickmail', '', $courseid);
}

if (!empty($type) and !in_array($type, array('log', 'drafts'))) {
    print_error('no_type', 'block_quickmail', '', $type);
}

if (!empty($type) and empty($typeid)) {
    $string = new stdclass;
    $string->tpe = $type;
    $string->id = $typeid;

    print_error('no_typeid', 'block_quickmail', '', $string);
}

$config = quickmail::load_config($courseid);

$context = context_course::instance($courseid);
$has_permission = (
        has_capability('block/quickmail:cansend', $context) or
        !empty($config['allowstudents'])
        );

if (!$has_permission) {
    print_error('no_permission', 'block_quickmail');
}

$sigs = $DB->get_records('block_quickmail_signatures', array('userid' => $USER->id), 'default_flag DESC');

$alt_params = array('courseid' => $course->id, 'valid' => 1);
$alternates = $DB->get_records_menu('block_quickmail_alternate', $alt_params, '', 'id, address');

$blockname = quickmail::_s('pluginname');
$header = quickmail::_s('email');

$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->navbar->add($blockname);
$PAGE->navbar->add($header);
$PAGE->set_title($blockname . ': ' . $header);
$PAGE->set_heading($blockname . ': ' . $header);
$PAGE->set_url('/course/view.php', array('courseid' => $courseid));
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');

$PAGE->requires->js('/blocks/quickmail/js/jquery.js');
$PAGE->requires->js('/blocks/quickmail/js/selection.js');

$course_roles = get_roles_used_in_context($context);

$filter_roles = $DB->get_records_select('role', sprintf('id IN (%s)', $config['roleselection']));

$roles = quickmail::filter_roles($course_roles, $filter_roles);

$allgroups = groups_get_all_groups($courseid);

$mastercap = true;
$groups = $allgroups;

$restricted_view = (
    !has_capability('moodle/site:accessallgroups', $context) and
    $config['ferpa'] == 'strictferpa'
);

$respected_view = (
    !has_capability('moodle/site:accessallgroups', $context) and
    $course->groupmode == 1 and
    $config['ferpa'] == 'courseferpa'
);

if ($restricted_view || $respected_view) {
    $mastercap = false;
    $mygroups = groups_get_user_groups($courseid);
    $gids = implode(',', array_values($mygroups['0']));
    $groups = empty($gids) ?
            array() :
            $DB->get_records_select('groups', 'id IN (' . $gids . ')');
}

$globalaccess = empty($allgroups);

// Fill the course users by
$users = array();
$users_to_roles = array();
$users_to_groups = array();

$everyone = quickmail::get_non_suspended_users($context, $courseid);

// DWE -> Check to see if there are email addresses
// DWE -> MOVING THIS CHECK DOWN TO WHERE $DATA-> ADDITIONAL_EMAILS is available. 
//if (count($everyone) == 1)
//{
//    print_error('no_users', 'block_quickmail');
//}

foreach ($everyone as $userid => $user) {
    $usergroups = groups_get_user_groups($courseid, $userid);

    $gids = ($globalaccess or $mastercap) ?
        array_values($usergroups['0']) :
        array_intersect(array_values($mygroups['0']), array_values($usergroups['0']));

    $userroles = get_user_roles($context, $userid);
    $filterd = quickmail::filter_roles($userroles, $roles);

    // Available groups
    if ((!$globalaccess and !$mastercap) and
        empty($gids) or empty($filterd) or $userid == $USER->id)
        continue;

    $groupmapper = function($id) use ($allgroups) { return $allgroups[$id]; };

    $users_to_groups[$userid] = array_map($groupmapper, $gids);
    $users_to_roles[$userid] = $filterd;
    if (!$user->suspended) {
        $users[$userid] = $user;
    }
}

if (empty($users)) {
    print_error('no_usergroups', 'block_quickmail');
}


// we are presenting the form with values populated from either the log or drafts table in the db
if (!empty($type)) {
    

    $email = $DB->get_record('block_quickmail_' . $type, array('id' => $typeid));

    if ($messageIDresend == 1) {
        $email->additional_emails = array();
        $email->failuserids = explode(',', $email->failuserids);        
    
        foreach ($email->failuserids as $failed_address_or_id) {
            if ( ! is_numeric($failed_address_or_id)) {
                $email->additional_emails [] = $failed_address_or_id;
                unset($failed_address_or_id);
            }
        }
        
        $email->additional_emails = implode(',', $email->additional_emails);
        $email->mailto 		  = implode(',', $email->failuserids);

    }
} else {
    $email = new stdClass;
    $email->id = null;
    $email->subject = optional_param('subject', '', PARAM_TEXT);
    $email->message = optional_param('message_editor[text]', '', PARAM_RAW);
    $email->mailto = optional_param('mailto', '', PARAM_TEXT);
    $email->format = $USER->mailformat;
}
$email->messageformat = $email->format;
$email->messagetext = $email->message;

$default_sigid = $DB->get_field('block_quickmail_signatures', 'id', array(
    'userid' => $USER->id, 'default_flag' => 1
));
$email->sigid = $default_sigid ? $default_sigid : -1;

// Some setters for the form
$email->type = $type;
$email->typeid = $typeid;

$editor_options = array(
    'trusttext' => true,
    'subdirs' => 1,
    'maxfiles' => EDITOR_UNLIMITED_FILES,
    'accepted_types' => '*',
    'context' => $context
);

$email = file_prepare_standard_editor(
    $email, 'message', $editor_options, $context, 'block_quickmail', $type, $email->id
);

$selected = array();
if (!empty($email->mailto)) {
    foreach (explode(',', $email->mailto) as $id) {
        $selected[$id] = $users[$id];
        unset($users[$id]);
    }
}

$form = new email_form(null, array(
    'editor_options' => $editor_options,
    'selected' => $selected,
    'users' => $users,
    'roles' => $roles,
    'groups' => $groups,
    'users_to_roles' => $users_to_roles,
    'users_to_groups' => $users_to_groups,
    'sigs' => array_map(function($sig) { return $sig->title; }, $sigs),
    'alternates' => $alternates
));

$warnings = array();

if ($form->is_cancelled()) {
    redirect(new moodle_url('/course/view.php?id=' . $courseid));
    // DWE -> DATA IS ABOUT TO BE INITIALIZED, we should check if we have selected users or emails around here. 
} else if ($data = $form->get_data()) {
    if (empty($data->subject)) {
        $warnings[] = get_string('no_subject', 'block_quickmail');
    }

    if (empty($data->mailto) && empty($data->additional_emails)) {
        $warnings[] = get_string('no_users', 'block_quickmail');
    }

    if (empty($warnings)) {

        // Submitted data
        $data->time = time();
        $data->format = $data->message_editor['format'];
        $data->message = $data->message_editor['text'];
        $data->attachment = quickmail::attachment_names($data->attachments);

        // Store data; id is needed for file storage
        if (isset($data->send)) {
            $data->id = $DB->insert_record('block_quickmail_log', $data);
            $table = 'log';
        } else if (isset($data->draft)) {
            $table = 'drafts';

            if (!empty($typeid) and $type == 'drafts') {
                $data->id = $typeid;
                $DB->update_record('block_quickmail_drafts', $data);
            } else {
                $data->id = $DB->insert_record('block_quickmail_drafts', $data);
            }
        }

        $data = file_postupdate_standard_editor(
            $data, 'message', $editor_options, $context, 'block_quickmail', $table, $data->id
        );

        $DB->update_record('block_quickmail_' . $table, $data);

        $prepender = $config['prepend_class'];
        if (!empty($prepender) and !empty($course->$prepender)) {
            $subject = "[{$course->$prepender}] $data->subject";
        } else {
            $subject = $data->subject;
        }

        // An instance id is needed before storing the file repository
        file_save_draft_area_files(
                $data->attachments, $context->id, 'block_quickmail', 'attachment_' . $table, $data->id, $editor_options
        );

        // Send emails
        if (isset($data->send)) {
            if ($type == 'drafts') {
                quickmail::draft_cleanup($context->id, $typeid);
            }

            if (!empty($sigs) and $data->sigid > -1) {
                $sig = $sigs[$data->sigid];

                $signaturetext = file_rewrite_pluginfile_urls($sig->signature, 'pluginfile.php', $context->id, 'block_quickmail', 'signature', $sig->id, $editor_options);


                $data->message .= "\n\n" .$signaturetext;
            }

            // Append links to attachments, if any
            $data->message .= quickmail::process_attachments(
                $context, $data, $table, $data->id
            );

            // Prepare html content of message
            $data->message = file_rewrite_pluginfile_urls($data->message, 'pluginfile.php', $context->id, 'block_quickmail', $table, $data->id, $editor_options);

            // Same user, alternate email
            if (!empty($data->alternateid)) {
                $user = clone($USER);
                $user->email = $alternates[$data->alternateid];
            } else {
                $user = $USER;
            }
            $data->failuserids = array();
            if(!empty($data->mailto)) {
                foreach (explode(',', $data->mailto) as $userid) {
                    // WHERE THE ACTUAL EMAILING IS HAPPENING
                    $success = email_to_user($everyone[$userid], $user, $subject, strip_tags($data->message), $data->message);

                    if (!$success) {
                        $warnings[] = get_string("no_email", 'block_quickmail', $everyone[$userid]);
                        $data->failuserids[] = $userid;
                    }
                }
            }


        $additional_email_array = explode(',', $data->additional_emails);


            $i = 0;

            foreach ($additional_email_array as $additional_email) {
                $additional_email = trim($additional_email); 
                if( ! (validate_email($additional_email))){
		    if($additional_email !== ''){
                        $warnings[] = get_string("no_email_address", 'block_quickmail', $additional_email);
		    }
		    continue;
		}


                $fakeuser = new object();
                $fakeuser->id = 99999900 + $i;
                $fakeuser->email = $additional_email;


                $additional_email_success = email_to_user($fakeuser, $user, $subject, strip_tags($data->message), $data->message);

                //force fail

                if (!$additional_email_success) {
                    $data->failuserids[] = $additional_email;

                    // will need to notify that an email is incorrect
                    $warnings[] = get_string("no_email_address", 'block_quickmail', $fakeuser->email);
                }

                $i++;
            }
            

            $data->failuserids = implode(',', $data->failuserids);
            $DB->update_record('block_quickmail_log', $data);

            if ($data->receipt) {
                email_to_user($USER, $user, $subject, strip_tags($data->message), $data->message);
            }
        }
    }
    $email = $data;
}

if (empty($email->attachments)) {
    if (!empty($type)) {
        $attachid = file_get_submitted_draft_itemid('attachment');
        file_prepare_draft_area(
            $attachid, $context->id, 'block_quickmail', 'attachment_' . $type, $typeid
        );
        $email->attachments = $attachid;
    }
}

$form->set_data($email);

if (empty($warnings)) {
    if (isset($email->send)) {
        redirect(new moodle_url('/blocks/quickmail/emaillog.php', array('courseid' => $course->id)));
    } else if (isset($email->draft)) {
        $warnings['success'] = get_string("changessaved");
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);

foreach ($warnings as $type => $warning) {
    $class = ($type === 'success') ? 'notifysuccess' : 'notifyproblem';
    echo $OUTPUT->notification($warning, $class);
}

echo html_writer::start_tag('div', array('class' => 'no-overflow'));
$form->display();
echo html_writer::end_tag('div');
echo $OUTPUT->footer();
