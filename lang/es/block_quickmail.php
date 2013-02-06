<?php

$string['pluginname'] = 'Quick-argo';
$string['quickmail:cansend'] = "Permitir a los usuarios enviar email atrav&eacute;s de Quickmail";
$string['quickmail:canconfig'] = "Permite a los usuarios configurar la instancia de QuickMail.";
$string['quickmail:canimpersonate'] = "Permite a los usuarios acceder al sistema como a otros usuarios y ver el historial.";
$string['quickmail:allowalternate'] = "Permite a los usuarios a&ntilde;adir un email alternativo par alos cursos.";
$string['backup_history'] = 'Incluir historial de Quickmail';
$string['restore_history'] = 'Restaurar el historial de Quickmail';
$string['overwrite_history'] = 'Sobreescribir el historial de Quickmail';
$string['alternate'] = 'Direcci&oacute;n Alternativa';
$string['composenew'] = 'Escribir un nuevo mensaje';
$string['email'] = 'Mensaje';
$string['drafts'] = 'Ver borradores';
$string['history'] = 'Ver Enviados';
$string['log'] = $string['history'];
$string['from'] = 'De';
$string['selected'] = 'Seleccionar Destinatarios';
$string['add_button'] = 'A&ntilde;adir';
$string['remove_button'] = 'Borrar';
$string['add_all'] = 'A&ntilde;adir Todos';
$string['remove_all'] = 'Borrar Todos';
$string['role_filter'] = 'Filtar por Rol';
$string['no_filter'] = 'Sin filtro ';
$string['potential_users'] = 'Destinatarios Potenciales';
$string['potential_sections'] = 'Secciones potenciales';
$string['no_section'] = 'No de una seccion';
$string['all_sections'] = 'Todas las Secciones';
$string['attachment'] = 'Adjuntos(s)';
$string['subject'] = 'Asunto';
$string['message'] = 'Mensaje';
$string['send_email'] = 'Enviar Correo';
$string['save_draft'] = 'Guardar Correo';
$string['actions'] = 'Acciones';
$string['signature'] = 'Firmas';
$string['delete_confirm'] = 'Estas seguro que quieres borrar el mensaje con los siguientes detalles: {$a}';
$string['title'] = 'Titulo';
$string['sig'] ='Firma';
$string['default_flag'] = 'Defecto';
$string['config'] = 'Configuraci&oacute;n';
$string['receipt'] = 'Recibir una copia';
$string['receipt_help'] = 'Recibir una copia del mensaje que se enviua';

$string['no_alternates'] = 'No se han encontrado cuentas de correo alternativas para {$a->fullname}. Hacer una m&aacute;s.';

$string['select_users'] = 'Seleccionar  Usuarios ...';
$string['select_groups'] = 'Seleccionar Secciones ...';

$string['moodle_attachments'] = 'Adjuntos ({$a})';
$string['download_all'] = 'Descargar todo';

// Config form strings
$string['allowstudents'] = 'Permitir usar a los estudiantes Quickmail';
$string['select_roles'] = 'Filtrar Roles por ';
$string['reset'] = 'Restaura sistema por defecto';

$string['no_type'] = '{$a} No es un tipo de vista PERMITIDO. Porfavor usa la aplicaci&oacute;n  correspondiente.';
$string['no_email'] = 'No seha podido enviar a {$a->firstname} {$a->lastname}.';
$string['no_log'] = 'Aun no tienes historial de emails.';
$string['no_drafts'] = 'No tienes historial de borradores.';
$string['no_subject'] = 'Debes escribir un asunto';
$string['no_course'] = 'Curso invalido con ID {$a}';
$string['no_permission'] = 'No tienes permisos para aenviar email con Quickmail.';
$string['no_users'] = 'No hay usuarios a los que se les pueda enviar correos electr&oacute;nicos.';
$string['no_selected'] = 'Debes seleccionar alg&uacute;n usuario para enviar correos electr&oacute;nico.';
$string['not_valid'] = 'Esto no es un  tipo de visor de registro v&aacute;lido de correo electr&oacute;nico  : {$a}';
$string['not_valid_user'] = 'No puedes ver el historial de correos de otros.';
$string['not_valid_action'] = 'Debes indicar una accion v&aacute;lida: {$a}';
$string['not_valid_typeid'] = 'Debes proporcionar una email valido para {$a}';
$string['delete_failed'] = 'Error al borrar el email';
$string['required'] = 'Por favor, rellene los campos obligatorios.';
$string['prepend_class'] = 'Anteponer el nombre del curso';
$string['prepend_class_desc'] = 'Anteponer el nombre corto del curso al asunto del email.';
$string['ferpa'] = 'Modo FERPA';
$string['ferpa_desc'] = 'Permite que el sistema se comporte bien en función de la configuración groupmode curso, ignorando la configuración groupmode pero separando grupos, o haciendo caso omiso de los grupos en conjunto.';
$string['strictferpa'] = 'Siempre Grupos Separedaos';
$string['courseferpa'] = 'Respetar el Modo del Cursos';
$string['noferpa'] = 'No Restar grupos';
$string['courselayout'] = 'Etiqueta del curso';
$string['courselayout_desc'] = 'Use _Course_ page Etiqueta para representar las p&aacute;ginas de blog de Quickmail. Abilita esta secci&oacute;n, si est&aacute;s recibiendo formularios de ancho fijo.';

$string['are_you_sure'] = 'Estas seguro de querre borrar {$a->title}? Esta acci&oacute;n no se puede deshacer.';

// Alternate Email strings
$string['alternate_new'] = 'A&ntilde;adir Direcci&oacute;n Alternativa';
$string['sure'] = 'Est&aacute;s seguro de querer borrar {$a->address}? &Eacute;sta acci&oacute;n no se puede deshacer.';
$string['valid'] = 'Estado de Activaci&oacute;n';
$string['approved'] = 'Aprovado';
$string['waiting'] = 'Esperando';
$string['entry_activated'] = 'La direcci&oacute;n Alternativa{$a->address} no puede ser utilizada en  {$a->course}.';
$string['entry_key_not_valid'] = 'enlace de activaci&oacute;n no es v&aacute;lida para {$a->address}.Continuar para reenviar el enlace de activaci&oacute;n.';
$string['entry_saved'] = 'Direcci&oacute;n alternativa {$a->address} Ha sido guardada.';
$string['entry_success'] = 'Se ha enviado un email   {$a->address} para verificar que la direcci&oacute;n es v&aacute;lida.Las instrucciones sobre c&oacute;mo activar la direcci&oacute;n est&aacute; contenida en sus contenidos.';
$string['entry_failure'] = 'Un email no se ha podido enviar a {$a->address}. Por favor verifica que {$a->address} existe, e int&eacute;ntalo de nuevo.';
$string['alternate_from'] = 'Moodle: Quickmail';
$string['alternate_subject'] = 'Comprobaci&oacute;n de la direcci&oacute;n de correo alternativa ';
$string['alternate_body'] = '
<p>
{$a->fullname} A&ntilde;adido {$a->address} como una direcci&oacute;n de envio alternativa  para  {$a->course}.
</p>

<p>
El prop&oacute;sito de este email es comprobar que esta direcci&oacute;n existe, y el propietario de 
esta direcci&oacute;n cuenta con los permisos adecuados en Moodle.
</p>

<p>
Si tu deseas completar el proceso de comprobaci&oacute;n,por favor continua 
escribiendo en tu navegador la siguiente direcci&oacute;n:: {$a->url}.
</p>

<p>
Si la descripci&oacute;n de este email, no tienen ning&uacute;n sentido para ti, entonces lo has recibido 
por error. Simplemente borra este emails.
</p>

Gracias.
';
$string['Destinatario'] ="Para";
$string['received'] ="Recibidos";
$string['Carpetas'] ="Carpetas: ";
$string['Crear_Nueva_carpeta'] ="Crear Nueva Carpeta";
$string['Mover_a'] ="Mover mensajes marcados a la Carpeta seleccionada";
$string['Search'] ="Buscar";
$string['Result_Search'] ="Resulatdos de la b&uacute;squeda";
$string['Carpeta'] ="Carpeta: ";
$string['user_filter'] = 'Filtar por Usuario';
$string['delete_msg'] = 'Borrado de Mensajes';