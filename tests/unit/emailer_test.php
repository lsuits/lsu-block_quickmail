<?php
 
require_once(dirname(__FILE__) . '/traits/unit_testcase_traits.php');

use block_quickmail_emailer;

class block_quickmail_emailer_testcase extends advanced_testcase {
    
    use has_general_helpers, 
        sends_emails;
    
    public function test_emailer_sends_to_an_email()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
        
        $sink = $this->open_email_sink();

        $user = $this->getDataGenerator()->create_user([
            'email' => 'teacher@example.com', 
            'username' => 'teacher'
        ]);

        $subject = 'Hello world';
        $body = 'This is one fine body.';

        $emailer = new block_quickmail_emailer($user, $subject, $body);
        $emailer->to_email('student@example.com');
        $emailer->send();

        $this->assertEquals(1, $this->email_sink_email_count($sink));
        $this->assertEquals($subject, $this->email_in_sink_attr($sink, 1, 'subject'));
        $this->assertTrue($this->email_in_sink_body_contains($sink, 1, $body));
        // $this->assertEquals($user_teacher->email, $this->email_in_sink_attr($sink, 1, 'from'));  <--- this would be nice
        $this->assertEquals(get_config('moodle', 'noreplyaddress'), $this->email_in_sink_attr($sink, 1, 'from'));
        $this->assertEquals('student@example.com', $this->email_in_sink_attr($sink, 1, 'to'));

        $this->close_email_sink($sink);
    }

    public function test_emailer_sends_to_a_user()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
        
        $sink = $this->open_email_sink();

        $sending_user = $this->getDataGenerator()->create_user([
            'email' => 'teacher@example.com', 
            'username' => 'teacher'
        ]);

        $receiving_user = $this->getDataGenerator()->create_user([
            'email' => 'student@example.com', 
            'username' => 'student'
        ]);

        $subject = 'Hello world';
        $body = 'This is one fine body.';

        $emailer = new block_quickmail_emailer($sending_user, $subject, $body);
        $emailer->to_user($receiving_user);
        $emailer->send();

        $this->assertEquals(1, $this->email_sink_email_count($sink));
        $this->assertEquals($subject, $this->email_in_sink_attr($sink, 1, 'subject'));
        $this->assertTrue($this->email_in_sink_body_contains($sink, 1, $body));
        // $this->assertEquals($user_teacher->email, $this->email_in_sink_attr($sink, 1, 'from'));  <--- this would be nice
        $this->assertEquals(get_config('moodle', 'noreplyaddress'), $this->email_in_sink_attr($sink, 1, 'from'));
        $this->assertEquals('student@example.com', $this->email_in_sink_attr($sink, 1, 'to'));

        $this->close_email_sink($sink);
    }

    public function test_emailer_sends_email_using_correct_replyto_params()
    {
        // reset all changes automatically after this test
        $this->resetAfterTest(true);
        
        $sink = $this->open_email_sink();

        $user = $this->getDataGenerator()->create_user([
            'email' => 'teacher@example.com', 
            'username' => 'teacher'
        ]);

        $subject = 'Hello world';
        $body = 'This is one fine body.';

        $emailer = new block_quickmail_emailer($user, $subject, $body);
        $emailer->to_email('student@example.com');
        $emailer->reply_to('reply@here.com', 'Reply Name');
        $emailer->send();

        $this->assertEquals(1, $this->email_sink_email_count($sink));
        $this->assertEquals($subject, $this->email_in_sink_attr($sink, 1, 'subject'));
        $this->assertTrue($this->email_in_sink_body_contains($sink, 1, $body));
        // $this->assertEquals('reply@here.com', $this->email_in_sink_attr($sink, 1, 'from')); //   <--- this would be nice
        $this->assertEquals('student@example.com', $this->email_in_sink_attr($sink, 1, 'to'));

        $this->close_email_sink($sink);
    }

}