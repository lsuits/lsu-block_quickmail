<?php

// general
$string['pluginname'] = 'Quickmail';

// compose message form text
$string['compose_heading'] = 'Send {$a->scope} {$a->output_channel}';
$string['subject'] = 'Subject';
$string['noreply'] = 'No-Reply';
$string['body'] = 'Body';
$string['cancel'] = 'Cancel';
$string['save_draft'] = 'Save Draft';
$string['save_signature'] = 'Save Signature';
$string['delete_signature'] = 'Delete Signature';
$string['user_signature_deleted'] = 'Your signature has been deleted.';
$string['send_message'] = 'Send {$a}';
$string['additional_emails'] = 'Additional Recipient Emails';
$string['additional_emails_help'] = 'Other email addresses you would like the message sent to, in a comma or semicolon separated list. Example:

 email1@example.com, email2@example.com
 ';

$string['fullname'] ='Full name';
$string['signature_title_required'] = 'A signature title is required.';
$string['signature_signature_required'] = 'A signature is required.';
$string['sig'] ='Signature';
$string['select_signature_for_edit'] ='Select Signature To Edit';
$string['no_signatures_create'] = 'You have no signatures. {$a}.';
$string['create_one_now'] = 'Create one now';

$string['send_email'] = 'Send Email'; // <---- deprecate

// Config form strings
$string['allowstudents'] = 'Allow students to use Quickmail';
$string['allowstudentsdesc'] = 'Allow students to use Quickmail. If you choose "Never", the block cannot be configured to allow students access at the course level.';
$string['select_roles'] = 'Roles to filter by';
$string['receipt'] = 'Receive a copy';
$string['receipt_help'] = 'Receive a copy of the email being sent';
$string['prepend_class'] = 'Prepend Course name';
$string['prepend_class_desc'] = 'Prepend the course shortname to the subject of the email.';
$string['ferpa'] = 'FERPA Mode';
$string['ferpa_desc'] = 'Allows the system to behave either according to the course groupmode setting, ignoring the groupmode setting but separating groups, or ignoring groups altogether.';
$string['strictferpa'] = 'Always Separate Groups';
$string['courseferpa'] = 'Respect Course Mode';
$string['noferpa'] = 'No Group Respect';
$string['downloads'] = 'Require login for attachments';
$string['downloads_desc'] = 'This setting determines if attachments are available only to logged in Moodle users';
$string['addionalemail'] = 'Allow emails to external email addresses';
$string['addionalemail_desc'] = 'If this option is enabled quickmail emails are also sent so external email adresses the user entered within the form.';
$string['output_channel'] = 'Send Quickmail messages as';
$string['output_channel_desc'] = 'Allows Quickmail messages to be sent as a Moodle Message, or just as email.';
$string['output_as_message'] = 'Moodle Message';
$string['output_as_email'] = 'Email Only';
$string['save_configuration'] = 'Save Settings';
$string['reset'] = 'Restore System Defaults';
$string['reset_success_message'] = 'Quickmail default settings have been restored!';

$string['no_permission'] = 'You do not have permission to send emails with Quickmail.';
$string['no_course'] = 'Invalid Course with id of {$a}';
$string['critical_error'] = 'Critical error';
$string['validation_error'] = 'Validation error';
$string['authorization_error'] = 'Authorization error';
$string['some_additional_emails_invalid'] = 'Some of the additional emails you are sending to are invalid, please fix and then resend.';

$string['messageprovider:quickmail_email'] = 'A Quickmail email';
$string['messageprovider:quickmail_message'] = 'A Quickmail message';

// block rendering
$string['compose'] = 'Compose Message';
$string['composenew'] = 'Compose New Email';
$string['manage_signatures'] = 'My Signatures';
$string['signature'] = 'Signature';
$string['signatures'] = 'Signatures';
$string['drafts'] = 'View Drafts';
$string['history'] = 'View History';
$string['alternate'] = 'Alternate Emails';
$string['config'] = 'Configuration';
$string['sendadmin'] = 'Admin Email';


$string['allusers'] = ' All Users';
$string['quickmail:cansend'] = "Allows users to send email through Quickmail";
$string['quickmail:canconfig'] = "Allows users to configure Quickmail instance.";
$string['quickmail:canimpersonate'] = "Allows users to log in as other users and view history.";
$string['quickmail:allowalternate'] = "Allows users to add an alternate email for courses.";
$string['quickmail:addinstance'] = "Add a new Quickmail block to a course page";
$string['quickmail:myaddinstance'] = "Add a new Quickmail block to the /my page";
$string['quickmail:candelete'] = "Allows users to delete email from history.";
$string['backup_history'] = 'Include Quickmail History';
$string['backup_block_configuration'] = 'Backup Quickmail Block Level Configuration Settings (Such as [Allow Students to use Quickmail])';
$string['restore_history'] = 'Restore Quickmail History';
$string['overwrite_history'] = 'Overwrite Quickmail History';
$string['eventalternateemailadded'] = 'Alternate email added';
$string['email'] = 'Email';

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
$string['potential_sections'] = 'Potential Groups';
$string['no_section'] = 'Not in a group';
$string['all_sections'] = 'All Groups';
$string['attachment'] = 'Attachment(s)';
$string['message'] = 'Message';
$string['actions'] = 'Actions';
$string['delete_confirm'] = 'Are you sure you want to delete message with the following details: {$a}';
$string['title'] = 'Title';
$string['default_flag'] = 'Default';


$string['download_auth_only'] = 'Authorized Users Only';
$string['download_open'] = 'Open Downloads';


$string['no_alternates'] = 'No alternate emails found for {$a->fullname}. Continue to make one.';

$string['select_users'] = 'Select Users ...';
$string['select_groups'] = 'Select Sections ...';

$string['moodle_attachments'] = 'Moodle Attachments ({$a})';
$string['download_all'] = 'Download All';
$string['qm_contents'] = 'Download File Contents';


$string['no_type'] = '{$a} is not in the acceptable type viewer. Please use the applciation correctly.';
$string['no_email'] = 'Could not email {$a->firstname} {$a->lastname}.';
$string['no_email_address'] = 'Could not email {$a}';
$string['no_log'] = 'You have no email history yet.';
$string['no_drafts'] = 'You have no email drafts.';
$string['no_subject'] = 'You must have a subject';
$string['no_usergroups'] = 'There are no users in your group capable of being emailed.';
$string['no_users'] = 'There are no users you are capable of emailing.';
$string['no_selected'] = 'You must select some users for emailing.';
$string['not_valid'] = 'This is not a valid email log viewer type: {$a}';
$string['not_valid_user'] = 'You can not view other email history.';
$string['not_valid_action'] = 'You must provide a valid action: {$a}';
$string['not_valid_typeid'] = 'You must provide a valid email for {$a}';
$string['delete_failed'] = 'Failed to delete email';
$string['required'] = 'Please fill in the required fields.';
$string['courselayout'] = 'Course Layout';
$string['courselayout_desc'] = 'Use _Course_ page layout  when rendering the Quickmail block pages. Enable this setting, if you are getting Moodle form fixed width issues.';

$string['are_you_sure'] = 'Are you sure you want to delete {$a->title}? This action
cannot be reversed.';

// Alternate Email strings
$string['alternate_new'] = 'Add Alternate Address';
$string['alternate_availability'] = 'Availability of this alternate address';
$string['alternate_availability_only'] = 'Available only to me for this course only';
$string['alternate_availability_user'] = 'Available only to me';
$string['alternate_availability_course'] = 'Available to all course senders';
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


// Strings for Error Reporting
$string['sent_success'] = 'all messages sent successfully';
$string['logsuccess'] = 'all messages sent successfully';
$string['message_failure'] = 'some users did not get message';
$string['send_again'] = 'send again';
$string['status'] = 'status';
$string['failed_to_send_to'] = 'failed to send to';
$string['users'] = 'users';
$string['user'] = 'user';

$string['draftssuccess'] = "Draft";

//admin
$string['email_error'] = 'Could not email: {$a->firstname} {$a->lastname} ({$a->email})';
$string['email_error_field'] = 'Can not have an empty: {$a}';
$string['messageprovider:broadcast'] = 'Send broadcast messages using Admin Email.';

$string['message_sent_to'] = 'Message sent to ';
$string['warnings'] = 'Warnings';
$string['message_body_as_follows'] = 'message body as follows ';
$string['sent_successfully_to_the_following_users'] = 'sent successfully to the following users: ' ;
$string['seconds'] = 'seconds';
$string['admin_email_send_receipt'] = 'Admin Email Send Receipt';
$string['something_broke'] = 'It looks like you either have email sending disabled or things are very broken';
$string['time_elapsed'] = 'Time Elapsed: ';

$string['redirect_back_to_course_from_message'] = 'Your message has been cancelled and you are now being redirected back to your course, {$a}';
$string['cancel_and_redirect_to_course'] = 'Any changes have been cancelled and you are now being redirected back to your course, {$a}';
$string['redirect_back_to_dashboard_from_signature'] = 'Any changes have been cancelled and you are now being redirected back to your dashboard.';

$string['back_to_course'] = 'Back to course';
$string['manage_signatures'] = 'Manage Signatures';
