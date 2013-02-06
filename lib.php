<?php
// Written at Louisiana State University
// Modified by Barbara Vergara www.fecyl.com

abstract class quickmail {
        public static function _s($key, $a = null) {
                return get_string($key, 'block_quickmail', $a);
        }
            static function attachment_names($draft) {
            global $USER;
                    
            $usercontext = get_context_instance(CONTEXT_USER, $USER->id);
                            
            $fs = get_file_storage();
            $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draft, 'id');
                                            
            $only_files = array_filter($files, function($file) {
            return !$file->is_directory() and $file->get_filename() != '.';
            });
                                                    
            $only_names = function ($file) { return $file->get_filename(); };
                                                            
            $only_named_files = array_map($only_names, $only_files);
                                                                    
            return implode(',', $only_named_files);
            }
            static function borrar_carpeta($id_carpeta,$courseid){
             global $DB;
            // seleccionamos todos los correos de esa carpeta
            $mensajes=$DB->get_records('block_quickmail_folders_msg',array('id_folder'=>$id_carpeta));
            $context = get_context_instance(CONTEXT_COURSE, $courseid);
            $contexto_id=$context->id;
            foreach ($mensajes as $mensaje) 
             {
              $id_msg=$mensaje->id;
              $id_alumno=$mensaje->mailto;
              quickmail::cleanup('block_quickmail_folders_msg',$contexto_id , $id_msg, $id_alumno);	
              }
              $DB->delete_records('block_quickmail_folders', array('id'=>$id_carpeta));
            }
           static	function cabecera_de_links($type,$courseid)
          {
            global $USER,$DB;
            $gen_url = function($type) use ($courseid) {
             $email_param = array('courseid' => $courseid, 'type' => $type);
             return new moodle_url('emaillog.php', $email_param);
            };
            
            $links=array();
            $links2=array();
                                                                       
            $ruta_escribir=new moodle_url('email.php', array('courseid' => $courseid));
            $links[]= html_writer::link($ruta_escribir, quickmail::_s('composenew'),array('class'=>'escribir'));
                                                                                            	 
            if ($type !='log')
             $links[]= html_writer::link($gen_url('log'), quickmail::_s('history'));
            if ($type !='drafts')
             $links[]= html_writer::link ($gen_url('drafts'), quickmail::_s('drafts'),array('class'=>'borrador'));
            if ($type !='received')
            {	
             $ruta_recibidos=new moodle_url('emaillog.php', array('courseid' => $courseid, 'action'=>'received','typeid'=>$USER->id,'type' =>'received'));
             $links[]= html_writer::link($ruta_recibidos, quickmail::_s('received'));
            }
            // ahora hacemos una consulta de las carpetas que tenemos 
             $mas_carpetas = $DB->get_records('block_quickmail_folders', array ('id_curso'=>$courseid, 'id_alumno'=>$USER->id), 'nombre ASC', 'nombre,id');
            foreach ($mas_carpetas as $carpeta) 
            {
             $ruta_creados=new moodle_url('emaillog.php', array('courseid' => $courseid, 'action'=>'otrasCapetas','typeid'=>$USER->id,'id_folder' =>$carpeta->id, 'type'=>'folders_msg'));
             $links2[]= html_writer::link($ruta_creados, $carpeta->nombre);
             }
            
             return  html_writer::tag('div',implode(html_writer::empty_tag('br'),$links).html_writer::empty_tag('br').implode(html_writer::empty_tag('br'),$links2), array('class'=>'lateral_izq'));
            }
            
            static function cambiar_tiempo($time) {
             $time=str_replace('Monday','Lunes',$time);
             $time=str_replace('Tuesday','Martes',$time);
             $time=str_replace('Wednesday','Mi&eacute;rcoles',$time);
             $time=str_replace('Thursday','Jueves',$time);
             $time=str_replace('Friday','Viernes',$time);
             $time=str_replace('Saturday','S&aacute;bado',$time);
             $time=str_replace('Sunday','Domingo',$time);
             $time=str_replace('Jaunary','Enero',$time);
             $time=str_replace('February','Febrero',$time);
             $time=str_replace('March','Marzo',$time);
             $time=str_replace('April','Abril',$time);
             $time=str_replace('May','Mayo',$time);
             $time=str_replace('June','Junio',$time);
             $time=str_replace('July','Julio',$time);
             $time=str_replace('August','Agosto',$time);
             $time=str_replace('September','Septiembre',$time);
             $time=str_replace('October','Octubre',$time);
             $time=str_replace('November','Noviembre',$time);
             $time=str_replace('December','Diciembre',$time);
             return $time;


    }
     static function cleanup($table, $contextid, $itemid, $de_quien) {
       global $DB;
                 
       // Clean up the files associated with this email
       // Fortunately, they are only db references, but
       // they shouldn't be there, nonetheless.
    switch($table) {	
      case "block_quickmail_log": 
       $tabla_letra='L';
      break;
      case "block_quickmail_drafts": 
       $tabla_letra='D';
      break;
      case "block_quickmail_folders_msg": 
       $tabla_letra='O';
      break;
      }
      
      
      $d_correo=$DB->get_records('block_quickmail_delete', array('id_email' => $itemid,'id_tabla'=>$tabla_letra),'', 'userid');
      $destinatarios=$DB->get_field($table,mailto, array('id' => $itemid));
      $destinatarios=explode(',',$destinatarios);
                                    					
      if ((count($d_correo) == count($destinatarios)) and (!in_array ($de_quien,$d_correo))) {
      $filearea = end(explode('_', $table));
                                                                                 
      $fs = get_file_storage();
      
      $fs->delete_area_files(
          $contextid, 'block_quickmail',
          'attachment_' . $filearea, $itemid
      );
      
      $fs->delete_area_files(
          $contextid, 'block_quickmail',
          $filearea, $itemid
      );
      $DB->delete_records('block_quickmail_delete',  array('id_email' => $itemid,'id_tabla'=>$tabla_letra));
      //    				$DB->delete_records('block_quickmail_delete',  array('id_email' => $itemid));
      return $DB->delete_records($table, array('id' => $itemid));
      
      }else {
       $delete_message = new stdClass();
       $delete_message->id_email=$itemid;
       $delete_message->userid=$de_quien;
       $delete_message->id_tabla=$tabla_letra;
       return 	$DB->insert_record('block_quickmail_delete', $delete_message);
      }
 }
    static function default_config($courseid) {
        global $DB;
                        
        $params = array('coursesid' => $courseid);
        $DB->delete_records('block_quickmail_config', $params);
}
 function delete_dialog($courseid, $type, $typeid, $de_quien,$de_donde_vengo = '', $id_folder = '') {
 global $CFG, $DB, $USER, $OUTPUT;
             
    $email = $DB->get_record('block_quickmail_'.$type, array('id' => $typeid));
                     
    if (empty($email))
    print_error('not_valid_typeid', 'block_quickmail', '', $typeid);
                                         
    switch ($de_donde_vengo) {
      case "received":
        $params = array('courseid' => $courseid, 'type' => $de_donde_vengo,'action' => $de_donde_vengo,'typeid'=>$USER->id);
        $yes_params = array('courseid' => $courseid, 'typeid' => $typeid, 'action' => 'confirm', 'de_quien'=>$de_quien , 'de_donde_vengo'=>$de_donde_vengo);
      break; 
      case "otrasCapetas":
        $params = array('courseid' => $courseid, 'type' => $type,'action' => $de_donde_vengo,'id_folder'=>  $id_folder  ,'typeid'=>$USER->id);
        $yes_params = array('courseid' => $courseid, 'typeid' => $typeid, 'action' => 'confirm', 'de_quien'=>$de_quien , 'type' => $type, 'de_donde_vengo'=>$de_donde_vengo, 'id_folder'=>$id_folder);
        
      break; 
      default :
        $params = array('courseid' => $courseid, 'type' => $type,'typeid'=>$USER->id);
        $yes_params = array('courseid' => $courseid, 'typeid' => $typeid, 'action' => 'confirm', 'de_quien'=>$de_quien , 'type' => $type);
        
        break; 
    }
                                                 
    $detinatarios=explode(",", $email->mailto);$destiantario="";
    for ($i=0;$i<count($detinatarios);$i++){
        $historial_param = array( 'user' => $USER->id, 'id' => $detinatarios[$i], 'history' => '1');
        $historial_enlace=new moodle_url('/message/index.php', $historial_param);
        $destiantario .= html_writer::link ($historial_enlace,quickmail::nombre_destinatario($detinatarios[$i])).", ";
    }
    $destiantario=substr($destiantario,0,-2);
    
    $de_param = array('user' => $USER->id, 'id' =>$email->userid , 'history' => '1');
    $del_enlace=new moodle_url('/message/index.php', $de_param);
    $de_quien = html_writer::link ($del_enlace,quickmail::nombre_destinatario($email->userid));
    
    
    
    $optionyes = new moodle_url('/blocks/quickmail/emaillog.php', $yes_params);
    $optionno = new moodle_url('/blocks/quickmail/emaillog.php', $params);
    
    
    $table = new html_table();
    $table->head = array(get_string('date'), quickmail::_s('from'),  quickmail::_s('Destinatario'),quickmail::_s('subject'),quickmail::_s('attachment'));
    
    
    
    
    $de_param = array('user' => $email->mailto, 'id' =>$email->userid , 'history' => '1');
    $del_enlace=new moodle_url('/message/index.php', $de_param);
    $de_quien = html_writer::link ($del_enlace,quickmail::nombre_destinatario($email->userid));
    if (empty($email->attachment))  $adjunto="";
    else 	$adjunto= quickmail::enlazar_adjuntos($courseid,'log',$email->id, ' | ' ,$email->time);
    $table->data = array(
    new html_table_row(array(
    new html_table_cell(quickmail::format_time($email->time)),
    new html_table_cell($de_quien),
    new html_table_cell($destiantario),
    new html_table_cell($email->subject),
    new html_table_cell($adjunto)
    )
    ),
    new html_table_row(array( 
    new html_table_cell("<b>".quickmail::_s('email').":</b>"))),
    new html_table_row(array( 
    new html_table_cell($email->message)))
    );
    
    
    $msg = quickmail::_s('delete_confirm', html_writer::table($table));
    
    $html = $OUTPUT->confirm($msg, $optionyes, $optionno);
    return $html;
    }
     static function draft_cleanup($contextid, $itemid) {
     return quickmail::cleanup('block_quickmail_drafts', $contextid, $itemid);
     }
private static function flatten_subdirs($tree, $gen_link, $level=0) {
    $attachments = $spaces = '';
    foreach (range(0, $level) as $space) {
        $spaces .= " - ";
    }
    foreach ($tree['files'] as $filename => $file) {
        $attachments .= $spaces . " " . $gen_link($filename) . "\n<br/>";
    }
    foreach ($tree['subdirs'] as $dirname => $subdir) {
        $attachments .= $spaces . " <b> ". $dirname . ":</b>\n<br/>";
        $attachments .= self::flatten_subdirs($subdir, $gen_link, $level + 2);
    }

return $attachments;
}
static function enlazar_adjuntos($courseid, $carpeta,$id_email, $juntar, $tiempo )
     {
      $attchments = '';
      $filename = '';
      $context = get_context_instance(CONTEXT_COURSE, $courseid);
                           
      $fs = get_file_storage();
      $tree = $fs->get_area_tree(
      $context->id, 'block_quickmail',
      'attachment_' . $carpeta, $id_email, 'id'
      );
      $base_url = "/$context->id/block_quickmail/attachment_{$carpeta}/$id_email";
      /**
      * @param string $filename name of the file for which we are generating a download link
      * @param string $text optional param sets the link text; if not given, filename is used
      * @param bool $plain if itrue, we will output a clean url for plain text email users
      *
      */
      $gen_link = function ($filename, $text = '') use ($base_url) {
      if (empty($text)) {
          $text = $filename;
      }
      $url = new moodle_url('/pluginfile.php', array(
      'forcedownload' => 1,
      'file' => "/$base_url/$filename"
      ));
      return html_writer::link($url, $text);
      };
      $link = $gen_link($tiempo."_attachments.zip", self::_s('download_all'));
      $attachments .= $link;
      $attachments .= $juntar;
      return $attachments . self::flatten_subdirs($tree, $gen_link);	
     }
     static function existe_carpeta($id_curso, $id_alumno, $nombre) {
        global $DB;
        $folder=array('nombre'=>$nombre,'id_curso'=>$id_curso,'id_alumno'=>$id_alumno);
        if ($DB->record_exists('block_quickmail_folders',$folder)) return true; 
        else return false;
    }
   static function filter_roles($user_roles, $master_roles) {
    return array_uintersect($master_roles, $user_roles, function($a, $b) {
    return strcmp($a->shortname, $b->shortname);
    });
   }
  static function format_time($time) {
  return quickmail::cambiar_tiempo(userdate($time, '%A, %d %B %Y, %I:%M %P','1'));
 }
 /**
 * get all users for a given context
 * @param $context a moodle context id
 * @return array of sparse user objects
 */
     public static function get_all_users($context){
             global $DB;
     $everyone = get_role_users(0, $context, false, 'u.id, u.firstname, u.lastname,
     u.email, u.mailformat, u.suspended, u.maildisplay, r.id AS roleid',
     'u.lastname, u.firstname');
     return $everyone;
     }
         /**
          * * @TODO this function relies on self::get_all_users, it should not have to
          *
          * returns all users enrolled in a gived coure EXCEPT for those whose
          * mdl_user_enrolments.status field is 1 (suspended)
          * @param $context moodle context id
          * @param $courseid the course id
          */
      public static function get_non_suspended_users($context, $courseid){
      global $DB;
      $everyone = self::get_all_users($context);
       
       $sql = "SELECT u.id, u.firstname, u.lastname, u.email, u.mailformat, u.suspended, u.maildisplay, ue.status
       FROM {user} as u
       JOIN {user_enrolments} as ue
       ON u.id = ue.userid
       JOIN {enrol} as en
       ON en.id = ue.enrolid
       WHERE en.courseid = ?
       AND ue.status = ?";
       //let's use a recordset in case the enrollment is huge
       $rs_valids = $DB->get_recordset_sql($sql, array($courseid, 0));
       
       //container for user_enrolments records
       $valids = array();
       
       /**
       * @TODO use a cleaner mechanism from std lib to do this without iterating over the array
       * for each chunk of the recordset,
       * insert the record into the valids container
       * using the id number as the array key;
       * this amtches the format used by self::get_all_users
       */
       foreach($rs_valids as $rsv){
       $valids[$rsv->id] = $rsv;
       }
       //required to close te recordset
       $rs_valids->close();
       
       //get the intersection of self::all_users and this potentially shorter list
       $evryone_not_suspended = array_intersect_key($valids, $everyone);
       return $evryone_not_suspended;
       }
       
         static function guardar_nombre_carpeta($nombre_carpeta,$id_carpeta)
             {
                global $DB;
                $carpeta = new stdClass;
                $carpeta->nombre = $nombre_carpeta;
                $carpeta->id = $id_carpeta;
                $DB->update_record('block_quickmail_folders', $carpeta);
             }
         static function history_cleanup($contextid, $itemid) {
                return quickmail::cleanup('block_quickmail_log', $contextid, $itemid);
        }
      function list_entries($courseid, $type, $page, $perpage, $userid, $count, $can_delete, $busqueda) {
       global $CFG, $DB, $OUTPUT,$USER;
                  
      $dbtable = 'block_quickmail_'.$type;
      switch($type) {	
      case "log": 
      $tabla_letra='L';
       break;
       case "drafts": 
       $tabla_letra='D';
       break;
       case "folders_msg": 
       $tabla_letra='O';
       break;
       }
       
       
       // hacemos distinción si los resultados a mosntrar son más de uno o dos 
       $seleccion=" firstname LIKE '%".$busqueda."%' OR lastname LIKE '%".$busqueda."%' OR email LIKE '%".$busqueda."%'";
       
       $num_regis = $DB->count_records_select('user', $seleccion);
       if ($num_regis > 1) $union=' IN ';
       else $union=' REGEXP ';
       
       $select =" id not in ( SELECT id_email  FROM  mdl_block_quickmail_delete where userid = ". $userid." and id_tabla like '".$tabla_letra."' )  and  courseid ='".$courseid."'  and userid='".$userid."' and (subject like '%".$busqueda."%' or message like '%".$busqueda."%' or mailto  ".$union." ( SELECT id FROM mdl_user WHERE ".$seleccion.") )" ;
       
       $table = new html_table();
       
       // $params = array('courseid' => $courseid, 'userid' => $userid);
       //        $logs = $DB->get_records($dbtable, $params,'time DESC', '*', $page * $perpage, $perpage * ($page + 1));
       
       $logs = $DB->get_records_select($dbtable, $select, $params, 'time DESC', '*', $page * $perpage, $perpage * ($page + 1));
       
       
       $accion =get_string('action').html_writer::checkbox('Mover[0]','',false,'',array('onclick'=>"seleccionar_todo('formulario_mover_msg')",'id'=>'Mover[0]'));
       
       $table->head= array(get_string('date'),  quickmail::_s('Destinatario'), quickmail::_s('subject'),
       quickmail::_s('attachment'),$accion);
       
       $table->rowclasses[0]="sub_rayado";
       $table->data = array();
       
       
       foreach ($logs as $log) {
       $date = quickmail::format_time($log->time);
       $subject = $log->subject;
       $attachments = $log->attachment;
       
       // bucle para  ver cuantos destinatarios tiene
       $detinatarios=explode(",", $log->mailto);$destiantario="";
       for ($i=0;$i<count($detinatarios);$i++){
       $historial_param = array( 'user' => $USER->id, 'id' => $detinatarios[$i], 'history' => '1');
       $historial_enlace=new moodle_url('/message/index.php', $historial_param);
       $destiantario .= html_writer::link ($historial_enlace,quickmail::nombre_destinatario($detinatarios[$i])).", ";
       }
       $destiantario=substr($destiantario,0,-2);
       $params = array(
       'courseid' => $log->courseid,
       'type' => $type,
       'typeid' => $log->id
       );
       
       $actions = array();
       
       $open_link = html_writer::link(
       new moodle_url('/blocks/quickmail/email.php', $params),
       $OUTPUT->pix_icon('i/search', 'Ver correo completo')
       );
       $actions[] = $open_link;
       if ($can_delete) {
       $delete_params = $params + array(
       'userid' => $userid,
       'de_quien' => $log->userid,
       'action' => 'delete'
       );
       
       $delete_link = html_writer::link (
       new moodle_url('/blocks/quickmail/emaillog.php', $delete_params),
       $OUTPUT->pix_icon("i/cross_red_big", "Borrar correo ")
       );
       
       $actions[] = $delete_link;
       }
       
       $k++;
      $actions[] =html_writer::checkbox('Mover['.$k.']',$log->id,false,'',array('id'=>'Mover['.$k.']'));
       $action_links = implode(' ', $actions);
       if (empty($log->attachment))  $adjunto="";
       else 		$adjuntos= quickmail::enlazar_adjuntos($log->courseid,$type,$log->id, ' | ',$log->time);
       $row_contenido = new html_table_row(); 
       $i++;
       if ($i>1)
       $row_contenido->style='background-image:url('.$CFG->wwwroot.'/blocks/quickmail/pix/bline.png);	background-repeat:repeat-x; background-position:bottom; 	font-size:12px;';
       
       $row_contenido->id='msg___'.$log->id;
       $cell_uno = new html_table_cell();$cell_dos = new html_table_cell();$cell_tres = new html_table_cell();$cell_cuatro = new html_table_cell();$cell_cinco = new html_table_cell();
       $cell_cinco->style='width:60px;';
       $cell_uno->text=$date;$cell_dos->text=$destiantario;$cell_tres->text=str_ireplace($busqueda,'<font  color="#FF0000" size="+1">'.$busqueda.'</font>',$subject);$cell_cuatro->text= $adjuntos;$cell_cinco->text=$action_links;
       $row_contenido->cells[]=$cell_uno;$row_contenido->cells[]=$cell_dos;$row_contenido->cells[]=$cell_tres;$row_contenido->cells[]=$cell_cuatro;$row_contenido->cells[]=$cell_cinco;
       $table->data[]= $row_contenido;
       
       }
       $carpetitas=array();
       $mas_carpetas = $DB->get_records('block_quickmail_folders', array ('id_curso'=>$courseid, 'id_alumno'=>$USER->id), 'nombre ASC', 'nombre,id');
       foreach ($mas_carpetas as $carpeta) 
       {
       $carpetitas[$carpeta->id]=$carpeta->nombre;
      }
      $cell1 = new html_table_cell(); $cell2 = new html_table_cell(); 
      $cell1->colspan = 2; $cell2->colspan = 3; 
      $cell1->style="text-align:right;font-size:12px;";
      $cell1->text = quickmail::_s('Carpetas').html_writer::select($carpetitas,'Carpetas_a_mover','','',array('id'=>'Carpetas_a_mover')).html_writer::tag('img','',array('src'=>$CFG->wwwroot."/pix/f/explore-32.png",'alt'=>quickmail::_s('Mover_a'), 'onclick'=>"mover_carpetas('".$USER->id."', '".$courseid."','capa_orperaciones','Carpetas_a_mover','".$type."')"));
      $cell2->text ='';
      $row1 = new html_table_row(); $row2 = new html_table_row(); 
      $row1->cells[] = $cell2;$row1->cells[] = $cell1;
      $table->data[]= $row1;
      $capa_operaciones=html_writer::tag('div','',array('id'=>'capa_orperaciones'));
      if (trim($busqueda)!="")$palabra_buscada= quickmail::_s('Result_Search').": <b>".$busqueda."</b>";
      
      $capa_buscar=html_writer::tag('div',quickmail::_s('Search').
      html_writer::tag('input','',array('type'=>'text','id'=>'buscar_email','name'=>'buscar_email', 'onclick'=>"submitenter('event')")).
      html_writer::tag('img','',array('src'=>$CFG->wwwroot."/pix/a/search.png",'alt'=>quickmail::_s('Mover_a'), 'onclick'=>'buscar_cadena()')).
      html_writer::tag('span', $palabra_buscada,array('style'=>'margin-left:50px;color:navy'))
      ,array('id'=>'capa_buscar'));
      $opciones_ocultas =html_writer::tag('input','',array('type'=>'hidden', 'id'=>'courseid','name'=>'courseid', 'value'=>$courseid));
      $opciones_ocultas .=html_writer::tag('input','',array('type'=>'hidden', 'id'=>'userid','name'=>'userid', 'value'=>$USER->id));
      $opciones_ocultas .=html_writer::tag('input','',array('type'=>'hidden', 'id'=>'type','name'=>'type', 'value'=>$type));
      
      
      $paging = $OUTPUT->paging_bar($count, $page, $perpage,
      '/blocks/quickmail/emaillog.php?type='.$type.'&amp;courseid='.$courseid);
      
      
      $html = quickmail::cabecera_de_links($type,$courseid);
      $html .= $capa_operaciones;
      $html .= html_writer::start_tag('form',array('id'=>'formulario_mover_msg','name'=>'formulario_mover_msg', 'action'=>'/blocks/quickmail/emaillog.php') );
      $html .= $capa_buscar.html_writer::table($table).$opciones_ocultas.html_writer::end_tag('form');
     $html .= $paging;
     
     return $html;
     }
     static    function list_entries_recibidas($courseid, $type, $page, $perpage, $userid, $count, $can_delete, $busqueda) {
        global $CFG, $DB, $OUTPUT,$USER;
                 
        $dbtable = 'block_quickmail_'.$type;
                         
        $table = new html_table();
        switch($type) {	
            case "log": 
            $tabla_letra='L';
            break;
            case "drafts": 
            $tabla_letra='D';
            break;
            case "folders_msg": 
            $tabla_letra='O';
            break;
            }
            // hacemos distinci¢n si los resultados a mosntrar son m s de uno o dos 
            $seleccion=" firstname LIKE '%".$busqueda."%' OR lastname LIKE '%".$busqueda."%' OR email LIKE '%".$busqueda."%'";
            
            
            $select =" id not in ( SELECT id_email  FROM  mdl_block_quickmail_delete where userid = ". $userid."  and id_tabla like '".$tabla_letra."'  )  and  courseid ='".$courseid."'  and (  mailto = ".$userid." OR mailto LIKE '".$userid.",%' OR mailto LIKE '%,".$userid."' OR mailto LIKE '%,".$userid.",%' )  and (subject like '%".$busqueda."%' or message like '%".$busqueda."%' or userid  IN ( SELECT id FROM mdl_user WHERE ".$seleccion.")) " ;
            $logs = $DB->get_records_select($dbtable, $select, $params, 'time DESC', '*', $page * $perpage, $perpage * ($page + 1));
            
            // $params = array('courseid' => $courseid, 'mailto' => $userid);
            //$logs = $DB->get_records($dbtable, $params, 'time DESC', '*', $page * $perpage, $perpage * ($page + 1));
            $params_otros = array('useridto' => $userid);
            $logs_otros = $DB->get_records('message_read', $params_otros,
            'timecreated DESC', '*', $page * $perpage, $perpage * ($page + 1));    
            $accion =get_string('action').html_writer::checkbox('Mover[0]','',false,'',array('onclick'=>"seleccionar_todo('formulario_mover_msg')",'id'=>'Mover[0]'));
            
            $table->head= array(get_string('date'),  quickmail::_s('from'), quickmail::_s('subject'),
            quickmail::_s('attachment'), $accion);
            
            $table->data = array();
            $table->rowclasses[0]="sub_rayado";
            
            foreach ($logs as $log) {
            $date = quickmail::format_time($log->time);
            $subject = $log->subject;
            $attachments = $log->attachment;
            
            $historial_param = array('user' => $USER->id, 'id' => $log->userid, 'history' => '1');
            $historial_enlace=new moodle_url('/message/index.php', $historial_param);
            $destiantario =  html_writer::link ($historial_enlace,quickmail::nombre_destinatario($log->userid));
            
            $params = array(
            'courseid' => $log->courseid,
            'type' => $type,
            'typeid' => $log->id
            );
            
            $actions = array();
            
            $open_link = html_writer::link(
            new moodle_url('/blocks/quickmail/email.php', $params),
            $OUTPUT->pix_icon('i/search', 'Ver correo completo')
            );
            $actions[] = $open_link;
            
            if ($can_delete) {
            $delete_params = $params + array(
            'userid' => $userid,
            'de_quien' => $userid,
            'action' => 'delete',
            'de_donde_vengo' =>'received'
            );
            
            $delete_link = html_writer::link (
            new moodle_url('/blocks/quickmail/emaillog.php', $delete_params),
            $OUTPUT->pix_icon("i/cross_red_big", "Borrar correo ")
            );
            
            $actions[] = $delete_link;
            }
            
            $k++;
            $actions[] =html_writer::checkbox('Mover['.$k.']',$log->id,false,'',array('id'=>'Mover['.$k.']'));
            $action_links = implode(' ', $actions);
            if (empty($log->attachment))  $adjunto="";
            else $adjuntos= quickmail::enlazar_adjuntos($log->courseid,'log',$log->id, ' | ',$log->time);
            
            $row_contenido = new html_table_row(); 
            $i++;
            if ($i>1)
            $row_contenido->style='background-image:url('.$CFG->wwwroot.'/blocks/quickmail/pix/bline.png);	background-repeat:repeat-x; background-position:bottom; 	font-size:12px;';
            $row_contenido->id='msg___'.$log->id;
            $cell_uno = new html_table_cell();$cell_dos = new html_table_cell();$cell_tres = new html_table_cell();$cell_cuatro = new html_table_cell();$cell_cinco = new html_table_cell();
            $cell_cinco->style='width:60px;';
            $cell_uno->text=$date;$cell_dos->text=$destiantario;$cell_tres->text=str_ireplace($busqueda,'<font  color="#FF0000" size="+1">'.$busqueda.'</font>',$subject);$cell_cuatro->text= $adjuntos;$cell_cinco->text=$action_links;
            $row_contenido->cells[]=$cell_uno;$row_contenido->cells[]=$cell_dos;$row_contenido->cells[]=$cell_tres;$row_contenido->cells[]=$cell_cuatro;$row_contenido->cells[]=$cell_cinco;
            $table->data[]= $row_contenido;
            
            // $table->data[] = array($date, $destiantario, $subject, $adjuntos, $action_links);
            }
            
            $carpetitas=array();
            $mas_carpetas = $DB->get_records('block_quickmail_folders', array ('id_curso'=>$courseid, 'id_alumno'=>$USER->id), 'nombre ASC', 'nombre,id');
            foreach ($mas_carpetas as $carpeta) 
            {
            $carpetitas[$carpeta->id]=$carpeta->nombre;
            }
            $cell1 = new html_table_cell(); $cell2 = new html_table_cell(); 
            $cell1->colspan = 3; $cell2->colspan = 2; 
            $cell1->style="text-align:right;font-size:12px;";
            $cell1->text = quickmail::_s('Carpetas').html_writer::select($carpetitas,'Carpetas_a_mover','','',array('id'=>'Carpetas_a_mover')).html_writer::tag('img','',array('src'=>$CFG->wwwroot."/pix/f/explore-32.png",'alt'=>quickmail::_s('Mover_a'), 'onclick'=>"mover_carpetas('".$USER->id."', '".$courseid."','capa_orperaciones','Carpetas_a_mover','".$type."')"));
            $cell2->text ='';
            $row1 = new html_table_row(); $row2 = new html_table_row(); 
            $row1->cells[] = $cell2;$row1->cells[] = $cell1;
            $table->data[]= $row1;
            $capa_operaciones=html_writer::tag('div','',array('id'=>'capa_orperaciones'));
            if (trim($busqueda)!="")$palabra_buscada= quickmail::_s('Result_Search').": <b>".$busqueda."</b>";
            
            $capa_buscar=html_writer::tag('div',quickmail::_s('Search').
            html_writer::tag('input','',array('type'=>'text','id'=>'buscar_email','name'=>'buscar_email', 'onclick'=>"submitenter('event')")).
            html_writer::tag('img','',array('src'=>$CFG->wwwroot."/pix/a/search.png",'alt'=>quickmail::_s('Mover_a'), 'onclick'=>'buscar_cadena()')).
            html_writer::tag('span', $palabra_buscada,array('style'=>'margin-left:50px;color:navy'))
            ,array('id'=>'capa_buscar'));
            $opciones_ocultas =html_writer::tag('input','',array('type'=>'hidden', 'id'=>'courseid','name'=>'courseid', 'value'=>$courseid));
            $opciones_ocultas .=html_writer::tag('input','',array('type'=>'hidden', 'id'=>'userid','name'=>'userid', 'value'=>$USER->id));
            $opciones_ocultas .=html_writer::tag('input','',array('type'=>'hidden', 'id'=>'type','name'=>'type', 'value'=>$type));
            $opciones_ocultas .=html_writer::tag('input','',array('type'=>'hidden', 'id'=>'action','name'=>'action', 'value'=>'received'));
            
            
            
            $paging = $OUTPUT->paging_bar($count, $page, $perpage,
            '/blocks/quickmail/emaillog.php?courseid='.$courseid.'&amp;action=received&amp;typeid='.$userid.'&amp;type=received');
            
            $html = quickmail::cabecera_de_links('received',$courseid);
            $html .= $capa_operaciones;
            $html .= html_writer::start_tag('form',array('id'=>'formulario_mover_msg','name'=>'formulario_mover_msg', 'action'=>'/blocks/quickmail/emaillog.php') );
            $html .= $capa_buscar.html_writer::table($table).$opciones_ocultas.html_writer::end_tag('form');
            $html .= $paging;
            
            return $html;
            
            }
            static	function list_entries_otras($courseid, $type, $page, $perpage, $userid, $count, $can_delete, $busqueda,$id_folder ) {
            global $CFG, $DB, $OUTPUT,$USER;
            
            $dbtable = 'block_quickmail_folders_msg';
                
                $table = new html_table();
                switch($type) {	
                 case "log": 
                 $tabla_letra='L';
                 break;
                 case "drafts": 
                 $tabla_letra='D';
                 break;
                 case "folders_msg":
                 case "folders": 
                 $tabla_letra='O';
                 break;
                 }
                 // hacemos distinci¢n si los resultados a mosntrar son m s de uno o dos 
                 $seleccion=" firstname LIKE '%".$busqueda."%' OR lastname LIKE '%".$busqueda."%' OR email LIKE '%".$busqueda."%'";
                 
                 $select ="  id not in ( SELECT id_email  FROM  mdl_block_quickmail_delete where userid = ". $userid."  and id_tabla like '".$tabla_letra."'  ) and  id_folder='".$id_folder."' and  courseid ='".$courseid."' and (subject like '%".$busqueda."%' or message like '%".$busqueda."%' or userid  IN ( SELECT id FROM mdl_user WHERE ".$seleccion.")) ";
                 
                 $logs = $DB->get_records_select($dbtable, $select, $params, 'time DESC', '*', $page * $perpage, $perpage * ($page + 1));
                 
                 
                 // $params = array('courseid' => $courseid, 'mailto' => $userid);
                 //$logs = $DB->get_records($dbtable, $params, 'time DESC', '*', $page * $perpage, $perpage * ($page + 1));
                 $params_otros = array('useridto' => $userid);
                 $logs_otros = $DB->get_records('message_read', $params_otros,
                 'timecreated DESC', '*', $page * $perpage, $perpage * ($page + 1));    
                 
                 $accion =get_string('action').html_writer::checkbox('Mover[0]','',false,'',array('onclick'=>"seleccionar_todo('formulario_mover_msg')",'id'=>'Mover[0]'));
                 
                 $table->head= array(get_string('date'),  quickmail::_s('from'), quickmail::_s('subject'),
                 quickmail::_s('attachment'), $accion);
                 
                 $table->rowclasses[0]="sub_rayado";
                 $table->data = array();
                 foreach ($logs as $log) {
                 $date = quickmail::format_time($log->time);
                 $subject = $log->subject;
                 $attachments = $log->attachment;
                 
                 $historial_param = array('user' => $USER->id, 'id' => $log->userid, 'history' => '1');
                 $historial_enlace=new moodle_url('/message/index.php', $historial_param);
                 $destiantario =  html_writer::link ($historial_enlace,quickmail::nombre_destinatario($log->userid));
                 
                 $params = array(
                 'courseid' => $log->courseid,
                 'type' => $type,
                 'typeid' => $log->id
                 );
                 
                 $actions = array();
                 
                 $open_link = html_writer::link(
                 new moodle_url('/blocks/quickmail/email.php', $params),
                 $OUTPUT->pix_icon('i/search', 'Ver correo completo')
                 );
                 $actions[] = $open_link;
                 
                 if ($can_delete) {
                 $delete_params = $params + array(
                 'userid' => $userid,
                 'de_quien' => $userid,
                 'action' => 'delete',
                 'de_donde_vengo' =>'otrasCapetas',
                 'id_folder'=>$id_folder
                 
                 );
                 
                 $delete_link = html_writer::link (
                 new moodle_url('/blocks/quickmail/emaillog.php', $delete_params),
                 $OUTPUT->pix_icon("i/cross_red_big", "Borrar correo ")
                 );
                 
                 $actions[] = $delete_link;
                 }
                 
                 $k++;
                 $actions[] =html_writer::checkbox('Mover['.$k.']',$log->id,false,'',array('id'=>'Mover['.$k.']'));
                 $action_links = implode(' ', $actions);
                 $adjuntos= "";
                 if ($log->id_msg_original !=0) 
                 $adjuntos= quickmail::enlazar_adjuntos($log->courseid,'log',$log->id_msg_original, ' | ',$log->time);
                 
                 $row_contenido = new html_table_row(); 
                 $row_contenido->id='msg___'.$log->id;
                 $i++;
                 if ($i>1)
                 $row_contenido->style='background-image:url('.$CFG->wwwroot.'/blocks/quickmail/pix/bline.png);	background-repeat:repeat-x; background-position:bottom; 	font-size:12px;';
                 $cell_uno = new html_table_cell();$cell_dos = new html_table_cell();$cell_tres = new html_table_cell();$cell_cuatro = new html_table_cell();$cell_cinco = new html_table_cell();
                 $cell_cinco->style='width:60px;';
                 $cell_uno->text=$date;$cell_dos->text=$destiantario;$cell_tres->text=str_ireplace($busqueda,'<font  color="#FF0000" size="+1">'.$busqueda.'</font>',$subject);$cell_cuatro->text= $adjuntos;$cell_cinco->text=$action_links;
                 $row_contenido->cells[]=$cell_uno;$row_contenido->cells[]=$cell_dos;$row_contenido->cells[]=$cell_tres;$row_contenido->cells[]=$cell_cuatro;$row_contenido->cells[]=$cell_cinco;
                 $table->data[]= $row_contenido;
                 
                 // $table->data[] = array($date, $destiantario, $subject, $adjuntos, $action_links);
                 }
                 $carpetitas=array();
                 $mas_carpetas = $DB->get_records('block_quickmail_folders', array ('id_curso'=>$courseid, 'id_alumno'=>$USER->id), 'nombre ASC', 'nombre,id');
                 foreach ($mas_carpetas as $carpeta) 
                 {
                 $carpetitas[$carpeta->id]=$carpeta->nombre;
                 }
                 $cell1 = new html_table_cell(); $cell2 = new html_table_cell(); 
                 $cell1->colspan = 2; $cell2->colspan = 3; 
                 $cell1->text = quickmail::_s('Carpetas').html_writer::select($carpetitas,'Carpetas_a_mover','','',array('id'=>'Carpetas_a_mover')).html_writer::tag('img','',array('src'=>$CFG->wwwroot."/pix/f/explore-32.png",'alt'=>quickmail::_s('Mover_a'), 'onclick'=>"mover_carpetas('".$USER->id."', '".$courseid."','capa_orperaciones','Carpetas_a_mover','".$type."')"));
                 $cell1->style="text-align:right;font-size:12px;";
                 
                 $cell2->text ='';
                 $row1 = new html_table_row(); $row2 = new html_table_row(); 
                 $row1->cells[] = $cell2;$row1->cells[] = $cell1;
                 $table->data[]= $row1;
                 $capa_operaciones=html_writer::tag('div','',array('id'=>'capa_orperaciones'));
                 if (trim($busqueda)!="")$palabra_buscada= quickmail::_s('Result_Search').": <b>".$busqueda."</b>";

                    $capa_buscar=html_writer::tag('div',quickmail::_s('Search').
                    html_writer::tag('input','',array('type'=>'text','id'=>'buscar_email','name'=>'buscar_email', 'onclick'=>"submitenter('event')")).
                    html_writer::tag('img','',array('src'=>$CFG->wwwroot."/pix/a/search.png",'alt'=>quickmail::_s('Mover_a'), 'onclick'=>'buscar_cadena()')).
                    html_writer::tag('span', $palabra_buscada,array('style'=>'margin-left:50px;color:navy'))
                    ,array('id'=>'capa_buscar'));
                    $opciones_ocultas =html_writer::tag('input','',array('type'=>'hidden', 'id'=>'courseid','name'=>'courseid', 'value'=>$courseid));
                    $opciones_ocultas .=html_writer::tag('input','',array('type'=>'hidden', 'id'=>'userid','name'=>'userid', 'value'=>$USER->id));
                    $opciones_ocultas .=html_writer::tag('input','',array('type'=>'hidden', 'id'=>'type','name'=>'type', 'value'=>$type));
                    $opciones_ocultas .=html_writer::tag('input','',array('type'=>'hidden', 'id'=>'action','name'=>'action', 'value'=>'received'));
                    
                    
                    $paging = $OUTPUT->paging_bar($count, $page, $perpage,
                    '/blocks/quickmail/emaillog.php?courseid='.$courseid.'&amp;action=otrasCapetas&amp;typeid='.$userid.'&amp;type=folders_msg&amp;id_folder='.$id_folder);
                    
                    $html = quickmail::cabecera_de_links('otrasCapetas',$courseid);
                    $html .= $capa_operaciones;
                    $html .= html_writer::start_tag('form',array('id'=>'formulario_mover_msg','name'=>'formulario_mover_msg', 'action'=>'/blocks/quickmail/emaillog.php') );
                    $html .= $capa_buscar.html_writer::table($table).$opciones_ocultas.html_writer::end_tag('form');
                    $html .= $paging;
                    return $html;
                    
                    }
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          
        static function load_config($courseid) {
         global $DB;
                    
        $fields = 'name,value';
        $params = array('coursesid' => $courseid);
        $table = 'block_quickmail_config';
        
        $config = $DB->get_records_menu($table, $params, '', $fields);
        
        if (empty($config)) {
        $m = 'moodle';
        $allowstudents = get_config($m, 'block_quickmail_allowstudents');
        $roleselection = get_config($m, 'block_quickmail_roleselection');
        $prepender = get_config($m, 'block_quickmail_prepend_class');
        $receipt = get_config($m, 'block_quickmail_receipt');
        $ferpa = get_config($m, 'block_quickmail_ferpa');
        
        $config = array(
        'allowstudents' => $allowstudents,
        'roleselection' => $roleselection,
        'prepend_class' => $prepender,
        'receipt' => $receipt,
        'ferpa' => $ferpa
        );
        }
        return $config;
    }
    static function mover_mensajes($usuario,$curso,$carpeta_destino, $carpeta_origen, $mensajes)
        {
          global $DB;
        $mensajes_a_mover=explode (";",substr($mensajes,0,-1));
        for ($i=0;$i<count($mensajes_a_mover);$i++)
        {
        $email = $DB->get_record('block_quickmail_'.$carpeta_origen, array('id' => $mensajes_a_mover[$i]));
        $movemessage = new stdClass();
        $movemessage->id_folder=$carpeta_destino;
        $movemessage->courseid=$curso;
        $movemessage->userid=$usuario;
        $movemessage->alternateid=$email->alternateid;
        $movemessage->mailto=$email->mailto;
        $movemessage->subject=$email->subject;
        $movemessage->message=$email->message;
        $movemessage->format=$email->format;
        $movemessage->time=$email->time; 
        $original=$email->id_msg_original;
        if ($original == 0)$movemessage->id_msg_original=$mensajes_a_mover[$i];
        else $movemessage->id_msg_original=$original;
        
        $dos=$DB->insert_record('block_quickmail_folders_msg', $movemessage);
        $DB->delete_records('block_quickmail_'.$carpeta_origen, array('id' => $mensajes_a_mover[$i]));
        
        }
        }
        static	function nombre_destinatario($ususario)
        {
        global $DB;
        $nombre=$DB->get_field('user', 'firstname', array('id' => $ususario));
        $apellido=$DB->get_field('user', 'lastname', array('id' => $ususario));
        return $nombre." ". $apellido;
        }
        static function process_attachments($context, $email, $table, $id) {
          $attchments = '';
          $filename = '';
          
          if (empty($email->attachment)) {
          return $attachments;
          }
          $fs = get_file_storage();
          $tree = $fs->get_area_tree(
          $context->id, 'block_quickmail',
          'attachment_' . $table, $id, 'id'
          );
          $base_url = "/$context->id/block_quickmail/attachment_{$table}/$id";
          
          /**
          * @param string $filename name of the file for which we are generating a download link
          * @param string $text optional param sets the link text; if not given, filename is used
          * @param bool $plain if itrue, we will output a clean url for plain text email users
          *
          */
          $gen_link = function ($filename, $text = '', $plain=false) use ($base_url) {
          if (empty($text)) {
          $text = $filename;
          }
          $url = new moodle_url('/pluginfile.php', array(
          'forcedownload' => 1,
          'file' => "/$base_url/$filename"
          ));
          //to prevent double encoding of ampersands in urls for our plaintext users,
          //we use the out() method of moodle_url
          //@see http://phpdocs.moodle.org/HEAD/moodlecore/moodle_url.html
          if($plain){
                     return $url->out(false);
                     }
                     return html_writer::link($url, $text);
                     };
                     $link = $gen_link("{$email->time}_attachments.zip", self::_s('download_all'));
                     
                     //get a plain text version of the link
                     //by calling gen_link with @param $plain set to true
                     $tlink = $gen_link("{$email->time}_attachments.zip", '', true);
                     $attachments .= "\n<br/>-------\n<br/>";
                     $attachments .= self::_s('moodle_attachments', $link);
                     if($plain){  $attachments .= "\n<br/>".$tlink."\n<br/>"; }
                     $attachments .= "\n<br/>-------\n<br/>";
                     return $attachments . self::flatten_subdirs($tree, $gen_link);    
                     
                     }
                     static function zip_attachments($context, $table, $id) {
                            global $CFG, $USER;
                                     
                            $base_path = "block_quickmail/{$USER->id}";
                            $moodle_base = "$CFG->tempdir/$base_path";
                                                     
                            if (!file_exists($moodle_base)) {
                            mkdir($moodle_base, $CFG->directorypermissions, true);
                            }
                                                             
                            $zipname = "attachment.zip";
                            $actual_zip = "$moodle_base/$zipname";
                            
                            $fs = get_file_storage();
                            $packer = get_file_packer();
                            
                            $files = $fs->get_area_files(
                            $context->id,
                            'block_quickmail',
                            'attachment_' . $table,
                            $id,
                            'id'
                            );
                            
                            $stored_files = array();
                            foreach ($files as $file) {
                            if ($file->is_directory() and $file->get_filename() == '.') {
                            continue;
                            }
                            
                            $stored_files[$file->get_filepath().$file->get_filename()] = $file;
                            }
                            
                            $packer->archive_to_pathname($stored_files, $actual_zip);
                            
                            return $actual_zip;
                            
                            }
                            
                     static function reflejar_en_mensajes($eventdata)
                     {
                     global $SITE,$CFG, $DB;
                     
                     //new message ID to return
                     $messageid = false;
                     
                     //TODO: we need to solve problems with database transactions here somehow, for now we just prevent transactions - sorry
                     $DB->transactions_forbidden();
                     
                     if (is_number($eventdata->userto)) {
                     $eventdata->userto = $DB->get_record('user', array('id' => $eventdata->userto));
                     }
                     if (is_int($eventdata->userfrom)) {
                     $eventdata->userfrom = $DB->get_record('user', array('id' => $eventdata->userfrom));
                     }
                     if (!isset($eventdata->userto->auth) or !isset($eventdata->userto->suspended) or !isset($eventdata->userto->deleted)) {
                     $eventdata->userto = $DB->get_record('user', array('id' => $eventdata->userto->id));
                     }
                     
                     //after how long inactive should the user be considered logged off?
                     if (isset($CFG->block_online_users_timetosee)) {
                     $timetoshowusers = $CFG->block_online_users_timetosee * 60;
                     } else {
                     $timetoshowusers = 300;//5 minutes
                     }
                     
                     // Create the message object
                     $savemessage = new stdClass();
                     $savemessage->useridfrom        = $eventdata->userfrom->id;
                     $savemessage->useridto          = $eventdata->userto->id;
                     $savemessage->subject           = $eventdata->subject;
                     //$savemessage->fullmessage       = $eventdata->fullmessage;
                     $savemessage->fullmessageformat = $eventdata->fullmessageformat;
                     //    $savemessage->fullmessagehtml   = $eventdata->fullmessagehtml;
                     // $savemessage->smallmessage      = $eventdata->smallmessage;
                     
                     $s = new stdClass();
                     $s->sitename = format_string($SITE->shortname, true, array('context' => get_context_instance(CONTEXT_COURSE, SITEID)));
                     $s->url = $CFG->wwwroot.'/message/index.php?user='.$eventdata->userto->id.'&id='.$eventdata->userfrom->id;
                     
                     $emailtagline = get_string_manager()->get_string('emailtagline', 'message', $s, $eventdata->userto->lang);
                     
                     $savemessage->fullmessage ="Asunto:".$eventdata->subject."\n\n". $eventdata->fullmessage."\n\n---------------------------------------------------------------------\n".$emailtagline;
                     $savemessage->fullmessagehtml .= "<br /><br />---------------------------------------------------------------------<br />".$emailtagline;
                     
                $savemessage->smallmessage  =$eventdata->subject."<br /><br />". $eventdata->smallmessage;
                if (!empty($eventdata->notification)) {
                $savemessage->notification = $eventdata->notification;
                } else {
                $savemessage->notification = 0;
                }
                
                if (!empty($eventdata->contexturl)) {
                $savemessage->contexturl = $eventdata->contexturl;
                } else {
                $savemessage->contexturl = null;
                }
                
                if (!empty($eventdata->contexturlname)) {
                $savemessage->contexturlname = $eventdata->contexturlname;
                } else {
                $savemessage->contexturlname = null;
                }
                
                $savemessage->timecreated = time();
                
                // Process the message
                // Store unread message just in case we can not send it
                $messageid = $savemessage->id = $DB->insert_record('message', $savemessage);
                $eventdata->savedmessageid = $savemessage->id;
                
                
                //prevent users from getting popup notifications of messages to themselves (happens with forum notifications)
                if ($eventdata->userfrom->id!=$eventdata->userto->id) {
                $procmessage = new stdClass();
                $procmessage->unreadmessageid = $eventdata->savedmessageid;
                $procmessage->processorid     = 3;
                
                //save this message for later delivery
                $DB->insert_record('message_working', $procmessage);
                }
                
                add_to_log(SITEID, 'message', 'write', 'index.php?user='.$savemessage->useridfrom.'&id='.$savemessage->useridto.'&history=1#m'.$eventdata->savedmessageid, $savemessage->useridfrom);
                
                }
                
                static function save_config($courseid, $data) {
                global $DB;
                                
                quickmail::default_config($courseid);
                
                foreach ($data as $name => $value) {
                $config = new stdClass;
                $config->coursesid = $courseid;
                $config->name = $name;
                $config->value = $value;
                
                $DB->insert_record('block_quickmail_config', $config);
                }
                }
                
                
                
                // Fin de la clase
        }
        function block_quickmail_pluginfile($course, $record, $context, $filearea, $args, $forcedownload) {
         $fs = get_file_storage();
         global $DB;
                        
         list($itemid, $filename) = $args;
         if ($filearea == 'attachment_log') {
         $time = $DB->get_field('block_quickmail_log', 'time', array(
         'id' => $itemid
         ));
         
         if ("{$time}_attachments.zip" == $filename) {
         $path = quickmail::zip_attachments($context, 'log', $itemid);
         send_temp_file($path, 'attachments.zip');
         }
         }
         $params = array(
         'component' => 'block_quickmail',
         'filearea' => $filearea,
         'itemid' => $itemid,
         'filename' => $filename
         );
         
         $instanceid = $DB->get_field('files', 'id', $params);
         
         if (empty($instanceid)) {
         send_file_not_found();
         } else {
         $file = $fs->get_file_by_id($instanceid);
         send_stored_file($file);
         }
         }
                                                                                           
