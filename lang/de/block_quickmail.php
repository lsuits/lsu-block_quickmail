<?php

$string['pluginname'] = 'Quickmail';
$string['quickmail:cansend'] = "Erlaubt Benutzern das Senden von Emails durch Quickmail";
$string['quickmail:canconfig'] = "Erlaubt Benutzern das Konfigurieren der Quickmail Instanz.";
$string['quickmail:canimpersonate'] = "Erlaubt Benutzern das Anmelden als anderer Benutzer und Einsehen des Verlaufs.";
$string['quickmail:allowalternate'] = "Erlaubt den Benutzern eine alternative Email-Adresse in diesem Kurs zu hinterlegen.";
$string['backup_history'] = 'Einschließen des Quickmail Verlaufs';
$string['restore_history'] = 'Wiederherstellen des Quickmail Verlaufs';
$string['overwrite_history'] = 'Überschreibe den Quickmail Verlauf';
$string['alternate'] = 'Alternative Email';
$string['composenew'] = 'Erstelle neue Email';
$string['email'] = 'Email';
$string['drafts'] = 'Anzeigen des Entwurfs';
$string['history'] = 'Anzeigen des Verlaufs';
$string['log'] = $string['history'];
$string['from'] = 'Von';
$string['selected'] = 'Ausgewählte Empfänger';
$string['add_button'] = 'Hinzufügen';
$string['remove_button'] = 'Entfernen';
$string['add_all'] = 'Alle hinzufügen';
$string['remove_all'] = 'Alle entfernen';
$string['role_filter'] = 'Rollen filtern';
$string['no_filter'] = 'kein Filter';
$string['potential_users'] = 'Mögliche Empfänger';
$string['potential_sections'] = 'Mögliche Abschnitte';
$string['no_section'] = 'Nicht in einem Abschnitt';
$string['all_sections'] = 'Alle Abschnitte';
$string['attachment'] = 'Anhang';
$string['subject'] = 'Betreff';
$string['message'] = 'Nachricht';
$string['send_email'] = 'Sende Email';
$string['save_draft'] = 'Speicher Entwurf';
$string['actions'] = 'Aktionen';
$string['signature'] = 'Signatures';
$string['delete_confirm'] = 'Wollen Sie wirklich die Nachricht {$a} löschen?';
$string['title'] = 'Titel';
$string['sig'] ='Signatur';
$string['default_flag'] = 'Grundeinstellung';
$string['config'] = 'Konfiguration';
$string['receipt'] = 'Kopie an mich';
$string['receipt_help'] = 'Kopie der versendeten Email an mich';

$string['no_alternates'] = 'Keine alternativen Emails für {$a->fullname} gefunden. Im nächsten Schritt erstellen Sie eine.';

$string['select_users'] = 'Auswählen der Benutzer ...';
$string['select_groups'] = 'Auswählen der Abschnitte ...';

// Config form strings
$string['allowstudents'] = 'Erlaube den Studenten Quickmail zu verwenden';
$string['select_roles'] = 'Rollen nach denen gefiltert werden soll';
$string['reset'] = 'Wiederherstellen der Grundeinstellungen des Systems';

$string['no_type'] = '{$a} ist nicht anzeigbar. Bitte verwenden Sie die Anwendung bestimmungsgemäß.';
$string['no_email'] = 'Konnte das Email nicht an {$a->firstname} {$a->lastname} senden.';
$string['no_log'] = 'Sie haben noch keinen Email-Verlauf.';
$string['no_drafts'] = 'Sie haben keine Entwürfe.';
$string['no_subject'] = 'Sie müssen einen Betreff angeben';
$string['no_course'] = 'Unzulässige Kurs-ID {$a}';
$string['no_permission'] = 'Sie haben keine Berechtigung mit Quickmail Emails zu versenden.';
$string['no_users'] = 'Es gibt keine Benutzer, die Emails empfangen könnten.';
$string['no_selected'] = 'Sie müssen einige Benutzer auswählen.';
$string['not_valid'] = 'Dies ist kein gültiger Typ um ein Email Log anzuzeigen: {$a}';
$string['not_valid_user'] = 'Sie können den Verlauf eines anderen benutzers nicht einsehen.';
$string['not_valid_action'] = 'Sie müssen eine gültige Aktion auswählen: {$a}';
$string['not_valid_typeid'] = 'Sie müssen eine gültige Email angeben: {$a}';
$string['delete_failed'] = 'Löschen der Email nicht möglich';
$string['required'] = 'Bitte fülen Sie alle benötigten Felder aus.';
$string['prepend_class'] = 'Stellen Sie die Kursnamen voran';
$string['prepend_class_desc'] = 'Stellen Sie den Kurskurznamen vor den Betreff der Email.';
$string['courselayout'] = 'Kurs Layout';
$string['courselayout_desc'] = 'Verwenden Sie die Kurs_Layout_Seite wenn Quickmail Block Seiten angezeigt werden sollen. Aktivieren Sie diese Einstellung, wenn sie Probleme mit festen Breiten haben.';

$string['are_you_sure'] = 'Sind Sie sicher, dass Sie {$a->title} löschen wollen? Dieser Vorgang kann nicht widerrufen werden!';

// Alternate Email strings
$string['alternate_new'] = 'Alternative Adresse hinzufügen';
$string['sure'] = 'Sind Sie sicher, dass Sie {$a->address} löschen wollen? Dieser Vorgang kann nicht widerrufen werden!';
$string['valid'] = 'Aktivierungsstatus';
$string['approved'] = 'Angenommen';
$string['waiting'] = 'Wartend';
$string['entry_activated'] = 'Die alternative Email {$a->address} kann nun im Kurs {$a->course} verwendet werden.';
$string['entry_key_not_valid'] = 'Aktivierungslink ist nicht mehr gültig für {$a->address}. Im nächsten Schritt wird der Aktivierungslink erneut gesendet.';
$string['entry_saved'] = 'Die alternative Adresse {$a->address} wurde gespeichert.';
$string['entry_success'] = 'Zur Überprüfung, dass die Email gültig ist, wurde ein Bestätigungseimal an {$a->address} gesendet. Instruktionen zur weiteren Vorgangsweise finden Sie im Email.';
$string['entry_failure'] = 'Das Email an {$a->address} nicht versandt werden. Bitte überprüfen Sie dass die Adresse {$a->address} existiert, und versuchen Sie es dann erneut.';
$string['alternate_from'] = 'Moodle: Quickmail';
$string['alternate_subject'] = 'Überprüfung der alternativen Email Adresse';
$string['alternate_body'] = '

{$a->fullname} hat die Adresse {$a->address} als alternative Adresse für den Kurs {$a->course} hinzugefügt.

Der Zweck dieser Email ist die Überprüfung, dass diese Adresse tatsächlich existiert und ihr Besitzer die erforderlichen Berechtigungen hierfür im Moodle hat.

Wenn Sie den Prozess der Überprüfung abschließen möchten, so rufen Sie bitte den folgenden Link in Ihrem Browser auf: {$a->url}

Sollte die Beschreibung in dieser Email für Sie keinen Sinn ergeben, so haben Sie diese Email versehentlich bekommen. Dann können Sie diese Nachricht getrost ignorieren.

Herzlichen Dank.
';
