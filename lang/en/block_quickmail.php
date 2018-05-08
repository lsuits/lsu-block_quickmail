<?php

// block
$string['pluginname'] = 'Quickmail';
$string['open_broadcast'] = 'Compose Message';
$string['open_compose'] = 'Compose Message';
$string['manage_drafts'] = 'View Drafts';
$string['view_queued'] = 'View Scheduled';
$string['view_sent'] = 'View Sent Messages';
$string['manage_signatures'] = 'My Signatures';
$string['manage_alternates'] = 'Alternate Emails';
$string['messageprovider:quickmessage'] = 'Quickmail message';

// capabilities
$string['quickmail:cansend'] = 'Send Quickmail messages in a course';
$string['quickmail:canconfig'] = 'Configure Quickmail settings in a course';
$string['quickmail:allowalternate'] = 'Create alternate Quickmail email addresses in a course';
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
$string['here'] = 'here';
$string['back_to_course'] = 'Back to course';
$string['back_to_mypage'] = 'Back to My page';

// message status
$string['drafted'] = 'Drafted';
$string['queued'] = 'Scheduled';
$string['sending'] = 'Sending';
$string['sent'] = 'Sent';
$string['deleted'] = 'Deleted';

// messaging terms
$string['included_ids_label'] = 'To';
$string['excluded_ids_label'] = 'Exclude';
$string['compose'] = 'Compose Course Message';
$string['broadcast'] = 'Compose Admin Message';
$string['subject'] = 'Subject';
$string['message_preview'] = 'Message Preview';
$string['preview_no_subject'] = '(No subject)';
$string['body'] = 'Body';
$string['preview_no_body'] = '(No content)';
$string['send_at'] = 'Send at';
$string['send_now'] = 'Send Now';
$string['send_message'] = 'Send Message';
$string['additional_emails'] = 'Additional Recipient Emails';
$string['unqueue'] = 'Unqueue';
$string['no_queued'] = 'You have no scheduled messages.';
$string['message_no_record'] = 'Could not find that message.';
$string['queued_no_record'] = 'Could not find that queued message.';
$string['receipt'] = 'Receive a send report';
$string['mentor_copy'] = 'Send copies to mentors of recipients?';
$string['select_message_type'] = 'Send message as';
$string['message_type_message'] = 'Moodle Message';
$string['message_type_email'] = 'Email';
$string['attached_files'] = 'Attached Files ({$a})';
$string['download_file_content'] = 'Download File Content';
$string['included_recipients_desc'] = 'Who should receive this message?';
$string['no_included_recipients'] = 'No included recipients';
$string['excluded_recipients_desc'] = 'Who should NOT receive this message?';
$string['no_excluded_recipients'] = 'No excluded recipients';
$string['created'] = 'Created';
$string['last_updated'] = 'Last Updated';
$string['scheduled_time'] = 'Scheduled Time';
$string['sent_at'] = 'Sent At';
$string['attachments'] = 'Attachments';
$string['recipients'] = 'Recipients';
$string['unqueue_scheduled_modal_title'] = 'Unqueue Scheduled Message';
$string['unqueue_scheduled_confirm_message'] = 'This will unschedule this message to be sent and save the message as a draft, are you sure?';
$string['send_now_scheduled_modal_title'] = 'Send Message Now';
$string['send_now_scheduled_confirm_message'] = 'This will forget the schedule and send the message now, are you sure?';
$string['send_receipt_subject_addendage'] = 'Sent Message';
$string['found_filtered_users'] = 'Found {$a} user(s)';

// history
$string['no_sents'] = 'You have no sent message history.';
$string['sent_messages'] = 'Sent Message History';

// drafts
$string['drafts'] = 'Drafts';
$string['no_drafts'] = 'You have no message drafts.';
$string['save_draft'] = 'Save Draft';
$string['draft_no_record'] = 'Could not find that draft message.';
$string['could_not_duplicate'] = 'Could not duplicate this draft. Please try again.';
$string['must_be_draft_to_duplicate'] = 'Message must be a draft to duplicate.';
$string['must_be_owner_to_duplicate'] = 'Sorry, that draft does not belong to you and cannot be duplicated.';
$string['delete_draft_modal_title'] = 'Delete Message Draft';
$string['delete_draft_confirm_message'] = 'This will permanently delete your draft message, are you sure?';
$string['duplicate_draft_modal_title'] = 'Duplicate Message Draft';
$string['duplicate_draft_confirm_message'] = 'This will make a copy of the draft, are you sure?';

// alternates
$string['alternate'] = 'Alternate Email';
$string['no_alternates'] = 'You have no alternate emails. Create a new one now!';
$string['alternate_new'] = 'Add Alternate Address';
$string['alternate_delete'] = 'Delete Alternate Address';
$string['alternate_availability'] = 'Who can send from this email?';
$string['alternate_availability_only'] = 'Only me for this course only';
$string['alternate_availability_user'] = 'Only me';
$string['alternate_availability_course'] = 'All course senders';
$string['alternate_resend_confirmation'] = 'Re-send confirm email';
$string['alternate_created'] = 'Alternate sending email successfully created!';
$string['alternate_delete_confirm'] = 'This will permanently delete your alternate email, are you sure?';
$string['alternate_deleted'] = 'Your alternate sending email has been deleted.';
$string['alternate_confirmed'] = 'Confirmed';
$string['alternate_email_not_found'] = 'Could not find that alternate email.';
$string['alternate_owner_must_confirm'] = 'Must be the owner of the email to confirm.';
$string['alternate_owner_must_delete'] = 'Must be the owner of the email to delete.';
$string['alternate_already_confirmed'] = 'That email has already been confirmed.';
$string['alternate_invalid_token'] = 'Invalid token.';
$string['alternate_waiting'] = 'Waiting';
$string['alternate_activated'] = 'Alternate email {$a} can now be used!';
$string['alternate_confirmation_email_resent'] = 'The confirmation email has been resent!';
$string['eventalternateemailadded'] = 'Alternate email added';
$string['eventalternateemailadded_desc'] = 'The user with id {$a->user_id} has added an alternate email: {$a->email}';
$string['alternate_subject'] = 'Alternate email address verification';

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
$string['delete_signature_modal_title'] = 'Delete Signature';
$string['delete_signature_confirm_message'] = 'This will permanently delete your signature, are you sure?';

// help buttons
$string['additional_emails'] = 'Additional emails';
$string['additional_emails_help'] = 'Other email addresses you would like the message sent to, in a comma or semicolon separated list. Example:

 email1@example.com, email2@example.com
 ';
$string['receipt_help'] = 'Receive an emailed report with the details of this message send';
$string['mentor_copy_help'] = 'If selected, any mentors of your recipients will receive a copy of the message.';
$string['from_email'] = 'Sender email address';
$string['from_email_help'] = 'The email address that this message will be sent from. You may add additional alternate addresses through the block menu on the course page.';
$string['allow_mentor_copy'] = 'Allow senders to automatically message mentors of recipients when sending';
$string['allow_mentor_copy_help'] = 'If enabled, the sender will have the ability to select whether or not mentors should be copied to any outbound message. This message will only happen if the recipient user has a mentor, otherwise, they will receive the message individually as per normal.';

// settings management
$string['restore_default_modal_title'] = 'Restore Default Configuration';
$string['restore_default_confirm_message'] = 'This will restore this course\'s Quickmail settings to default, are you sure?';
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
$string['redirect_back_to_course_from_message_after_queued_send'] = 'Your message is now scheduled to be sent.';
$string['redirect_back_to_course_from_message_after_send'] = 'Your message will be sent shortly.';
$string['redirect_back_to_course_from_message_after_duplicate'] = 'Your message has been successfully duplicated.';
$string['redirect_back_to_course_from_message_after_save'] = 'Your draft has been saved.';

// validation
$string['missing_subject'] = 'Missing subject line.';
$string['missing_body'] = 'Missing message body.';
$string['missing_email'] = 'Missing email address.';
$string['invalid_email'] = 'Invalid email address.';
$string['missing_firstname'] = 'Missing first name.';
$string['missing_lastname'] = 'Missing last name.';
$string['invalid_availability'] = 'Invalid availability value.';
$string['no_included_recipients_validation'] = 'You must select at least one recipient.';
$string['invalid_additional_emails_validation'] = 'Some of the additional emails you entered were invalid.';
$string['invalid_custom_data_key'] = 'Custom data key "{$a}" is not allowed.';
$string['invalid_custom_data_delimiters'] = 'Custom data delimiters not formatted properly.';
$string['invalid_additional_email'] = 'The additional email "{$a}" you entered is invalid';
$string['invalid_send_method'] = 'That send method is not allowed.';

// errors
$string['critical_error'] = 'Critical error';
$string['validation_exception_message'] = 'Validation exception!';
$string['course_required'] = 'A course is required.';

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

// email templates
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

$string['receipt_email_body'] = '
<p>This message is to inform you that your message was sent.</p>

<p>
<strong>Message details summary:</strong><br>
<br>Course: {$a->course_name}
<br>Message Subject: {$a->subject}
<br>Recipients: {$a->recipient_count}
<br>Additional Recipient Emails: {$a->additional_email_count}
<br>File Attachment Count: {$a->attachment_count}
</p>

<p>Note: This message does not guarantee that all messages were received by the potential recipients.</p>

<p>You can view further details of this sent message {$a->sent_message_link}.</p>
';