<?php

// Written at Louisiana State University

abstract class lsu_dev {
    abstract static function pluginname();

    static function is_lsu() {
        global $CFG;
        return isset($CFG->is_lsu) and $CFG->is_lsu;
    }

    public static function _s($key, $a = null) {
        $class = get_called_class();

        return get_string($key, $class::pluginname(), $a);
    }

    /**
     * Shorten locally called string even more
     */
    public static function gen_str() {
        $class = get_called_class();

        return function ($key, $a = null) use ($class) {
            return get_string($key, $class::pluginname(), $a);
        };
    }
}

abstract class quickmail extends lsu_dev {
    static function pluginname() {
        return 'block_quickmail';
    }

    static function format_time($time) {
        return date("l, d F Y, h:i A", $time);
    }

    static function cleanup($table, $itemid) {
        global $DB;

        // Clean up the files associated with this email
        // Fortunately, they are only db references, but
        // they shouldn't be there, nonetheless.
        $params = array('component' => $table, 'itemid' => $itemid);

        $result = (
            $DB->delete_records('files', $params) and
            $DB->delete_records($table, array('id' => $itemid))
        );

        return $result;
    }

    static function history_cleanup($itemid) {
        return quickmail::cleanup('block_quickmail_log', $itemid);
    }

    static function draft_cleanup($itemid) {
        return quickmail::cleanup('block_quickmail_drafts', $itemid);
    }

    static function process_attachments($context, $email, $table, $id) {
        global $CFG, $USER;

        $base_path = "temp/block_quickmail/{$USER->id}";
        $moodle_base = "$CFG->dataroot/$base_path";

        if (!file_exists($moodle_base)) {
            mkdir($moodle_base, $CFG->directorypermissions, true);
        }

        $zipname = $zip = $actual_zip = '';

        if (!empty($email->attachment)) {
            $zipname = "attachment.zip";
            $zip = "$base_path/$zipname";
            $actual_zip = "$moodle_base/$zipname";

            $packer = get_file_packer();
            $fs = get_file_storage();

            $files = $fs->get_area_files(
                $context->id,
                'block_quickmail_'.$table, 
                'attachment', 
                $id, 
                'id'
            );

            $stored_files = array();

            foreach ($files as $file) {
                if($file->is_directory() and $file->get_filename() == '.')
                    continue;

                $stored_files[$file->get_filepath().$file->get_filename()] = $file;
            }

            $packer->archive_to_pathname($stored_files, $actual_zip);
        }

        return array($zipname, $zip, $actual_zip);
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

    static function filter_roles($user_roles, $master_roles) {
        return array_uintersect($master_roles, $user_roles, function($a, $b) {
            return strcmp($a->shortname, $b->shortname);
        });
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

            $config = array(
                'allowstudents' => $allowstudents,
                'roleselection' => $roleselection
            );
        }

        return $config;
    }

    static function default_config($courseid) {
        global $DB;

        $params = array('coursesid' => $courseid);
        $DB->delete_records('block_quickmail_config', $params);
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

    function delete_dialog($courseid, $type, $typeid) {
        global $CFG, $DB, $USER, $OUTPUT;

        $email = $DB->get_record('block_quickmail_'.$type, array('id' => $typeid));

        if (empty($email))
            print_error('not_valid_typeid', 'block_quickmail', '', $typeid);

        $params = array('courseid' => $courseid, 'type' => $type);
        $yes_params = $params + array('typeid' => $typeid, 'action' => 'confirm');

        $optionyes = new moodle_url('/blocks/quickmail/emaillog.php', $yes_params);
        $optionno = new moodle_url('/blocks/quickmail/emaillog.php', $params);

        $table = new html_table();
        $table->head = array(get_string('date'), quickmail::_s('subject'));
        $table->data = array(
            new html_table_row(array(
                new html_table_cell(quickmail::format_time($email->time)),
                new html_table_cell($email->subject))
            )
        );

        $msg = quickmail::_s('delete_confirm', html_writer::table($table));

        $html = $OUTPUT->confirm($msg, $optionyes, $optionno);
        return $html;
    }

    function list_entries($courseid, $type, $page, $perpage, $userid, $count, $can_delete) {
        global $CFG, $DB, $OUTPUT;

        $dbtable = 'block_quickmail_'.$type;

        $table = new html_table();

        $params = array('courseid' => $courseid, 'userid' => $userid);
        $logs = $DB->get_records($dbtable, $params,
            'time DESC', '*', $page * $perpage, $perpage * ($page + 1));

        $table->head= array(get_string('date'), quickmail::_s('subject'),
            quickmail::_s('attachment'), get_string('action'));

        $table->data = array();

        foreach ($logs as $log) {
            $date = quickmail::format_time($log->time);
            $subject = $log->subject;
            $attachments = $log->attachment;

            $params = array(
                'courseid' => $log->courseid,
                'type' => $type,
                'typeid' => $log->id
            );

            $actions = array();

            $open_link = html_writer::link(
                new moodle_url('/blocks/quickmail/email.php', $params),
                $OUTPUT->pix_icon('i/search', 'Open Email')
            );
            $actions[] = $open_link;

            if ($can_delete) {
                $delete_link = html_writer::link (
                    new moodle_url('/blocks/quickmail/emaillog.php',
                        $params + array('action' => 'delete')
                    ),
                    $OUTPUT->pix_icon("i/cross_red_big", "Delete Email")
                );

                $actions[] = $delete_link;
            }

            $action_links = implode(' ', $actions);

            $table->data[] = array($date, $subject, $attachments, $action_links);
        }

        $paging = $OUTPUT->paging_bar($count, $page, $perpage,
            '/blocks/quickmail/emaillog.php?courseid='.$courseid);

        $html = $paging;
        $html .= html_writer::table($table);
        $html .= $paging;
        return $html;
    }
}

