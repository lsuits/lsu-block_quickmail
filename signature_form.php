<?php

// Written at Louisiana State University

require_once($CFG->libdir . '/formslib.php');

class signature_form extends moodleform {
    public function definition() {
        global $USER;

        $mform =& $this->_form;

        $mform->addElement('hidden', 'courseid', '');
        $mform->setType('courseid',PARAM_INT);
        
        $mform->addElement('hidden', 'id', '');
        $mform->setType('id',PARAM_INT);
        
        $mform->addElement('hidden', 'userid', $USER->id);
        $mform->setType('userid',PARAM_INT);
        
        $mform->addElement('text', 'title', quickmail::_s('title'));
        $mform->setType('title',PARAM_TEXT);
        
        $mform->addElement('editor', 'signature_editor', quickmail::_s('sig'),
            null, $this->_customdata['signature_options']);
        $mform->setType('signature_editor', PARAM_RAW);
        $mform->addElement('checkbox', 'default_flag', quickmail::_s('default_flag'));

        $buttons = array(
            $mform->createElement('submit', 'save', get_string('savechanges')),
            $mform->createElement('submit', 'delete', get_string('delete')),
            $mform->createElement('cancel')
        );

        $mform->addGroup($buttons, 'buttons', quickmail::_s('actions'), array(' '), false);
        $mform->addRule('title', null, 'required', null, 'client');
    }
}
