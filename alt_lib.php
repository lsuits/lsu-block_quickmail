<?php

interface quickmail_alternate_actions {
    const VIEW = 'view';
    const DELETE = 'delete';
    const INTERACT = 'interact';
    const CONFIRMED = 'confirmed';
    const INFORMATION = 'inform';
    const VERIFY = 'verify';
}

abstract class quickmail_alternate implements quickmail_alternate_actions {

    private static function base_url($courseid, $additional= array()) {
        $params = array('courseid' => $courseid) + $additional;
        return new moodle_url('/blocks/quickmail/alternate.php', $params);
    }

    public static function get($course) {
        global $DB;

        $params = array('courseid' => $course->id);
        return $DB->get_records('block_quickmail_alternate', $params, 'valid DESC');
    }

    public static function get_one($id) {
        global $DB;

        $params = array('id' => $id);
        return $DB->get_record('block_quickmail_alternate', $params, '*', MUST_EXIST);
    }

    public static function delete($course, $id) {
        global $OUTPUT, $DB;

        $email = self::get_one($id);

        $confirm_url = self::base_url($course->id, array(
            'id' => $email->id, 'action' => self::CONFIRMED
        ));

        $cancel_url = self::base_url($course->id);

        return $OUTPUT->confirm(quickmail::_s('sure', $email), $confirm_url, $cancel_url);
    }

    public static function confirmed($course, $id) {
        global $DB;

        $DB->delete_records('block_quickmail_alternate', array('id' => $id));

        return redirect(self::base_url($course->id, array('flash' => 1)));
    }

    public static function verify($course, $id) {
        global $DB, $OUTPUT;

        $entry = self::get_one($id);

        $value = optional_param('key', null, PARAM_TEXT);
        $userid = optional_param('activator', null, PARAM_INT);

        $params = array(
            'instance' => $course->id,
            'value' => $value,
            'userid' => $userid,
            'script' => 'blocks/quickmail'
        );

        $back_url = self::base_url($course->id);

        // Pass through already valid entries
        if ($entry->valid) {
            redirect($back_url);
        }

        // Verify key
        if (empty($value) or !$key = $DB->get_record('user_private_key', $params)) {
            $reactivate = self::base_url($course->id, array(
                'id' => $id, 'action' => self::INFORMATION
            ));

            $html = $OUTPUT->notification(quickmail::_s('entry_key_not_valid', $entry));
            $html .= $OUTPUT->continue_button($reactivate);
            return $html;
        }

        // One at a time...They can resend the link if they want
        delete_user_key('blocks/quickmail', $userid);

        $entry->valid = 1;
        $DB->update_record('block_quickmail_alternate', $entry);

        $entry->course = $course->fullname;

        $html = $OUTPUT->notification(quickmail::_s('entry_activated', $entry), 'notifysuccess');
        $html .= $OUTPUT->continue_button($back_url);

        return $html;
    }

    public static function inform($course, $id) {
        global $DB, $OUTPUT, $USER;

        $entry = self::get_one($id);

        // No restriction
        // Valid forever
        $value = get_user_key('blocks/quickmail', $USER->id, $course->id);

        $url = self::base_url($course->id);

        $approval_url = self::base_url($course->id, array(
            'id' => $id, 'action' => self::VERIFY,
            'activator' => $USER->id, 'key' => $value
        ));

        $a = new stdClass;
        $a->address = $entry->address;
        $a->url = html_writer::link($approval_url, $approval_url->out());
        $a->course = $course->fullname;
        $a->fullname = fullname($USER);

        $from = quickmail::_s('alternate_from');
        $subject = quickmail::_s('alternate_subject');
        $html_body = quickmail::_s('alternate_body', $a);
        $body = strip_tags($html_body);

        // Send email
        $user = clone($USER);
        $user->email = $entry->address;
        $user->firstname = quickmail::_s('pluginname');
        $user->lastname = quickmail::_s('alternate');

        $result = email_to_user($user, $from, $subject, $body, $html_body);

        // Add to log
        add_to_log($course->id, 'quickmail', 'add', $url->out(),
            quickmail::_s('alternate') . ' ' . $entry->address);

        $html = $OUTPUT->box_start();

        if ($result) {
            $html .= $OUTPUT->notification(quickmail::_s('entry_saved', $entry), 'notifysuccess');
            $html .= html_writer::tag('p', quickmail::_s('entry_success', $entry));
        } else {
            $html .= $OUTPUT->notification(quickmail::_s('entry_failure', $entry));
        }

        $html .= $OUTPUT->continue_button($url);
        $html .= $OUTPUT->box_end();

        return $html;
    }

    public static function interact($course, $id) {
        $form = new quickmail_alternate_form(null, array(
            'course' => $course, 'action' => self::INTERACT
        ));

        if ($form->is_cancelled()) {
            redirect(self::base_url($course->id));
        } else if ($data = $form->get_data()) {
            global $DB;

            // Check if email exists in this course
            $older = $DB->get_record('block_quickmail_alternate', array(
                'address' => $data->address, 'courseid' => $data->courseid
            ));

            if ($older) {
                $data->id = $older->id;
                $data->valid = $older->valid;
            } else if (!empty($data->id)) {
                // Changed address?
                if ($data->valid) {
                    $older = self::get_one($id);

                    $valid = $older->address != $data->address ? 0 : 1;

                    $data->valid = $valid;
                }

                $DB->update_record('block_quickmail_alternate', $data);
            } else {
                unset($data->id);
                $data->id = $DB->insert_record('block_quickmail_alternate', $data);
            }

            $action = $data->valid ? self::VERIFY : self::INFORMATION;

            redirect(self::base_url($course->id, array(
                'action' => $action, 'id' => $data->id
            )));
        }

        if ($id) {
            $form->set_data(self::get_one($id));
        }

        // MDL-31677
        $reflect = new ReflectionClass('quickmail_alternate_form');
        $form_field = $reflect->getProperty('_form');
        $form_field->setAccessible(true);

        return $form_field->getValue($form)->toHtml();
    }

    public static function view($course) {
        global $OUTPUT;

        $alternates = self::get($course);

        $new_url = self::base_url($course->id, array('action' => self::INTERACT));

        if (empty($alternates)) {

            $html = $OUTPUT->notification(quickmail::_s('no_alternates', $course));
            $html .= $OUTPUT->continue_button($new_url);
            return $html;
        }

        $table = new html_table();
        $table->head = array(
            get_string('email'),
            quickmail::_s('valid'),
            get_string('action')
        );

        $approval = array(quickmail::_s('waiting'), quickmail::_s('approved'));

        $icons = array(
            self::INTERACT => $OUTPUT->pix_icon('i/edit', get_string('edit')),
            self::DELETE => $OUTPUT->pix_icon('i/cross_red_big', get_string('delete'))
        );

        foreach ($alternates as $email) {
            $edit_url = self::base_url($course->id, array(
                'action' => self::INTERACT, 'id' => $email->id
            ));

            $edit = html_writer::link($edit_url, $icons[self::INTERACT]);

            $delete_url = self::base_url($course->id, array(
                'action' => self::DELETE, 'id' => $email->id
            ));

            $delete = html_writer::link($delete_url, $icons[self::DELETE]);

            $row = array(
                $email->address,
                $approval[$email->valid],
                implode(' | ', array($edit, $delete))
            );

            $table->data[] = new html_table_row($row);
        }

        $new_link = html_writer::link($new_url, quickmail::_s('alternate_new'));

        $html = html_writer::tag('div', $new_link, array('class' => 'new_link'));
        $html .= $OUTPUT->box_start();
        $html .= html_writer::table($table);
        $html .= $OUTPUT->box_end();
        return $html;
    }
}
