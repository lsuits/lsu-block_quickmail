<?php

// Written at Louisiana State University

require_once($CFG->libdir . '/formslib.php');

class email_form extends moodleform {
    private function reduce_users($in, $user) {
        return $in . '<option value="'.$this->option_value($user).'">'.
               $this->option_display($user).'</option>';
    }

    private function option_display($user) {
        $users_to_groups = $this->_customdata['users_to_groups'];

        if (empty($users_to_groups[$user->id])) {
            $groups = quickmail::_s('no_section');
        } else {
            $only_names = function($group) { return $group->name; };
            $groups = implode(',', array_map($only_names, $users_to_groups[$user->id]));
        }

        return sprintf("%s (%s)", fullname($user), $groups);
    }

    private function option_value($user) {
        $users_to_groups = $this->_customdata['users_to_groups'];
        $users_to_roles = $this->_customdata['users_to_roles'];

        $only_sn = function($role) { return $role->shortname; };

        $roles = implode(',', array_map($only_sn, $users_to_roles[$user->id]));

        // everyone defaults to none
        $roles .= ',none';

        if (empty($users_to_groups[$user->id])) {
            $groups = 0;
        } else {
            $only_id = function($group) { return $group->id; };
            $groups = implode(',', array_map($only_id, $users_to_groups[$user->id]));
            $groups .= ',all';
        }

        return sprintf("%s %s %s", $user->id, $groups, $roles);
    }

    public function definition() {
        global $CFG, $USER, $COURSE, $OUTPUT;

        $mform =& $this->_form;

        $mform->addElement('hidden', 'mailto', '');
        $mform->setType('mailto', PARAM_TEXT);
        
        $mform->addElement('hidden', 'userid', $USER->id);
        $mform->setType('userid',PARAM_INT);
        
        $mform->addElement('hidden', 'courseid', $COURSE->id);
        $mform->setType('courseid', PARAM_INT);
        
        $mform->addElement('hidden', 'type', '');
        $mform->setType('type', PARAM_ALPHA);
        
        $mform->addElement('hidden', 'typeid', 0);
        $mform->setType('typeid', PARAM_INT);

        $role_options = array('none' => quickmail::_s('no_filter'));
        foreach ($this->_customdata['roles'] as $role) {
            $role_options[$role->shortname] = role_get_name($role);
        }

        $group_options = empty($this->_customdata['groups']) ? array() : array(
            'all' => quickmail::_s('all_sections')
        );
        foreach ($this->_customdata['groups'] as $group) {
            $group_options[$group->id] = $group->name;
        }
        $group_options[0] = quickmail::_s('no_section');

        $user_options = array();
        foreach ($this->_customdata['users'] as $user) {
            $user_options[$this->option_value($user)] = $this->option_display($user);
        }

        $links = array();
        $gen_url = function($type) use ($COURSE) {
            $email_param = array('courseid' => $COURSE->id, 'type' => $type);
            return new moodle_url('emaillog.php', $email_param);
        };

        $draft_link = html_writer::link ($gen_url('drafts'), quickmail::_s('drafts'));
        $links[] =& $mform->createElement('static', 'draft_link', '', $draft_link);

        $context = context_course::instance($COURSE->id);
        
        $config = quickmail::load_config($COURSE->id);

        $can_send = (
            has_capability('block/quickmail:cansend', $context) or
            !empty($config['allowstudents'])
        );

        if ($can_send) {
            $history_link = html_writer::link($gen_url('log'), quickmail::_s('history'));
            $links[] =& $mform->createElement('static', 'history_link', '', $history_link);
        }

        $mform->addGroup($links, 'links', '&nbsp;', array(' | '), false);

        $req_img = html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('req'), 'class' => 'req', 'alt' => ''));

        $table = new html_table();
        $table->attributes['class'] = 'emailtable';

        //$selected_required_label = new html_table_cell();
        //$selected_required_label->text = html_writer::tag('strong',
        //    quickmail::_s('selected') . $req_img, array('class' => 'required'));

        
        // DWE -> NON REQUIRED VERSION
        $selected_label = new html_table_cell();
        $selected_label->text = html_writer::tag('strong',
            quickmail::_s('selected') . " "/*$req_img*/);
        
        $role_filter_label = new html_table_cell();
        $role_filter_label->colspan = "2";
        $role_filter_label->text = html_writer::tag('div',
            quickmail::_s('role_filter'), array('class' => 'object_labels'));

        $select_filter = new html_table_cell();
        $select_filter->text = html_writer::tag('select',
            array_reduce($this->_customdata['selected'], array($this, 'reduce_users'), ''),
            array('id' => 'mail_users', 'multiple' => 'multiple', 'size' => 30));

        $embed = function ($text, $id) {
            return html_writer::tag('p',
                html_writer::empty_tag('input', array(
                    'value' => $text, 'type' => 'button', 'id' => $id
                ))
            );
        };

        $embed_quick = function ($text) use ($embed) {
            return $embed(quickmail::_s($text), $text);
        };

        $center_buttons = new html_table_cell();
        $center_buttons->text = (
            $embed($OUTPUT->larrow() . ' ' . quickmail::_s('add_button'), 'add_button') .
            $embed(quickmail::_s('remove_button') . ' ' . $OUTPUT->rarrow(), 'remove_button') .
            $embed_quick('add_all') .
            $embed_quick('remove_all')
        );

        $filters = new html_table_cell();
        $filters->text = html_writer::tag('div',
            html_writer::select($role_options, '', 'none', null, array('id' => 'roles'))
        ) . html_writer::tag('div',
            quickmail::_s('potential_sections'),
            array('class' => 'object_labels')
        ) . html_writer::tag('div',
            html_writer::select($group_options, '', 'all', null,
            array('id' => 'groups', 'multiple' => 'multiple', 'size' => 5))
        ) . html_writer::tag('div',
            quickmail::_s('potential_users'),
            array('class' => 'object_labels')
        ) . html_writer::tag('div',
            html_writer::select($user_options, '', '', null,
            array('id' => 'from_users', 'multiple' => 'multiple', 'size' => 20))
        );

        
        // DWE -> NON REQUIRED VERSION
        $table->data[] = new html_table_row(array($selected_label, $role_filter_label));

        
        //$table->data[] = new html_table_row(array($selected_required_label, $role_filter_label));
        $table->data[] = new html_table_row(array($select_filter, $center_buttons, $filters));

        if (has_capability('block/quickmail:allowalternate', $context)) {
            $alternates = $this->_customdata['alternates'];
        } else {
            $alternates = array();
        }

        if (empty($alternates)) {
            $mform->addElement('static', 'from', quickmail::_s('from'), $USER->email);
        } else {
            $options = array(0 => $USER->email) + $alternates;
            $mform->addElement('select', 'alternateid', quickmail::_s('from'), $options);
        }

        $mform->addElement('static', 'selectors', '', html_writer::table($table));

        // DWE -> how would I go about validating multiple email addresses
        // https://tracker.moodle.org/browse/MDL-20318
        // https://moodle.org/mod/forum/discuss.php?d=109235
        
        
        $mform->addElement('text', 'additional_emails', 'Additional Email Addresses',array('style'=>'width: 50%;'));
        $mform->setType('additional_emails', PARAM_TEXT);                
                
        $mform->addElement(
            'filemanager', 'attachments', quickmail::_s('attachment'),
            null, array('subdirs' => 1, 'accepted_types' => '*')
        );

        $mform->addElement('text', 'subject', quickmail::_s('subject'));
        $mform->setType('subject', PARAM_TEXT);
        $mform->addRule('subject', null, 'required');

        $mform->addElement('editor', 'message_editor', quickmail::_s('message'),
            null, $this->_customdata['editor_options']);

        $options = $this->_customdata['sigs'] + array(-1 => 'No '. quickmail::_s('sig'));
        $mform->addElement('select', 'sigid', quickmail::_s('signature'), $options);

        $radio = array(
            $mform->createElement('radio', 'receipt', '', get_string('yes'), 1),
            $mform->createElement('radio', 'receipt', '', get_string('no'), 0)
        );

        $mform->addGroup($radio, 'receipt_action', quickmail::_s('receipt'), array(' '), false);
        $mform->addHelpButton('receipt_action', 'receipt', 'block_quickmail');
        $mform->setDefault('receipt', !empty($config['receipt']));

        $buttons = array();
        $buttons[] =& $mform->createElement('submit', 'send', quickmail::_s('send_email'));
        $buttons[] =& $mform->createElement('submit', 'draft', quickmail::_s('save_draft'));
        $buttons[] =& $mform->createElement('cancel');

        $mform->addGroup($buttons, 'buttons', quickmail::_s('actions'), array(' '), false);
    }
}
