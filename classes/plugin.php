<?php

use \block_quickmail\exceptions\authorization_exception;

class block_quickmail_plugin {

    public static $name = 'block_quickmail';

    /**
     * Constructor
     */
    public function __construct() {
        //
    }

    public static function get_db() {
        global $DB;
        
        return $DB;
    }

    public static function get_cfg() {
        global $CFG;
        
        return $CFG;
    }

    ////////////////////////////////////////////////////
    ///
    ///  CONFIGURATION
    ///  
    ////////////////////////////////////////////////////

    /**
     * Returns a config array, or specific value, for the given key (block or course relative)
     * 
     * @param  string  $key
     * @param  int     $course_id   optional, if set, gets specific course configuration
     * @return mixed
     */
    public static function _c($key = '', $course_id = 0) {
        return $course_id ? 
            self::get_course_config($course_id, $key) :
            self::get_block_config($key);
    }

    /**
     * Returns a config array for the given course, and specific key if given
     * 
     * @return array|mixed
     */
    private static function get_course_config($course_id, $key = '') {
        // get this course's config, if any
        $course_config = self::get_db()->get_records_menu('block_quickmail_config', ['coursesid' => $course_id], '', 'name,value');

        // get the master block config
        $block_config = self::get_block_config();
        
        // determine allowstudents for this course
        if ($block_config['allowstudents'] < 0) {
            $course_allow_students = 0;
        } else {
            $course_allow_students = array_key_exists('allowstudents', $course_config) ? 
                $course_config['allowstudents'] : 
                $block_config['allowstudents'];
        }

        // determine default output_channel, if any, for this course
        // NOTE: block-level "all" will default to course-level "message"
        if ($block_config['output_channels_available'] == 'all') {
            $course_default_output_channel = array_key_exists('default_output_channel', $course_config) ? 
                $course_config['default_output_channel'] : 
                'message';
        } else {
            $course_default_output_channel = $block_config['output_channels_available'];
        }

        $config = [
            'allowstudents'             => (int) $course_allow_students,
            'roleselection'             => array_key_exists('roleselection', $course_config) ? $course_config['roleselection'] : $block_config['roleselection'],
            'receipt'                   => array_key_exists('receipt', $course_config) ? $course_config['receipt'] : $block_config['receipt'],
            'prepend_class'             => array_key_exists('prepend_class', $course_config) ? $course_config['prepend_class'] : $block_config['prepend_class'],
            'ferpa'                     => $block_config['ferpa'],
            'downloads'                 => $block_config['downloads'],
            'additionalemail'           => $block_config['additionalemail'],
            'output_channels_available' => $block_config['output_channels_available'],
            'default_output_channel'    => $course_default_output_channel,
            'allowed_user_fields'       => $block_config['allowed_user_fields']
        ];

        return $key ? $config[$key] : $config;
    }

    /**
     * Returns quickmail's block config as array, or optionally a specific setting
     * 
     * @param  string $key
     * @return array|mixed
     */
    private static function get_block_config($key = '') {
        $default_output_channel = get_config('moodle', 'block_quickmail_output_channels_available');

        $config = [
            'allowstudents'             => (int) get_config('moodle', 'block_quickmail_allowstudents'),
            'roleselection'             => get_config('moodle', 'block_quickmail_roleselection'),
            'receipt'                   => (int) get_config('moodle', 'block_quickmail_receipt'),
            'prepend_class'             => get_config('moodle', 'block_quickmail_prepend_class'),
            'ferpa'                     => get_config('moodle', 'block_quickmail_ferpa'),
            'downloads'                 => (int) get_config('moodle', 'block_quickmail_downloads'),
            'additionalemail'           => (int) get_config('moodle', 'block_quickmail_additionalemail'),
            'output_channels_available' => $default_output_channel,
            'default_output_channel'    => $default_output_channel == 'all' ? 'message' : $default_output_channel,
            'allowed_user_fields'       => explode(',', get_config('moodle', 'block_quickmail_allowed_user_fields'))
        ];

        return $key ? $config[$key] : $config;
    }

    /**
     * Returns the user table field names that may be configured to be injected dynamically into messages
     * 
     * @return array
     */
    public static function get_supported_user_fields() {
        return [
            'firstname',
            'middlename',
            'lastname',
            'email',
            'alternatename',
        ];
    }

    /**
     * Returns the supported output channels
     * 
     * @return array
     */
    public static function get_supported_output_channels() {
        return [
            'all',
            'message',
            'email'
        ];
    }

    /**
     * Returns an array of editor options with a given context
     * 
     * @param  object $context
     * @return array
     */
    public static function get_editor_options($context)
    {
        return [
            'trusttext' => true,
            'subdirs' => true,
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            // 'accepted_types' => '*',
            'context' => $context
        ];
    }

    /**
     * Returns an array of filemanager options
     * 
     * @return array
     */
    public static function get_filemanager_options()
    {
        return [
            'subdirs' => 1, 
            'accepted_types' => '*'
        ];
    }

    /**
     * Updates a given course's settings to match the given params
     * 
     * @param  int $course_id
     * @param  array $params
     * @return void
     */
    public static function update_course_config($course_id, $params = [])
    {
        // first, clear out old settings
        self::delete_course_config($course_id);

        // next, iterate over each given param, inserting each record for this course
        foreach ($params as $name => $value) {
            $config = new \stdClass;
            $config->coursesid = $course_id;
            $config->name = $name;
            $config->value = $value;

            self::get_db()->insert_record('block_quickmail_config', $config);
        }
    }

    /**
     * Deletes a given course's settings
     * 
     * @param  int $course_id
     * @return void
     */
    public static function delete_course_config($course_id)
    {
        self::get_db()->delete_records('block_quickmail_config', ['coursesid' => $course_id]);
    }

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
        if ($permission == 'cansend' && self::get_block_config('allowstudents')) {
            return true;
        }

        // finally, check capability
        return has_capability('block/quickmail:' . $permission, $context);
    }

}