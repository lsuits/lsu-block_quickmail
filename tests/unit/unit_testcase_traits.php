<?php

////////////////////////////////////////////////////
///
///  GENERAL TEST HELPERS
/// 
////////////////////////////////////////////////////

trait unit_testcase_has_general_helpers {

    public function dd($thing)
    {
        var_dump($thing);
        die;
    }

    public function get_user_ids_from_user_array(array $users, $as_string = false)
    {
        $user_ids = array_map(function($user) {
            return $user->id;
        }, $users);

        return ! $as_string
            ? $user_ids
            : implode($user_ids, ',');
    }

}

////////////////////////////////////////////////////
///
///  COURSE SET UP HELPERS
/// 
////////////////////////////////////////////////////

trait unit_testcase_sets_up_courses {

    /**
     * Creates a course within a category with 1 teacher, 4 students
     * 
     * @return array  course, user_teacher, students[]
     */
    public function setup_course_with_teacher_and_students()
    {
        // create a course category
        $category = $this->getDataGenerator()->create_category();

        // create a course
        $course = $this->getDataGenerator()->create_course();

        // create a user (teacher)
        $user_teacher = $this->getDataGenerator()->create_user([
            'email' => 'teacher@example.com', 
            'username' => 'teacher'
        ]);

        // create a user (student1)
        $user_student1 = $this->getDataGenerator()->create_user([
            'email' => 'student1@example.com', 
            'username' => 'student1'
        ]);

        // create a user (student2)
        $user_student2 = $this->getDataGenerator()->create_user([
            'email' => 'student2@example.com', 
            'username' => 'student2'
        ]);

        // create a user (student3)
        $user_student3 = $this->getDataGenerator()->create_user([
            'email' => 'student3@example.com', 
            'username' => 'student3'
        ]);

        // create a user (student4)
        $user_student4 = $this->getDataGenerator()->create_user([
            'email' => 'student4@example.com', 
            'username' => 'student4'
        ]);

        // enrol the teacher in the course
        $this->getDataGenerator()->enrol_user($user_teacher->id, $course->id, 4, 'manual');

        // enrol the students in the course
        $this->getDataGenerator()->enrol_user($user_student1->id, $course->id, 5, 'manual');
        $this->getDataGenerator()->enrol_user($user_student2->id, $course->id, 5, 'manual');
        $this->getDataGenerator()->enrol_user($user_student3->id, $course->id, 5, 'manual');
        $this->getDataGenerator()->enrol_user($user_student4->id, $course->id, 5, 'manual');


        return [
            $course,
            $user_teacher,
            [
                $user_student1,
                $user_student2,
                $user_student3,
                $user_student4
            ]
        ];
    }

    public function assign_configuration_to_course($course, $override_params)
    {
        global $DB;

        $params = $this->get_course_config_params($override_params);

        // iterate over each given param, inserting each record for this course
        foreach ($params as $name => $value) {
            var_dump($name);

            $config = new \stdClass;
            $config->coursesid = $course->id;
            $config->name = $name;
            $config->value = $value;

            $DB->insert_record('block_quickmail_config', $config);
        }
    }

    public function get_course_config_params(array $override_params)
    {
        $default_output_channel = get_config('moodle', 'block_quickmail_output_channels_available');

        $default_default_output_channel = $default_output_channel == 'all' ? 'message' : $default_output_channel;

        $supported_user_fields_string = implode(',', block_quickmail_plugin::get_supported_user_fields());

        $params = [];

        $params['allowstudents'] = array_key_exists('allowstudents', $override_params) ? $override_params['allowstudents'] : (int) get_config('moodle', 'block_quickmail_allowstudents');
        $params['roleselection'] = array_key_exists('roleselection', $override_params) ? $override_params['roleselection'] : get_config('moodle', 'block_quickmail_roleselection');
        $params['receipt'] = array_key_exists('receipt', $override_params) ? $override_params['receipt'] : (int) get_config('moodle', 'block_quickmail_receipt');
        $params['prepend_class'] = array_key_exists('prepend_class', $override_params) ? $override_params['prepend_class'] : get_config('moodle', 'block_quickmail_prepend_class');
        $params['ferpa'] = array_key_exists('ferpa', $override_params) ? $override_params['ferpa'] : get_config('moodle', 'block_quickmail_ferpa');
        $params['downloads'] = array_key_exists('downloads', $override_params) ? $override_params['downloads'] : (int) get_config('moodle', 'block_quickmail_downloads');
        $params['additionalemail'] = array_key_exists('additionalemail', $override_params) ? $override_params['additionalemail'] : (int) get_config('moodle', 'block_quickmail_additionalemail');
        $params['output_channels_available'] = array_key_exists('output_channels_available', $override_params) ? $override_params['output_channels_available'] : $default_output_channel;
        $params['default_output_channel'] = array_key_exists('default_output_channel', $override_params) ? $override_params['default_output_channel'] : $default_default_output_channel;
        $params['allowed_user_fields'] = array_key_exists('allowed_user_fields', $override_params) ? $override_params['allowed_user_fields'] : $supported_user_fields_string;

        return $params;
    }

}

////////////////////////////////////////////////////
///
///  COMPOSE FORM SUBMISSION HELPERS
///  
///  needs:
///   # unit_testcase_has_general_helpers
/// 
////////////////////////////////////////////////////

trait unit_testcase_submits_compose_message_form {

    // @TODO : make send_at_timestamp work properly!
    // @TODO : convert additional_emails override to an array of emails
    public function get_compose_message_form_submission(array $to_users, $output_channel = 'email', $send_at_timestamp = 0, array $override_params = [])
    {
        $params = $this->get_compose_message_form_submission_params($override_params);

        $form_data = (object)[];

        $form_data->alternate_email_id = $params['alternate_email_id']; // default: '0'
        $form_data->mailto_ids = $this->get_user_ids_from_user_array($to_users, true);
        $form_data->subject = $params['subject']; // default: 'this is the subject'
        $form_data->additional_emails = $params['additional_emails']; // default: ''
        $form_data->message_editor = [
            'text' => $params['body'], // default: 'this is a very important message body'
            'format' => '1',
            'itemid' => 881830772
        ];
        $form_data->attachments = 0;
        $form_data->signature_id = $params['signature_id']; // default: '0'
        $form_data->output_channel = $output_channel;
        $form_data->to_send_at = $send_at_timestamp;
        $form_data->receipt = $params['receipt']; // default: '0'
        $form_data->send = 'Send Message';

        return $form_data;
    }

    public function get_compose_message_form_submission_params(array $override_params)
    {
        $params = [];

        $params['alternate_email_id'] = array_key_exists('alternate_email_id', $override_params) ? $override_params['alternate_email_id'] : '0';
        $params['additional_emails'] = array_key_exists('additional_emails', $override_params) ? $override_params['additional_emails'] : '';
        $params['subject'] = array_key_exists('subject', $override_params) ? $override_params['subject'] : 'this is the subject';
        $params['body'] = array_key_exists('body', $override_params) ? $override_params['body'] : 'this is a very important message body';
        $params['signature_id'] = array_key_exists('signature_id', $override_params) ? $override_params['signature_id'] : '0';
        $params['receipt'] = array_key_exists('receipt', $override_params) ? $override_params['receipt'] : '0';

        return $params;
    }

}