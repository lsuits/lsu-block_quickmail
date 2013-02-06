<?php
// Modified  at  www.fecyl.com by Barbara Vergara
// Written at Louisiana State University

require_once $CFG->libdir . '/formslib.php';

class config_form extends moodleform {
    public function definition() {
    global $USER,$DB;
    $mform =& $this->_form;
                    
    $reset_link = html_writer::link(
    new moodle_url('/blocks/quickmail/config.php', array(
    'courseid' => $this->_customdata['courseid'],
    'reset' => 1
    )), quickmail::_s('reset')
    );
    $mform->addElement('static', 'reset', '', $reset_link);
    
    $student_select = array(0 => get_string('no'), 1 => get_string('yes'));
    
    $mform->addElement('select', 'allowstudents',
    quickmail::_s('allowstudents'), $student_select);
    
    $roles =& $mform->addElement('select', 'roleselection',
    quickmail::_s('select_roles'), $this->_customdata['roles']);
    
    $roles->setMultiple(true);
    
    $options = array(
    0 => get_string('none'),
    'idnumber' => get_string('idnumber'),
    'shortname' => get_string('shortname')
    );
    
    $mform->addElement('select', 'prepend_class',
    quickmail::_s('prepend_class'), $options);
    
    $mform->addElement('select', 'receipt',
    quickmail::_s('receipt'), $student_select);
    $mform->addElement('text', 'carpeta',  quickmail::_s('Crear_Nueva_carpeta'),array('value'=>''));
    // aqui mostramos las existentes
    $mform->addElement('html','<div id="capa_operaciones"></div>');
    $mas_carpetas = $DB->get_records('block_quickmail_folders', array ('id_curso'=>$this->_customdata['courseid'], 'id_alumno'=>$USER->id), 'nombre ASC', 'nombre,id');
    foreach ($mas_carpetas as $carpeta) 
    {
    $carpetas_existente=array();
    $carpetas_existente[] =& $mform->createElement('text', 'Carpeta['.$carpeta->id.']','Carpetas['.$carpeta->id.']', array('value'=>$carpeta->nombre, 'style'=>'border:none;background-color:#fff'));
    $carpetas_existente[] =& $mform->createElement('button','Modificar_'.$carpeta->id,'Modifcar', array('onclick'=>"modficar_carpeta('".$carpeta->id."')"));
    $carpetas_existente[] =& $mform->createElement('button','Guardar_'.$carpeta->id,'Guardar', array('style'=>'display:none', 'onclick'=>"modificar_carpeta('".$carpeta->id."')"));
    $carpetas_existente[] =& $mform->createElement('button','Borrar_'.$carpeta->id,'Borrar', array('onclick'=>"borrar_carpeta('".$carpeta->id."', '".$carpeta->nombre."','".$this->_customdata['courseid']."')"));
    $mform->addElement('html',	'<div id="capa_operaciones_'.$carpeta->id.'">');
    $mform->addGroup($carpetas_existente, 'Carpetas Existentes', 'Carpetas:', ' ', false);
    $mform->addElement('html',	'</div>');
    
    }
    
    
    $mform->addElement('submit', 'save', get_string('savechanges'));
    $mform->addElement('hidden', 'courseid', $this->_customdata['courseid']);
    
    $mform->addRule('roleselection', null, 'required');
    
    }
}
