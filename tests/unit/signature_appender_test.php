<?php
 
require_once(dirname(__FILE__) . '/traits/unit_testcase_traits.php');

use block_quickmail\messenger\signature_appender;
use block_quickmail\persistents\signature;

class block_quickmail_signature_appender_testcase extends advanced_testcase {
    
    use has_general_helpers,
        sets_up_courses;

    public function test_appends_signature()
    {
        $this->resetAfterTest(true);

        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();
        
        $signature = signature::create_new([
            'user_id' => $user_teacher->id,
            'title' => 'first',
            'default_flag' => 0,
            'signature' => '<p>This is my signature!</p>',
        ]);

        $body = 'This is the message I hope you like it.';

        $formatted_body = signature_appender::append_user_signature_to_body(
            $body, 
            $user_teacher->id,
            $signature->get('id')
        );

        $this->assertContains('<p>This is my signature!</p>', $formatted_body);
    }

    public function test_does_not_append_signature_if_requested_signature_does_not_belong_to_sending_user()
    {
        $this->resetAfterTest(true);

        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();
        
        $signature = signature::create_new([
            'user_id' => $user_students[0]->id,
            'title' => 'first',
            'default_flag' => 0,
            'signature' => '<p>This is my signature!</p>',
        ]);

        $body = 'This is the message I hope you like it.';

        $formatted_body = signature_appender::append_user_signature_to_body(
            $body, 
            $user_teacher->id,
            $signature->get('id')
        );

        $this->assertNotContains('<p>This is my signature!</p>', $formatted_body);
    }

    public function test_does_not_append_signature_if_no_signature_id_is_given()
    {
        $this->resetAfterTest(true);

        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();
        
        $signature = signature::create_new([
            'user_id' => $user_teacher->id,
            'title' => 'first',
            'default_flag' => 0,
            'signature' => '<p>This is my signature!</p>',
        ]);

        $body = 'This is the message I hope you like it.';

        $formatted_body = signature_appender::append_user_signature_to_body(
            $body, 
            $user_teacher->id
        );

        $this->assertNotContains('<p>This is my signature!</p>', $formatted_body);
    }

}