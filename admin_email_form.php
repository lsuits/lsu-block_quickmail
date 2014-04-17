<?php

// Written at Louisiana State University

require_once $CFG->libdir . '/formslib.php';

class admin_email_form extends moodleform {

    function definition() {

        $type = optional_param('type', '', PARAM_ALPHA);
        $typeid = optional_param('typeid', 0, PARAM_INT);

        global $CFG, $DB;

        if(!empty($type)) {
            $data = $DB->get_record('block_quickmail_' . $type, array('id' => $typeid));
        }

        $mform =& $this->_form;

        if(!empty($type)) {
            $mform->addElement('text', 'subject', get_string('subject', 'block_admin_email'))->setValue($data->subject);
        } else {
            $mform->addElement('text', 'subject', get_string('subject', 'block_admin_email'));
        }
        $mform->setType('subject', PARAM_TEXT);
        
        $mform->addElement('text', 'noreply', get_string('noreply', 'block_admin_email'));
        $mform->setType('noreply', PARAM_TEXT);
        
        if(!empty($type)) {
            $mform->addElement('editor', 'body',  get_string('body', 'block_admin_email'))->setValue(array('text'=> $data->message));
        } else {
            $mform->addElement('editor', 'body',  get_string('body', 'block_admin_email'));
        }
        $mform->setType('body', PARAM_RAW);

        $buttons = array(
            $mform->createElement('submit', 'send', get_string('send_email', 'block_admin_email')),
            $mform->createElement('cancel', 'cancel', get_string('cancel'))
        );
        $mform->addGroup($buttons, 'actions', '&nbsp;', array(' '), false);

        $mform->addRule('subject', null, 'required', 'client');
        $mform->addRule('noreply', null, 'required', 'client');
        $mform->addRule('body', null, 'required');
    }

    function validation($data, $files) {
        $errors = array();
        foreach(array('subject', 'body', 'noreply') as $field) {
            if(empty($data[$field]))
                $errors[$field] = get_string('email_error_field', 'block_admin_email', $field);
        }
    }
}
