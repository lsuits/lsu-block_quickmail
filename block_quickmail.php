<?php

// Written at Louisiana State University

require_once($CFG->dirroot . '/blocks/quickmail/lib.php');

class block_quickmail extends block_list {
    function init() {
        $this->title = quickmail::_s('pluginname');
    }

    function applicable_formats() {
        global $USER;
        if(is_siteadmin($USER->id) || has_capability('block/quickmail:myaddinstance', context_system::instance())) {
            return array('site' => true, 'my' => true, 'course-view' => true);
        } else {
            return array('site' => false, 'my' => false, 'course-view' => true);
        }
    }
    function has_config() {
        return true;
    }
    /**
     * Disable multiple instances of this block
     * @return bool Returns false
     */
    function instance_allow_multiple() {
        return false;
    }
    
    function get_content() {
        global $USER, $CFG, $COURSE, $OUTPUT;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        $context = context_course::instance($COURSE->id);

        $config = quickmail::load_config($COURSE->id);
        $permission = has_capability('block/quickmail:cansend', $context);

        $can_send = ($permission or !empty($config['allowstudents']));

        $icon_class = array('class' => 'icon');

        $cparam = array('courseid' => $COURSE->id);

        if ($can_send && $COURSE->id != SITEID) {
            $send_email_str = quickmail::_s('composenew');
            $icon = $OUTPUT->pix_icon('t/email', $send_email_str, 'moodle', $icon_class);
            $send_email = html_writer::link(
                new moodle_url('/blocks/quickmail/email.php', $cparam),
                $icon.$send_email_str
            );
            $this->content->items[] = $send_email;

            $signature_str = quickmail::_s('signature');
            $icon = $OUTPUT->pix_icon('i/edit', $signature_str, 'moodle', $icon_class);
            $signature = html_writer::link(
                new moodle_url('/blocks/quickmail/signature.php', $cparam),
                $icon.$signature_str
            );
            $this->content->items[] = $signature;

            $draft_params = $cparam + array('type' => 'drafts');
            $drafts_email_str = quickmail::_s('drafts');
            $icon = $OUTPUT->pix_icon('i/settings', $drafts_email_str, 'moodle', $icon_class);
            $drafts = html_writer::link(
                new moodle_url('/blocks/quickmail/emaillog.php', $draft_params),
                $icon.$drafts_email_str
            );
            $this->content->items[] = $drafts;

            $history_str = quickmail::_s('history');
            $icon = $OUTPUT->pix_icon('i/settings', $history_str, 'moodle', $icon_class);
            $history = html_writer::link(
                new moodle_url('/blocks/quickmail/emaillog.php', $cparam),
                $icon.$history_str
            );
            $this->content->items[] = $history;

            if (has_capability('block/quickmail:allowalternate', $context)) {
                $alt_str = quickmail::_s('alternate');
                $icon = $OUTPUT->pix_icon('i/edit', $alt_str, 'moodle', $icon_class);
                $alt = html_writer::link(
                    new moodle_url('/blocks/quickmail/alternate.php', $cparam),
                    $icon.$alt_str
                );

                $this->content->items[] = $alt;
            }
            
            if (has_capability('block/quickmail:canconfig', $context)) {
            $config_str = quickmail::_s('config');
            $icon = $OUTPUT->pix_icon('i/settings', $config_str, 'moodle', $icon_class);
            $config = html_writer::link(
                new moodle_url('/blocks/quickmail/config_qm.php', $cparam),
                $icon.$config_str
            );
            $this->content->items[] = $config;
        }


        }

        if((has_capability('block/quickmail:myaddinstance', context_system::instance()) || is_siteadmin($USER->id)) && $COURSE->id == SITEID) {
            $send_adminemail_str = quickmail::_s('sendadmin');
            $icon = $OUTPUT->pix_icon('t/email', $send_adminemail_str, 'moodle', $icon_class);
            $send_adminemail = html_writer::link(
                new moodle_url('/blocks/quickmail/admin_email.php'),
                $icon.$send_adminemail_str
            );
            $this->content->items[] = $send_adminemail;
        } 
        if (is_siteadmin($USER->id) && $COURSE->id == SITEID) {
            $history_str = quickmail::_s('history');
            $icon = $OUTPUT->pix_icon('i/settings', $history_str, 'moodle', $icon_class);
            $history = html_writer::link(
                new moodle_url('/blocks/quickmail/emaillog.php', $cparam),
                $icon.$history_str
            );
            $this->content->items[] = $history;
        }


        return $this->content;
    }
}
