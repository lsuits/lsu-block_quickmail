<?php

namespace block_quickmail\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\metadata\provider as metadata_provider;
use core_privacy\local\request\plugin\provider as plugin_provider;
use core_privacy\local\request\user_preference_provider as preference_provider;
use core_privacy\local\request\contextlist;
use core_privacy\local\legacy_polyfill;
use core_privacy\local\request\writer;
use core_privacy\local\request\approved_contextlist;

class provider implements metadata_provider, plugin_provider, preference_provider {

    use legacy_polyfill;

    public static function _get_metadata(collection $collection) {
 
        ////////////////////////////////////
        ///
        /// FILES
        /// 
        //////////////////////////////////// 
        
        $collection->add_subsystem_link(
            'core_files',
            [],
            'privacy:metadata:core_files'
        );

        ////////////////////////////////////
        ///
        /// DATA TABLES
        /// 
        //////////////////////////////////// 

        $collection->add_database_table(
            'block_quickmail_alt_emails',
            [
                'user_id' => 'privacy:metadata:block_quickmail_alt_emails:user_id',
                'email' => 'privacy:metadata:block_quickmail_alt_emails:email',
                'firstname' => 'privacy:metadata:block_quickmail_alt_emails:firstname',
                'lastname' => 'privacy:metadata:block_quickmail_alt_emails:lastname',
            ],
            'privacy:metadata:block_quickmail_alt_emails'
        );

        $collection->add_database_table(
            'block_quickmail_messages',
            [
                'user_id' => 'privacy:metadata:block_quickmail_messages:user_id',
                'subject' => 'privacy:metadata:block_quickmail_messages:subject',
                'body' => 'privacy:metadata:block_quickmail_messages:body',
            ],
            'privacy:metadata:block_quickmail_messages'
        );

        $collection->add_database_table(
            'block_quickmail_msg_recips',
            [
                'user_id' => 'privacy:metadata:block_quickmail_msg_recips:user_id',
            ],
            'privacy:metadata:block_quickmail_msg_recips'
        );

        $collection->add_database_table(
            'block_quickmail_notifs',
            [
                'user_id' => 'privacy:metadata:block_quickmail_notifs:user_id',
                'subject' => 'privacy:metadata:block_quickmail_notifs:subject',
                'body' => 'privacy:metadata:block_quickmail_notifs:body',
            ],
            'privacy:metadata:block_quickmail_notifs'
        );

        $collection->add_database_table(
            'block_quickmail_signatures',
            [
                'user_id' => 'privacy:metadata:block_quickmail_signatures:user_id',
                'title' => 'privacy:metadata:block_quickmail_signatures:title',
                'signature' => 'privacy:metadata:block_quickmail_signatures:signature',
            ],
            'privacy:metadata:block_quickmail_signatures'
        );

        ////////////////////////////////////
        ///
        /// USER PREFERENCES
        /// 
        //////////////////////////////////// 

        $collection->add_user_preference(
            'block_quickmail_preferred_picker',
            'privacy:metadata:preference:block_quickmail_preferred_picker'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param   int           $userid       The user to search.
     * @return  contextlist   $contextlist  The list of contexts used in this plugin.
     */
    public static function _get_contexts_for_userid(int $userid) {
        // get array of course data, keyed by course id
        $courses = enrol_get_users_courses($userid, false, null, null);

        // convert courses to comma-separated list of course ids
        $courseidstring = implode(',', array_keys($courses));

        $sql = 'SELECT c.id
                FROM {context} c
                WHERE c.contextlevel = :coursecontextlevel
                AND c.instanceid IN (' . $courseidstring . ')';
         
        $params = [
            'coursecontextlevel' => CONTEXT_COURSE,
            'userid'             => $userid,
        ];

        $contextlist = new contextlist();
 
        // add course contexts in which this user is enrolled in
        $contextlist->add_from_sql($sql, $params);
        
        $contextlist->add_user_context($userid);
        
        $contextlist->add_system_context();

        return $contextlist;
    }

    /**
     * Export all user data for the specified user, in the specified contexts, using the supplied exporter instance.
     *
     * @param   approved_contextlist    $contextlist    The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        // need to do this
    }
    
    /**
     * Export all user preferences for the plugin.
     *
     * @param   int         $userid The userid of the user whose data is to be exported.
     */
    public static function export_user_preferences(int $userid) {
        $block_quickmail_preferred_picker = get_user_preference('block_quickmail_preferred_picker', null, $userid);
        
        if (null !== $block_quickmail_preferred_picker) {
            writer::export_user_preference('mod_forum', 'block_quickmail_preferred_picker', $block_quickmail_preferred_picker, get_string('picker_style_option_title', 'block_quickmail'));
        }
    }

    public static function delete_data_for_user(approved_contextlist $contextlist) {
        // need to do this
    }

}