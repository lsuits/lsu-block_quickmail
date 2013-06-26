<?php

require_once $CFG->libdir . '/formslib.php';

class quickmail_alternate_form extends moodleform {
    function definition() {
        $m =& $this->_form;

        $course = $this->_customdata['course'];

        $m->addElement('header', 'alt_header', $course->fullname);
        $m->addElement('text', 'address', get_string('email'));
        $m->setType('address', PARAM_NOTAGS);
        $m->addRule('address', get_string('missingemail'), 'required', null, 'server');

        $m->addElement('hidden', 'valid', 0);
        $m->setType('valid',PARAM_INT);
        
        $m->addElement('hidden', 'courseid', $course->id);
        $m->setType('courseid',PARAM_INT);
        
        $m->addElement('hidden', 'id', '');
        $m->setType('id',PARAM_INT);
        
        $m->addElement('hidden', 'action', $this->_customdata['action']);
        $m->setType('action',PARAM_ALPHA);

        $buttons = array(
            $m->createElement('submit', 'submit', get_string('savechanges')),
            $m->createElement('cancel')
        );

        $m->addGroup($buttons, 'buttons', '', array(' '), false);

        $m->closeHeaderBefore('buttons');
    }
}
