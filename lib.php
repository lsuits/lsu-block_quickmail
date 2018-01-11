<?php

/**
 * File serving.
 *
 * @param stdClass $course The course object.
 * @param stdClass $cm The cm object.
 * @param context $context The context object.
 * @param string $filearea The file area.
 * @param array $args List of arguments.
 * @param bool $forcedownload Whether or not to force the download of the file.
 * @param array $options Array of options.
 * @return void|false
 */
function block_quickmail_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    if ( ! empty(block_quickmail_plugin::_c('downloads'))) {
        require_course_login($course, true, $cm);
    }

    list($itemid, $filename) = $args;

    if ($filearea == 'attachments') {
        $time = $DB->get_field('block_quickmail_messages', 'timecreated', [
            'id' => $itemid
        ]);

        if ("{$time}_attachments.zip" == $filename) {
            $path = block_quickmail_plugin::zip_attachments($context, $itemid);
            send_temp_file($path, 'attachments.zip');
        }
    }

    $params = array(
        'component' => 'block_quickmail',
        'filearea' => $filearea,
        'itemid' => $itemid,
        'filename' => $filename
    );

    $instanceid = $DB->get_field('files', 'id', $params);

    if (empty($instanceid)) {
        send_file_not_found();
    } else {
        $file = $fs->get_file_by_id($instanceid);
        send_stored_file($file, null, 0, $forcedownload);
    }
}