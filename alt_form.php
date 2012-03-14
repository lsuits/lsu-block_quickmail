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
        $m->addElement('hidden', 'courseid', $course->id);
        $m->addElement('hidden', 'id', '');
        $m->addElement('hidden', 'action', $this->_customdata['action']);

        $buttons = array(
            $m->createElement('submit', 'submit', get_string('savechanges')),
            $m->createElement('cancel')
        );

        $m->addGroup($buttons, 'buttons', '', array(' '), false);

        $m->closeHeaderBefore('buttons');
    }
}
