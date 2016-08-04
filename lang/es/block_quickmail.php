<?php

$string['pluginname'] = 'Quickmail';
$string['quickmail:cansend'] = "Permitir a los usuarios a usar Quickmail";
$string['quickmail:canconfig'] = "Permitir a los usuarios configurar Quickmail.";
$string['quickmail:canimpersonate'] = "Permitir a los usuarios a registrarse como otro usuario y ver el historial.";
$string['quickmail:allowalternate'] = "Permitir a los usuarios a añadir un email alternativo.";
$string['quickmail:addinstance'] = "Añadir un nuevo bloque Quickmail";
$string['quickmail:candelete'] = "Permitir a los usuario a borrar el historial";
$string['backup_history'] = 'Incluir Historial de Quickmail';
$string['restore_history'] = 'Eliminar Historial de Quickmail';
$string['overwrite_history'] = 'Reemplazar Historial de Quickmail';
$string['alternate'] = 'Emails Alternativos';
$string['composenew'] = 'Crear Email';
$string['email'] = 'Email';
$string['drafts'] = 'Ver Borradores';
$string['history'] = 'Ver Historial';
$string['log'] = 'Ver  Historial';
$string['from'] = 'De';
$string['selected'] = 'Seleccionar Receptores';
$string['add_button'] = 'Añadir';
$string['remove_button'] = 'Eliminar';
$string['add_all'] = 'Añadir Todos';
$string['remove_all'] = 'Eliminar Todos';
$string['role_filter'] = 'Role';
$string['no_filter'] = 'Sin filtros';
$string['potential_users'] = 'Receptores Potenciales';
$string['potential_sections'] = 'Secciones Potenciales';
$string['no_section'] = 'No esta en una seccion';
$string['all_sections'] = 'Todas las Secciones';
$string['attachment'] = 'Adjunto(s)';
$string['subject'] = 'Asunto';
$string['message'] = 'Mensaje';
$string['send_email'] = 'Ver Email';
$string['save_draft'] = 'Guardar Borrador';
$string['actions'] = 'Acciones';
$string['signature'] = 'Firmas';
$string['delete_confirm'] = 'Seguro que quieres eliminar el mensaje con los siguientes detalles?: {$a}';
$string['title'] = 'Titulo';
$string['sig'] ='Firma';
$string['default_flag'] = 'Defecto';
$string['config'] = 'Configuración';
$string['receipt'] = 'Recibir copia';
$string['receipt_help'] = 'Recibir una copia del emial que se va a enviar';
$string['no_alternates'] = 'No hay correos alternativos para {$a->fullname}. Continuar para crear uno.';

$string['select_users'] = 'Selecciona Usuarios ...';
$string['select_groups'] = 'Selecciona Secciones ...';

$string['moodle_attachments'] = 'Adjuntos Moodle ({$a})';
$string['download_all'] = 'Descarga Todos';
$string['qm_contents'] = 'Descarga Contenido de Fichero';

// Config form strings
$string['allowstudents'] = 'Permitir a los estudiantes a usar Quickmail';
$string['select_roles'] = 'Roles a filtrar por';
$string['reset'] = 'Restaurar opciones por defecto';

$string['no_type'] = '{$a} no es un tipo de visor aceptable. Por favor usa la aplicación correctamente';
$string['no_email'] = 'No se puede enviar a {$a->firstname} {$a->lastname}.';
$string['no_log'] = 'Aún no tienes un historial.';
$string['no_drafts'] = 'No tienes borradores.';
$string['no_subject'] = 'Debe haber un asunto';
$string['no_course'] = 'Curso con invalido con id {$a}';
$string['no_permission'] = 'No tienes permisos para enviar con Quickmail.';
$string['no_usergroups'] = 'No hay usuarios en tu grupo a quienes enviar mail.';
$string['no_users'] = 'No hay usuarios que puedan usar el envio de correos';
$string['no_selected'] = 'Tienes que seleccionar algun usuario para enviar el mail.';
$string['not_valid'] = 'No hay un visor valido para ver los logs {$a}';
$string['not_valid_user'] = 'No puedes ver el historial de los otros usuarios.';
$string['not_valid_action'] = 'Debes seleccionar una accion valida: {$a}';
$string['not_valid_typeid'] = 'Debes seleccionar un correo valido para {$a}';
$string['delete_failed'] = 'Fallo al borrar el email';
$string['required'] = 'Por favor rellene todos los datos requeridos.';
$string['prepend_class'] = 'Pre poner el nombre del curso';
$string['prepend_class_desc'] = 'Pre poner el nombre del curso en el asunto.';
$string['ferpa'] = 'Modo FERPA';
$string['ferpa_desc'] = 'Allows the system to behave either according to the course groupmode setting, ignoring the groupmode setting but separating groups, or ignoring groups altogether.';
$string['strictferpa'] = 'Siempre separar grupos';
$string['courseferpa'] = 'Respetar el modo de curso';
$string['noferpa'] = 'No respetar grupos';
$string['courselayout'] = 'Curso';
$string['courselayout_desc'] = 'Use _Course_ page layout  when rendering the Quickmail block pages. Enable this setting, if you are getting Moodle form fixed width issues.';

$string['are_you_sure'] = 'Seguro que quieres eliminar {$a->title}? Esta accion no puede deshacerse.';

// Alternate Email strings
$string['alternate_new'] = 'Añadir una direccion alternativa';
$string['sure'] = 'Seguro que quieres eliminar {$a->address}? Esta accion no se puede deshacer.';
$string['valid'] = 'Activado';
$string['approved'] = 'Aprobado';
$string['waiting'] = 'Esperando';
$string['entry_activated'] = 'Mail alternativo {$a->address} se puede usar en {$a->course}.';
$string['entry_key_not_valid'] = 'El link ya no es valido para {$a->address}. Continua para reenviar el link de activacion.';
$string['entry_saved'] = 'Correo alternativo {$a->address} ha sido guardado';
$string['entry_success'] = 'El email de verificacion ha sido enviado a {$a->address}. Las instrucciones han sido enviadas en el correo.';
$string['entry_failure'] = 'No puede enviarse un correo a {$a->address}. Por favor compueba que {$a->address} existe y pruebe de nuevo.';
$string['alternate_from'] = 'Moodle: Quickmail';
$string['alternate_subject'] = 'Verificación de mail alternativo';
$string['alternate_body'] = '
<p>
{$a->fullname} añadido {$a->address} como un emisor alternativo para el curso {$a->course}.
</p>

<p>
Si quieres continuar con la verificacion por favor visite el
el siguiente link: {$a->url}.
</p>


Gracias.
';
