<?php

function block_quickmail_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, $options = []) 
{
    // Check the contextlevel is as expected
    if ($context->contextlevel != CONTEXT_COURSE) {
        send_file_not_found();
    }

    // Make sure the filearea is one of those used by the plugin.
    if ($filearea !== 'attachments') {
        send_file_not_found();
    }

    // Make sure the user is logged in and has access to the module (plugins that are not course modules should leave out the 'cm' part).
    // depending on configuration, allow unauthenticated users to download file
    if ( ! empty(block_quickmail_config::get('downloads'))) {
        require_course_login($course, true, $cm);
    }

    // Check the relevant capabilities - these may vary depending on the filearea being accessed.
    // if ( ! has_capability('block/quickmail:view', $context)) {
    //     return false;
    // }
    
    // extract params through args
    $itemid = array_shift($args);
    $filename = array_pop($args);
    $path = ! count($args)
        ? '/'
        : '/' . implode('/', $args) . '/';

    // get the message from the itemid
    if ( ! $message = \block_quickmail\persistents\message::find_or_null($itemid)) {
        send_file_not_found();
    }

    // handle a request for serving the master zip download (includes all attachments)
    if (strpos($filename, '_attachments.zip') !== false) {
        global $USER;
        
        $zip_name = 'attachments.zip';

        $path = \block_quickmail\filemanager\message_file_handler::zip_attachments_for_user($message, $USER, $zip_name);
        
        send_temp_file($path, $zip_name);
    
    // otherwise, serve the selected file
    } else {
        $fs = get_file_storage();

        $file = $fs->get_file($context->id, 'block_quickmail', $filearea, $itemid, $path, $filename);

        // if the file does not exist
        if ( ! $file) {
            send_file_not_found();
        }

        send_stored_file($file, 86400, 0, $forcedownload); // $options
    }

}
