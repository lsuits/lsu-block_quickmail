<?php

// Written at  www.fecyl.com by Barbara Vergara

require_once('../../config.php');
require_once('lib.php');

require_login();

$courseid = required_param('courseid', PARAM_INT);
$type = optional_param('type', 'log', PARAM_ALPHA);
$typeid = optional_param('typeid', 0, PARAM_INT);
$action = optional_param('action', null, PARAM_ALPHA);
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$userid = optional_param('userid', $USER->id, PARAM_INT);
$busqueda = optional_param('buscar_email', '', PARAM_ALPHA);

$id_folder= optional_param('id_folder', 10, PARAM_INT);
$de_quien = optional_param('de_quien', 0, PARAM_INT);
$de_donde_vengo =optional_param('de_donde_vengo', '', PARAM_ALPHA);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('no_course', 'block_quickmail', '', $courseid);
}

$context = get_context_instance(CONTEXT_COURSE, $courseid);
if ($type=='foldersmsg')$type="folders_msg";
// Has to be in on of these
if (!in_array($type, array('log', 'drafts','received','folders_msg'))) {
    print_error('not_valid', 'block_quickmail', '', $type);
}

$canimpersonate = has_capability('block/quickmail:canimpersonate', $context);
if (!$canimpersonate and $userid != $USER->id) {
    print_error('not_valid_user', 'block_quickmail');
}

$config = quickmail::load_config($courseid);

$valid_actions = array('delete', 'confirm','received', 'otrasCapetas');

$can_send = has_capability('block/quickmail:cansend', $context);

$proper_permission = ($can_send or !empty($config['allowstudents']));

$can_delete = ($can_send or ($proper_permission and $type == 'drafts'));

// Stops students from tempering with history
if (!$proper_permission or (!$can_delete and in_array($action, $valid_actions))) {
    print_error('no_permission', 'block_quickmail');
}

if (isset($action) and !in_array($action, $valid_actions)) {
    print_error('not_valid_action', 'block_quickmail', '', $action);
}
if (isset($action) and empty($typeid)) {
    print_error('not_valid_typeid', 'block_quickmail', '', $action);
}


//$blockname = quickmail::_s('pluginname');
$blockname = 'Quick-Argo';
if ($type=='folders_msg')
{
    $nombre_carpeta= $DB->get_field('block_quickmail_folders','nombre', array ('id'=>$id_folder));
        $header = quickmail::_s('Carpeta').$nombre_carpeta;
}
else if ($de_donde_vengo=='received' )
    $header = quickmail::_s($de_donde_vengo);
    else 
    $header = quickmail::_s($type);
    
    $PAGE->set_context($context);
    $PAGE->set_course($course);
    $PAGE->navbar->add($blockname);
    $PAGE->navbar->add($header);
    $PAGE->set_title($blockname . ': ' . $header);
    $PAGE->set_heading($blockname . ': ' . $header);
    $PAGE->set_url('/course/view.php', array('id' => $courseid));
    $PAGE->requires->js('/blocks/quickmail/js/aad.js');
    $PAGE->requires->css('/blocks/quickmail/styles.css');
    
    $PAGE->set_pagetype($blockname);
    
     
      switch ($type) {
      case "received":
          $type='log'; 
          $tabla_letra='L';
          $select =" id not in ( SELECT id_email  FROM  mdl_block_quickmail_delete where userid = ". $userid." and id_tabla like '".$tabla_letra."'  )  and  courseid ='".$courseid."'  and (  mailto = ".$userid." OR mailto LIKE '".$userid.",%' OR mailto LIKE '%,".$userid."' OR mailto LIKE '%,".$userid.",%' ) " ;
      break; 
      case "folders_msg":
          $tabla_letra='O';
          $select =" id not in ( SELECT id_email  FROM  mdl_block_quickmail_delete where userid = ". $userid." and id_tabla like '".$tabla_letra."'  ) and courseid='".$courseid."'   ";
          if ($action== "delete") $select .= " and id='".$typeid ."'";
          else $select .=" and id_folder='".$id_folder."' " ;
      break; 
      case "drafts":
          $tabla_letra='D';
          $select =" id not in ( SELECT id_email  FROM  mdl_block_quickmail_delete where userid = ". $userid." and id_tabla like '".$tabla_letra."'  )  and  courseid ='".$courseid."' and userid = ". $userid." " ;
      break; 
      default:
          $params = array('userid' => $userid, 'courseid' => $courseid);
      break; 
      
      }
        $dbtable = 'block_quickmail_' . $type;
        
        $count = $DB->count_records_select($dbtable, $select,$params);
        
            	
    switch ($action) {
        case "received":
            $html = quickmail::list_entries_recibidas($courseid, 'log', $page, $perpage, $userid, $count, $can_delete,$busqueda);
        
        break; 
        case "confirm":
            if (quickmail::cleanup($dbtable, $context->id, $typeid,$de_quien)) {
            
                switch ($de_donde_vengo) {
                    case "received":
                    $redirecccion= array('courseid' => $courseid,'type' => $de_donde_vengo, 'action'=>$de_donde_vengo, 'typeid'=>$userid);	
                    break;
                    case "otrasCapetas": 
                    $redirecccion= array('courseid' => $courseid,'type' => $type, 'action'=>$de_donde_vengo, 'typeid'=>$userid , 'id_folder'=>$id_folder);	
                    break;
                    default:
                    $redirecccion= array('courseid' => $courseid,'type' => $type);
                    break;
            }
            
            $url = new moodle_url('/blocks/quickmail/emaillog.php',$redirecccion);
            redirect($url);
            } else
                print_error('delete_failed', 'block_quickmail', '', $typeid);
        break;
        case "delete":
            $html = quickmail::delete_dialog($courseid, $type, $typeid, $de_quien, $de_donde_vengo, $id_folder);
            $header = quickmail::_s('delete_msg');
        break;
        case "otrasCapetas": 
            $html = quickmail::list_entries_otras($courseid, 'folders_msg', $page, $perpage, $userid, $count, $can_delete,$busqueda, $id_folder);
        break;
        default:
            $html = quickmail::list_entries($courseid, $type, $page, $perpage, $userid, $count, $can_delete, $busqueda);
    }
    
    if($canimpersonate and $USER->id != $userid) {
    $user = $DB->get_record('user', array('id' => $userid));
    $header .= ' for '. fullname($user);
    }
                
    echo $OUTPUT->header();
    $cabecera=$OUTPUT->heading($header);
    //echo $OUTPUT->heading('Quick-Argo');
                
    if($canimpersonate) {
    $sql = "SELECT DISTINCT(l.userid), u.firstname, u.lastname
    FROM {block_quickmail_$type} l,
    {user} u
    WHERE u.id = l.userid AND courseid = ? ORDER BY u.lastname";
    $users = $DB->get_records_sql($sql, array($courseid));
    
    $user_options = array_map(function($user) { return fullname($user); }, $users);
    
    $url = new moodle_url('emaillog.php', array(
    'courseid' => $courseid,
    'type' => $type
    ));
    
    $default_option = array('' => quickmail::_s('select_users'));
    
    $filtro_usuario=$OUTPUT->single_select($url, 'userid', $user_options, $userid, $default_option);
    }
echo html_writer::tag('div',$cabecera,array('class'=>'fondo_color_izqda'));
echo html_writer::tag('div',$filtro_usuario,array('class'=>'fondo_color_drcha'));
echo html_writer::tag('div','       ',array('style'=>'clear:both'));
                // aqui, no falla
    if(empty($count)) {
    echo $OUTPUT->notification(quickmail::_s('no_'.$type));
    
    echo $OUTPUT->continue_button('/blocks/quickmail/email.php?courseid='.$courseid);
    
    echo $OUTPUT->footer();
    exit;
    }
    echo html_writer::tag('div',$html);
                
                echo $OUTPUT->footer();
                
