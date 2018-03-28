<?php

// block
$string['pluginname'] = 'Quickmail';
$string['open_compose'] = 'Compose Message';
$string['manage_drafts'] = 'View Drafts';
$string['view_queued'] = 'View Scheduled';
$string['view_sent'] = 'View Sent Messages';
$string['manage_signatures'] = 'My Signatures';
$string['manage_alternates'] = 'Alternate Emails';
$string['messageprovider:quickmessage'] = 'Quickmail message';

// permissions
$string['quickmail:cansend'] = 'Allows users to send email through Quickmail';
$string['quickmail:canconfig'] = 'Allows users to configure Quickmail instance.';
$string['quickmail:allowalternate'] = 'Allows users to add an alternate email for courses.';
$string['quickmail:addinstance'] = 'Add a new Quickmail block to a course page';
$string['quickmail:myaddinstance'] = 'Add a new Quickmail block to the /my page';
$string['quickmail:viewgroupusers'] = 'View all users in every group';

// general terms
$string['duplicate'] = 'Duplicate';
$string['open'] = 'Open';
$string['create_new'] = 'Create New';
$string['actions'] = 'Actions';
$string['title'] = 'Title';
$string['status'] = 'Status';
$string['back_to_course'] = 'Back to course';

// messaging terms
$string['compose'] = 'Compose Message';
$string['subject'] = 'Subject';
$string['body'] = 'Body';
$string['send_at'] = 'Send at';
$string['send_message'] = 'Send Message';
$string['additional_emails'] = 'Additional Recipient Emails';
$string['queued'] = 'Scheduled';
$string['unqueue'] = 'Unqueue';
$string['no_queued'] = 'Could not find that queued message.';
$string['queued_no_record'] = 'Could not find that queued message.';
$string['receipt'] = 'Receive a send report';
$string['select_message_type'] = 'Send message as';
$string['message_type_message'] = 'Moodle Message';
$string['message_type_email'] = 'Email';
$string['attached_files'] = 'Attached Files ({$a})';
$string['download_file_content'] = 'Download File Content';

// history
$string['no_sents'] = 'You have no sent message history.';
$string['sent_messages'] = 'Sent Message History';

// drafts
$string['drafts'] = 'Drafts';
$string['no_drafts'] = 'You have no message drafts.';
$string['save_draft'] = 'Save Draft';
$string['draft_no_record'] = 'Could not find that draft message.';

// alternates
$string['alternate'] = 'Alternate Email';
$string['no_alternates'] = 'You have no alternate emails. Create a new one now!';
$string['alternate_new'] = 'Add Alternate Address';
$string['alternate_availability'] = 'Who can send from this email?';
$string['alternate_availability_only'] = 'Only to me for this course only';
$string['alternate_availability_user'] = 'Only to me';
$string['alternate_availability_course'] = 'All course senders';
$string['alternate_resend_confirmation'] = 'Re-send confirm email';
$string['alternate_created'] = 'Alternate sending email successfully created!';
$string['alternate_deleted'] = 'Your alternate sending email has been deleted.';
$string['alternate_confirmed'] = 'Confirmed';
$string['alternate_waiting'] = 'Waiting';
$string['alternate_activated'] = 'Alternate email {$a} can now be used!';
$string['alternate_confirmation_email_resent'] = 'The confirmation email has been resent!';
$string['eventalternateemailadded'] = 'Alternate email added';
$string['alternate_subject'] = 'Alternate email address verification';
$string['alternate_body'] = '
<p>
{$a->fullname} added {$a->email} as an alternate sending address for {$a->plugin_name}.
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

// signatures
$string['signature'] = 'Signature';
$string['signatures'] = 'Signatures';
$string['signature_title_required'] = 'A signature title is required.';
$string['signature_title_must_be_unique'] = 'The signature title must be unique.';
$string['signature_signature_required'] = 'A signature is required.';
$string['select_signature_for_edit'] ='Select Signature To Edit';
$string['save_signature'] = 'Save Signature';
$string['delete_signature'] = 'Delete Signature';
$string['user_signature_deleted'] = 'Your signature has been deleted.';
$string['no_signatures_create'] = 'You have no signatures. {$a}.';

// help buttons
$string['additional_emails_help'] = 'Other email addresses you would like the message sent to, in a comma or semicolon separated list. Example:

 email1@example.com, email2@example.com
 ';
$string['receipt_help'] = 'Receive an emailed report with the details of this message send';
$string['from_email_help'] = 'The email address that this message will be sent from. You may add additional alternate addresses through the block menu on the course page.';

// settings management
$string['reset_success_message'] = 'Quickmail default settings have been restored!';

// configuration
$string['allowstudents'] = 'Allow students to use Quickmail';
$string['allowstudents_desc'] = 'Allow students to use Quickmail. If you choose "Never", the block cannot be configured to allow students access at the course level.';
$string['selectable_roles'] = 'Selectable roles';
$string['selectable_roles_desc'] = 'These roles will be available for selection when composing a message.';
$string['prepend_class'] = 'Prepend Course name';
$string['prepend_class_desc'] = 'Prepend the course shortname to the subject of the email.';
$string['ferpa'] = 'FERPA Mode';
$string['ferpa_desc'] = 'Allows the system to behave either according to the course groupmode setting, ignoring the groupmode setting but separating groups, or ignoring groups altogether.';
$string['strictferpa'] = 'Always Separate Groups';
$string['courseferpa'] = 'Respect Course Mode';
$string['noferpa'] = 'No Group Respect';
$string['downloads'] = 'Require login for attachments';
$string['downloads_desc'] = 'This setting determines if attachments are available only to logged in Moodle users';
$string['additionalemail'] = 'Allow emails to external email addresses';
$string['additionalemail_desc'] = 'If this option is enabled, the sender will have the ability to send messages to additional emails outside of Moodle';
$string['message_type'] = 'Send Quickmail messages as';
$string['message_type_desc'] = 'Allows Quickmail messages to be sent as a Moodle message, traditional email, or sender preference.';
$string['default_message_type'] = 'Preferred message sending method';
$string['default_message_type_desc'] = 'Send your messages as Moodle Messages or traditional email.';
$string['message_types_available'] = 'Message message type restrictions';
$string['message_types_available_desc'] = 'Restrict Quickmail messages to be sent as Moodle Messages, traditional emails, or sender preference.';
$string['message_type_available_all'] = 'No restrictions, sender preference';
$string['message_type_available_message'] = 'Restrict to Moodle messages only';
$string['message_type_available_email'] = 'Restrict to traditional email only';
$string['select_allowed_user_fields'] = 'Supported user data fields';
$string['select_allowed_user_fields_desc'] = 'Senders will be able to reference the selected fields to make email content dynamic and specific to the recipient. Ex: "[:firstname:]"';

// redirect messages
$string['redirect_back_to_course_from_message_after_cancel'] = 'Your message has been cancelled and you are now being redirected back to your course, {$a}';
$string['redirect_back_to_course_from_message_after_send'] = 'Your message has been successfully sent.';
$string['redirect_back_to_course_from_message_after_save'] = 'Your draft has been saved.';
$string['cancel_and_redirect_to_course'] = 'Any changes have been cancelled and you are now being redirected back to your course, {$a}';

// errors
$string['critical_error'] = 'Critical error';

// caches
$string['cachedef_qm_msg_recip_count'] = 'Cached message recipient counts.';
$string['cachedef_qm_msg_deliv_count'] = 'Cached message delievered counts.';
$string['cachedef_qm_msg_attach_count'] = 'Cached message attachment counts.';
$string['cachedef_qm_msg_addl_email_count'] = 'Cached message additional email counts.';

// backup/restore
$string['backup_history'] = 'Include Quickmail History';
$string['backup_block_configuration'] = 'Backup Quickmail Block Level Configuration Settings (Such as [Allow Students to use Quickmail])';
$string['restore_history'] = 'Restore Quickmail History';
$string['overwrite_history'] = 'Overwrite Quickmail History';