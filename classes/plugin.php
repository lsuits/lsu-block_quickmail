<?php

use \block_quickmail\exceptions\authorization_exception;

class block_quickmail_plugin {

    public static $name = 'block_quickmail';

    ////////////////////////////////////////////////////
    ///
    ///  LOCALIZATION
    ///  
    ////////////////////////////////////////////////////

    /**
     * Shortcut for get_string()
     * 
     * @param  string $key
     * @param  mixed $a  optional attributes
     * @return string
     */
    public static function _s($key, $a = null) {
        return self::get_block_string($key, $a);
    }

    /**
     * Returns a lang string for this plugin
     * 
     * @param  string $key
     * @param  mixed $a  optional attributes
     * @return string
     */
    private static function get_block_string($key, $a = null) {
        return get_string($key, self::$name, $a);
    }

    ////////////////////////////////////////////////////
    ///
    ///  HELPERS
    ///  
    ////////////////////////////////////////////////////

    /**
     * Returns a trimmed, shortened, "preview" string with appendage and default if no content
     * 
     * @param  string  $string     the string to be previewed
     * @param  int     $length     number of characters to be displayed
     * @param  string  $appendage  a string to be appended if string is cut off
     * @param  string  $default    default string to be returned is no string is given
     * @return string
     */
    public static function render_preview_string($string, $length, $appendage = '...', $default = '--') {
        $string = trim($string);

        if ( ! $string) {
            return $default;
        }

        if (strlen($string) > $length) {
            $string = wordwrap($string, $length);
            $string = explode("\n", $string, 2);
            $string = $string[0] . $appendage;
        }

        return $string;
    }

    ////////////////////////////////////////////////////
    ///
    ///  FILE ATTACHMENTS
    ///  
    ////////////////////////////////////////////////////

    public static function process_attachments($context, $message, $table, $id) {
        $attachments = '';
        $filename = '';

        if (empty($email->attachment)) {
            return $attachments;
        }

        $fs = get_file_storage();

        $tree = $fs->get_area_tree(
            $context->id, 'block_quickmail',
            'attachment_' . $table, $id, 'id'
        );

        $base_url = "/$context->id/block_quickmail/attachment_{$table}/$id";

        /**
         * @param string $filename name of the file for which we are generating a download link
         * @param string $text optional param sets the link text; if not given, filename is used
         * @param bool $plain if itrue, we will output a clean url for plain text email users
         *
         */
        $gen_link = function ($filename, $text = '', $plain=false) use ($base_url) {
            if (empty($text)) {
                $text = $filename;
            }
            $url = new moodle_url('/pluginfile.php', array(
                'forcedownload' => 1,
                'file' => "/$base_url/$filename"
            ));

            //to prevent double encoding of ampersands in urls for our plaintext users,
            //we use the out() method of moodle_url
            //@see http://phpdocs.moodle.org/HEAD/moodlecore/moodle_url.html
            if($plain){
                return $url->out(false);    
            }

            return html_writer::link($url, $text);
        };



        $link = $gen_link("{$email->time}_attachments.zip", self::_s('download_all'));

        //get a plain text version of the link
        //by calling gen_link with @param $plain set to true
        $tlink = $gen_link("{$email->time}_attachments.zip", '', true);

        $attachments .= "\n<br/>-------\n<br/>";
        $attachments .= self::_s('moodle_attachments', $link);
        $attachments .= "\n<br/>".$tlink;
        $attachments .= "\n<br/>-------\n<br/>";
        $attachments .= self::_s('qm_contents') . "\n<br />";

        return $attachments . self::flatten_subdirs($tree, $gen_link);
    }




    public static function zip_attachments($context, $id) {
        global $CFG, $USER;

        $base_path = "block_quickmail/{$USER->id}";
        $moodle_base = "$CFG->tempdir/$base_path";

        if (!file_exists($moodle_base)) {
            mkdir($moodle_base, $CFG->directorypermissions, true);
        }

        $zipname = "attachment.zip";
        $actual_zip = "$moodle_base/$zipname";

        $fs = get_file_storage();
        $packer = get_file_packer();

        $files = $fs->get_area_files(
            $context->id,
            'block_quickmail',
            'attachments',
            $id,
            'id'
        );

        $stored_files = array();
        
        foreach ($files as $file) {
            if ($file->is_directory() and $file->get_filename() == '.') {
                continue;
            }

            $stored_files[$file->get_filepath().$file->get_filename()] = $file;
        }

        $packer->archive_to_pathname($stored_files, $actual_zip);

        return $actual_zip;
    }


    

    //////////////////// DEPRECATED /////////////////////////////
    
    ////////////////////////////////////////////////////
    ///
    ///  CONTEXT
    ///  
    ////////////////////////////////////////////////////

    /**
     * Resolves a context (system or course) based on a given course id
     * 
     * @param string  $type        system|course
     * @param int     $course_id
     * @throws critical_exception
     * @return mixed  context_system|context_course, course
     */
    public static function resolve_context($type = 'system', $course_id = 0) {
        switch ($type) {
            case 'course':
                // if course context is required, make sure we have an id
                if (empty($course_id)) {
                    throw new critical_exception('no_course', $course_id);
                }
                
                // fetch the course
                $course = self::get_valid_course($course_id);

                // fetch the course context
                $context = context_course::instance($course->id);

                // return the context AND course
                return [$context, $course];

                break;
            
            case 'system':
            default:
                // return only the context
                return context_system::instance();
                break;
        }
    }

    /**
     * Fetches a moodle course by id, if unavailable throw exception
     * 
     * @param  int $course_id
     * @return moodle course
     * @throws critical_exception
     */
    public static function get_valid_course($course_id) {
        try {
            $course = get_course($course_id);
        } catch (dml_exception $e) {
            throw new critical_exception('no_course', $course_id);
        }

        return $course;
    }

    /**
     * Throws exception if authenticated user does not have the given permission within the given context
     * 
     * @param  string $permission  cansend|allowalternate|canconfig|myaddinstance
     * @param  object $context
     * @throws authorization_exception
     * @return void
     */
    public static function check_user_permission($permission, $context) {
        if ( ! self::user_has_permission_in_context($permission, $context)) {
            throw new authorization_exception('no_permission');
        }
    }

    /**
     * Reports whether or not the authenticated user has the given permission within the given context
     * 
     * @param  string $permission  cansend|allowalternate|canconfig|myaddinstance
     * @param  object $context
     * @return boolean
     */
    public static function user_has_permission_in_context($permission, $context) {
        // first, check for special cases...
        if ($permission == 'cansend' && block_quickmail_config::block('allowstudents')) {
            return true;
        }

        // finally, check capability
        return has_capability('block/quickmail:' . $permission, $context);
    }

}