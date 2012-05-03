<?php

$string['pluginname'] = 'Messagerie';
$string['quickmail:cansend'] = 'Permet aux utilisateurs d\'envoyer des courriels par le biais de Messagerie';
$string['quickmail:canconfig'] = 'Permet aux utilisateurs de configurer l\'instance de Messagerie .';
$string['quickmail:canimpersonate'] = 'Permet aux utilisateurs de se connecter en tant qu\'un autre utilisateur et de voir l\'historique.';
$string['quickmail:allowalternate'] = "Autoriser les utilisateurs à ajouter une adresse secondaire pour les cours.";
$string['alternate'] = 'Adresses secondaires';
$string['composenew'] = 'Ecrire un nouveau message'; 
$string['email'] = 'Message';
$string['drafts'] = 'Voir les brouillons';
$string['history'] = 'Voir l\'historique';
$string['log'] = $string['history']; 
$string['from'] = 'De';
$string['selected'] = 'Destinataires sélectionnés';
$string['add_button'] = 'Ajouter';
$string['remove_button'] = 'Supprimer';
$string['add_all'] = 'Ajouter tous';
$string['remove_all'] = 'Supprimer tous';
$string['role_filter'] = 'Filtrer par rôle';
$string['no_filter'] = 'Pas de filtre';
$string['potential_users'] = 'Destinataires possibles';
$string['potential_sections'] = 'Groupes possibles';
$string['no_section'] = 'Dans aucun groupe';
$string['all_sections'] = 'Dans tous les groupes';
$string['attachment'] = 'Pièce(s) jointe(s)';
$string['subject'] = 'Objet';
$string['message'] = 'Message';
$string['send_email'] = 'Envoyer le message';
$string['save_draft'] = 'Enregistrer le brouillon';
$string['actions'] = 'Actions';
$string['signature'] = 'Signatures';
$string['delete_confirm'] = 'Etes-vous sûr de vouloir supprimer le message avec les détails suivants : {$a}';
$string['title'] = 'Titre';
$string['sig'] ='Signature';
$string['default_flag'] = 'Par défaut';
$string['config'] = 'Configuration';
$string['receipt'] = 'Recevoir une copie';
$string['receipt_help'] = 'Recevoir une copie du message envoyé';

$string['no_alternates'] = 'Pas d\'adresse secondaire de trouvée pour {$a->fullname}. Poursuivre la création.';

$string['select_users'] = 'Sélectionner les utilisateurs...';
$string['select_groups'] = 'Sélectionner les groupes...';

// Config form strings
$string['allowstudents'] = 'Autoriser les étudiants à utiliser Messagerie';
$string['select_roles'] = 'Rôles à filtrer';
$string['reset'] = 'Restaurer les paramètres par défaut';

$string['no_type'] = '{$a} n\'est pas dans une vue acceptable. Merci d\'utiliser une application adaptée.';
$string['no_email'] = 'Pas d\'envoi possible à {$a->firstname} {$a->lastname}.';
$string['no_log'] = 'Vous n\'avez pas d\'historique.'; 
$string['no_drafts'] = 'Vous n\'avez pas de brouillon.'; 
$string['no_subject'] = 'Vous devez mettre un objet à votre message';
$string['no_course'] = 'Id du cours {$a} invalide';
$string['no_permission'] = 'Vous n\'êtes pas autorisé à envoyer des messages avec Messsagerie.';
$string['no_users'] = 'Il n\'y a pas d\'utilisateur capable d\'envoyer des messages.';
$string['no_selected'] = 'Vous devez sélectionner au moins un utilisateur pour envoyer un message.';
$string['not_valid'] = 'Ce n\'est pas un type de message valide : {$a}';
$string['not_valid_user'] = 'Vous ne pouvez pas afficher d\'autre historique.';
$string['not_valid_action'] = 'Vous devez faire une action valide : {$a}';
$string['not_valid_typeid'] = 'Vous devez fournir une adresse valide pour {$a}';
$string['delete_failed'] = 'Impossible de supprimer le message';
$string['required'] = 'Merci de remplir les champs obligatoires.';
$string['prepend_class'] = 'Nom du cours dans l\'objet';
$string['prepend_class_desc'] = 'Ajoute l\'identification du cours dans l\'objet du message.';
$string['courselayout'] = 'Mise en forme dans le cours';
$string['courselayout_desc'] = 'Utiliser le mode édition pour le positionnement du bloc Messagerie dans les pages du cours. Activez ce paramètre si vous utilisez un style Moodle à largeur fixe.';

$string['are_you_sure'] = 'Etes-vous sûr de supprimer {$a->title} ? Cette action est irreversible !';

// Alternate Email strings
$string['alternate_new'] = 'Ajouter des adresses secondaires';
$string['sure'] = 'Etes-vous sûr de supprimer {$a->address}? Cette action ne peut être annulée.';
$string['valid'] = 'Statut d\'activation';
$string['approved'] = 'Validé';
$string['waiting'] = 'En attente';
$string['entry_activated'] = 'L\'adresse secondaire {$a->address} peut être désormais utilisée dans le cours {$a->course}.';
$string['entry_key_not_valid'] = 'Le lien d\'activation pour {$a->address} n\'est plus valide. Renvoyer un nouveau lien d\'activation.';
$string['entry_saved'] = 'L\'adresse secondaire {$a->address} a été enregistrée.';
$string['entry_success'] = 'Un message pour vérifier la validité de l\'adresse a été envoyé à {$a->address}. Merci de lire les instructions présentes dans ce message pour l\'activation.';
$string['entry_failure'] = 'Un message ne peut être envoyé à {$a->address}. Merci de vérifier que l\'adresse {$a->address} existe, et essayez à nouveau.';
$string['alternate_from'] = 'Moodle : Messagerie';
$string['alternate_subject'] = 'Vérification de l\'adresse secondaire';
$string['alternate_body'] = '
<p>
{$a->fullname} a ajouté {$a->address} comme adresse secondaire pour le cours {$a->course}.
</p>

<p>
Ce message a pour but de vérifier la validité de cette adresse, et si le destinataire à les droits nécessaires sur la plateforme.
</p>

<p>
Si vous souhaitez terminer le processus de validation, merci de cliquer sur le lien suivant :<br> {$a->url}.
</p>

<p>
Si le contenu de ce message n\'a aucun sens pour vous, c\'est qu\'il a été envoyé par erreur. Merci d\'ignorer simplement ce message.
</p>

Cordialement.
';
