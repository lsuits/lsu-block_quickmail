<?php

$string['pluginname'] = 'Quickmail';
$string['quickmail:cansend'] = "Allows users to send email through Quickmail";
$string['quickmail:canconfig'] = "Allows users to configure Quickmail instance.";
$string['quickmail:canimpersonate'] = "Allows users to log in as other users and view history.";
$string['quickmail:allowalternate'] = "Allows users to add an alternate email for courses.";
$string['quickmail:addinstance'] = "Add a new Quickmail block";
$string['quickmail:candelete'] = "Allows users to delete email from history.";
$string['backup_history'] = 'Include Quickmail History';
$string['restore_history'] = 'Restore Quickmail History';
$string['overwrite_history'] = 'Overwrite Quickmail History';
$string['alternate'] = 'Alternate Emails';
$string['composenew'] = 'Compose New Email';
$string['email'] = 'Email';
$string['drafts'] = 'View Drafts';
$string['history'] = 'View History';
$string['log'] = 'View History';
$string['from'] = 'From';
$string['selected'] = 'Selected Recipients';
$string['add_button'] = 'Add';
$string['remove_button'] = 'Remove';
$string['add_all'] = 'Add All';
$string['remove_all'] = 'Remove All';
$string['role_filter'] = 'Role Filter';
$string['no_filter'] = 'No filter';
$string['potential_users'] = 'Potential Recipients';
$string['potential_sections'] = 'Potential Sections';
$string['no_section'] = 'Not in a section';
$string['all_sections'] = 'All Sections';
$string['attachment'] = 'Attachment(s)';
$string['subject'] = 'Subject';
$string['message'] = 'Message';
$string['send_email'] = 'Send Email';
$string['save_draft'] = 'Save Draft';
$string['actions'] = 'Actions';
$string['signature'] = 'Signatures';
$string['delete_confirm'] = 'Are you sure you want to delete message with the following details: {$a}';
$string['title'] = 'Title';
$string['sig'] ='Signature';
$string['default_flag'] = 'Default';
$string['config'] = 'Configuration';
$string['receipt'] = 'Receive a copy';
$string['receipt_help'] = 'Receive a copy of the email being sent';

$string['no_alternates'] = 'No alternate emails found for {$a->fullname}. Continue to make one.';

$string['select_users'] = 'Select Users ...';
$string['select_groups'] = 'Select Sections ...';

$string['moodle_attachments'] = 'Moodle Attachments ({$a})';
$string['download_all'] = 'Download All';
$string['qm_contents'] = 'Download File Contents';

// Config form strings
$string['allowstudents'] = 'Allow students to use Quickmail';
$string['select_roles'] = 'Roles to filter by';
$string['reset'] = 'Restore System Defaults';

$string['no_type'] = '{$a} is not in the acceptable type viewer. Please use the applciation correctly.';
$string['no_email'] = 'Could not email {$a->firstname} {$a->lastname}.';
$string['no_log'] = 'You have no email history yet.';
$string['no_drafts'] = 'You have no email drafts.';
$string['no_subject'] = 'You must have a subject';
$string['no_course'] = 'Invalid Course with id of {$a}';
$string['no_permission'] = 'You do not have permission to send emails with Quickmail.';
$string['no_usergroups'] = 'There are no users in your group capable of being emailed.';
$string['no_users'] = 'There are no users you are capable of emailing.';
$string['no_selected'] = 'You must select some users for emailing.';
$string['not_valid'] = 'This is not a valid email log viewer type: {$a}';
$string['not_valid_user'] = 'You can not view other email history.';
$string['not_valid_action'] = 'You must provide a valid action: {$a}';
$string['not_valid_typeid'] = 'You must provide a valid email for {$a}';
$string['delete_failed'] = 'Failed to delete email';
$string['required'] = 'Please fill in the required fields.';
$string['prepend_class'] = 'Prepend Course name';
$string['prepend_class_desc'] = 'Prepend the course shortname to the subject of
the email.';
$string['ferpa'] = 'FERPA Mode';
$string['ferpa_desc'] = 'Allows the system to behave either according to the course groupmode setting, ignoring the groupmode setting but separating groups, or ignoring groups altogether.';
$string['strictferpa'] = 'Always Separate Groups';
$string['courseferpa'] = 'Respect Course Mode';
$string['noferpa'] = 'No Group Respect';
$string['courselayout'] = 'Course Layout';
$string['courselayout_desc'] = 'Use _Course_ page layout  when rendering the Quickmail block pages. Enable this setting, if you are getting Moodle form fixed width issues.';

$string['are_you_sure'] = 'Are you sure you want to delete {$a->title}? This action
cannot be reversed.';

// Alternate Email strings
$string['alternate_new'] = 'Add Alternate Address';
$string['sure'] = 'Are you sure you want to delete {$a->address}? This action cannot be undone.';
$string['valid'] = 'Activation Status';
$string['approved'] = 'Approved';
$string['waiting'] = 'Waiting';
$string['entry_activated'] = 'Alternate email {$a->address} can now be used in {$a->course}.';
$string['entry_key_not_valid'] = 'Activation link is no longer valid for {$a->address}. Continue to resend activation link.';
$string['entry_saved'] = 'Alternate address {$a->address} has been saved.';
$string['entry_success'] = 'An email to verify that the address is valid has been sent to {$a->address}. Instructions on how to activate the address is contained in its contents.';
$string['entry_failure'] = 'An email could not be sent to {$a->address}. Please verify that {$a->address} exists, and try again.';
$string['alternate_from'] = 'Moodle: Quickmail';
$string['alternate_subject'] = 'Alternate email address verification';
$string['alternate_body'] = '
<p>
{$a->fullname} added {$a->address} as an alternate sending address for {$a->course}.
</p>

<p>
The purpose of this email was to verify that this address exists, and the owner
of this address has the appropriate permissions in Moodle.
</p>

<p>
If you wish to complete the verification process, please continue by directing
your browser to the following url: {$a->url}.
</p>

<p>
If the description of this email does not make any sense to you, then you may have
received it by mistake. Simply discard this message.
</p>

Thank you.
';
