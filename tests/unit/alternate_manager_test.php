<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    block_quickmail
 * @copyright  2008 onwards Louisiana State University
 * @copyright  2008 onwards Chad Mazilly, Robert Russo, Jason Peak, Dave Elliott, Adam Zapletal, Philip Cali
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
require_once(dirname(__FILE__) . '/traits/unit_testcase_traits.php');

use block_quickmail\services\alternate_manager;
use block_quickmail\persistents\alternate_email;
use block_quickmail\exceptions\validation_exception;

class block_quickmail_alternate_manager_testcase extends advanced_testcase {
    
    use has_general_helpers,
        sets_up_courses,
        sends_emails;

    public function test_does_not_create_alternate_if_given_invalid_data()
    {
        $this->resetAfterTest(true);
 
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();
        
        $this->expectException(validation_exception::class);

        $form_data = [
            'email' => '',
            'firstname' => '',
            'lastname' => '',
            'availability' => '',
            'allowed_role_ids' => [],
        ];

        $alternate = alternate_manager::create_alternate_for_user($user_teacher, $course->id, $form_data);
    }

    public function test_creating_with_availability_only_requires_course()
    {
        $this->resetAfterTest(true);
 
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();
        
        $this->expectException(validation_exception::class);

        $form_data = [
            'email' => '',
            'firstname' => '',
            'lastname' => '',
            'availability' => 'only',
            'allowed_role_ids' => [],
        ];

        $alternate = alternate_manager::create_alternate_for_user($user_teacher, 0, $form_data);
    }

    public function test_creating_with_availability_course_requires_course()
    {
        $this->resetAfterTest(true);
 
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();
        
        $this->expectException(validation_exception::class);

        $form_data = [
            'email' => '',
            'firstname' => '',
            'lastname' => '',
            'availability' => 'course',
            'allowed_role_ids' => [],
        ];

        $alternate = alternate_manager::create_alternate_for_user($user_teacher, 0, $form_data);
    }

    public function test_creates_alternate_record_with_availability_only_successfully()
    {
        $this->resetAfterTest(true);
 
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();
        
        $form_data = [
            'email' => 'an@email.com',
            'firstname' => 'Firsty',
            'lastname' => 'Lasty',
            'availability' => 'only',
            'allowed_role_ids' => [],
        ];

        $alternate = alternate_manager::create_alternate_for_user($user_teacher, $course->id, $form_data);

        $this->assertInstanceOf(alternate_email::class, $alternate);
        $this->assertEquals('an@email.com', $alternate->get('email'));
        $this->assertEquals('Firsty', $alternate->get('firstname'));
        $this->assertEquals('Lasty', $alternate->get('lastname'));
        $this->assertEquals('', $alternate->get('allowed_role_ids'));
        $this->assertEquals($user_teacher->id, $alternate->get('setup_user_id'));
        $this->assertEquals($course->id, $alternate->get('course_id'));
        $this->assertEquals($user_teacher->id, $alternate->get('user_id'));
        $this->assertEquals(0, $alternate->get('is_validated'));
    }

    public function test_creates_alternate_record_with_availability_course_successfully()
    {
        $this->resetAfterTest(true);
 
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();
        
        $form_data = [
            'email' => 'an@email.com',
            'firstname' => 'Firsty',
            'lastname' => 'Lasty',
            'availability' => 'course',
            'allowed_role_ids' => [],
        ];

        $alternate = alternate_manager::create_alternate_for_user($user_teacher, $course->id, $form_data);

        $this->assertInstanceOf(alternate_email::class, $alternate);
        $this->assertEquals('an@email.com', $alternate->get('email'));
        $this->assertEquals('Firsty', $alternate->get('firstname'));
        $this->assertEquals('Lasty', $alternate->get('lastname'));
        $this->assertEquals('', $alternate->get('allowed_role_ids'));
        $this->assertEquals($user_teacher->id, $alternate->get('setup_user_id'));
        $this->assertEquals($course->id, $alternate->get('course_id'));
        $this->assertEquals(0, $alternate->get('user_id'));
        $this->assertEquals(0, $alternate->get('is_validated'));
    }

    public function test_creates_alternate_record_with_availability_user_successfully()
    {
        $this->resetAfterTest(true);
 
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();
        
        $form_data = [
            'email' => 'an@email.com',
            'firstname' => 'Firsty',
            'lastname' => 'Lasty',
            'availability' => 'user',
            'allowed_role_ids' => [],
        ];

        $alternate = alternate_manager::create_alternate_for_user($user_teacher, 0, $form_data);

        $this->assertInstanceOf(alternate_email::class, $alternate);
        $this->assertEquals('an@email.com', $alternate->get('email'));
        $this->assertEquals('Firsty', $alternate->get('firstname'));
        $this->assertEquals('Lasty', $alternate->get('lastname'));
        $this->assertEquals('', $alternate->get('allowed_role_ids'));
        $this->assertEquals($user_teacher->id, $alternate->get('setup_user_id'));
        $this->assertEquals(0, $alternate->get('course_id'));
        $this->assertEquals($user_teacher->id, $alternate->get('user_id'));
        $this->assertEquals(0, $alternate->get('is_validated'));
    }

    public function test_sends_confirmation_email_to_user_after_creating_alternate()
    {
        $this->resetAfterTest(true);
 
        $sink = $this->open_email_sink();

        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();
        
        $form_data = [
            'email' => 'an@email.com',
            'firstname' => 'Firsty',
            'lastname' => 'Lasty',
            'availability' => 'only',
            'allowed_role_ids' => [],
        ];

        $alternate = alternate_manager::create_alternate_for_user($user_teacher, $course->id, $form_data);

        $this->assertEquals(1, $this->email_sink_email_count($sink));
        $this->assertEquals(\block_quickmail_string::get('alternate_subject'), $this->email_in_sink_attr($sink, 1, 'subject'));
        $this->assertEquals('an@email.com', $this->email_in_sink_attr($sink, 1, 'to'));

        $this->close_email_sink($sink);
    }

    public function test_does_not_resend_confirmation_email_for_invalid_alternate_id()
    {
        $this->resetAfterTest(true);
 
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $alternate = alternate_email::create_new([
            'setup_user_id' => $user_teacher->id,
            'firstname' => 'Firsty',
            'lastname' => 'Lasty',
            'allowed_role_ids' => '',
            'course_id' => $course->id,
            'user_id' => $user_teacher->id,
            'email' => $user_teacher->email,
            'is_validated' => false
        ]);

        $this->expectException(validation_exception::class);

        $wrong_id = $alternate->get('id') + 1;

        alternate_manager::resend_confirmation_email_for_user($wrong_id, $user_teacher);
    }

    public function test_does_not_resend_confirmation_email_to_an_invalid_user()
    {
        $this->resetAfterTest(true);
 
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $alternate = alternate_email::create_new([
            'setup_user_id' => $user_teacher->id,
            'firstname' => 'Firsty',
            'lastname' => 'Lasty',
            'allowed_role_ids' => '',
            'course_id' => $course->id,
            'user_id' => $user_teacher->id,
            'email' => $user_teacher->email,
            'is_validated' => false
        ]);

        $this->expectException(validation_exception::class);

        alternate_manager::resend_confirmation_email_for_user($alternate->get('id'), $user_students[0]);
    }

    public function test_does_not_resend_confirmation_email_for_already_confirmed()
    {
        $this->resetAfterTest(true);
 
        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $alternate = alternate_email::create_new([
            'setup_user_id' => $user_teacher->id,
            'firstname' => 'Firsty',
            'lastname' => 'Lasty',
            'allowed_role_ids' => '',
            'course_id' => $course->id,
            'user_id' => $user_teacher->id,
            'email' => $user_teacher->email,
            'is_validated' => true
        ]);

        $this->expectException(validation_exception::class);

        alternate_manager::resend_confirmation_email_for_user($alternate->get('id'), $user_teacher);
    }

    public function test_resends_confirmation_email_to_user()
    {
        $this->resetAfterTest(true);
 
        $sink = $this->open_email_sink();

        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $alternate = alternate_email::create_new([
            'setup_user_id' => $user_teacher->id,
            'firstname' => 'Firsty',
            'lastname' => 'Lasty',
            'allowed_role_ids' => '',
            'course_id' => $course->id,
            'user_id' => $user_teacher->id,
            'email' => $user_teacher->email,
            'is_validated' => false
        ]);

        alternate_manager::resend_confirmation_email_for_user($alternate->get('id'), $user_teacher);

        $this->assertEquals(1, $this->email_sink_email_count($sink));
        $this->assertEquals(\block_quickmail_string::get('alternate_subject'), $this->email_in_sink_attr($sink, 1, 'subject'));
        $this->assertEquals($user_teacher->email, $this->email_in_sink_attr($sink, 1, 'to'));

        $this->close_email_sink($sink);
    }

    public function test_does_not_confirm_invalid_alternate()
    {
        $this->resetAfterTest(true);

        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $alternate = alternate_email::create_new([
            'setup_user_id' => $user_teacher->id,
            'firstname' => 'Firsty',
            'lastname' => 'Lasty',
            'allowed_role_ids' => '',
            'course_id' => $course->id,
            'user_id' => $user_teacher->id,
            'email' => $user_teacher->email,
            'is_validated' => false
        ]);

        $this->assertEquals(0, $alternate->get('is_validated'));

        $this->expectException(validation_exception::class);

        // generate, or fetch existing, token for this user and alternate instance
        // note: this does not expire!
        $token = get_user_key('blocks/quickmail', $user_teacher->id, $alternate->get('id'));

        $wrong_id = $alternate->get('id') + 1;

        $alternate = alternate_manager::confirm_alternate_for_user($wrong_id, $token, $user_teacher);

        $this->assertEquals(0, $alternate->get('is_validated'));
    }

    public function test_does_not_confirm_confirmed_alternate()
    {
        $this->resetAfterTest(true);

        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $alternate = alternate_email::create_new([
            'setup_user_id' => $user_teacher->id,
            'firstname' => 'Firsty',
            'lastname' => 'Lasty',
            'allowed_role_ids' => '',
            'course_id' => $course->id,
            'user_id' => $user_teacher->id,
            'email' => $user_teacher->email,
            'is_validated' => true
        ]);

        $this->expectException(validation_exception::class);

        // generate, or fetch existing, token for this user and alternate instance
        // note: this does not expire!
        $token = get_user_key('blocks/quickmail', $user_teacher->id, $alternate->get('id'));

        $alternate = alternate_manager::confirm_alternate_for_user($alternate->get('id'), $token, $user_teacher);

        $this->assertEquals(0, $alternate->get('is_validated'));
    }

    public function test_confirms_unconfirmed_alternate()
    {
        $this->resetAfterTest(true);

        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $alternate = alternate_email::create_new([
            'setup_user_id' => $user_teacher->id,
            'firstname' => 'Firsty',
            'lastname' => 'Lasty',
            'allowed_role_ids' => '',
            'course_id' => $course->id,
            'user_id' => $user_teacher->id,
            'email' => $user_teacher->email,
            'is_validated' => false
        ]);

        $this->assertEquals(0, $alternate->get('is_validated'));

        // generate, or fetch existing, token for this user and alternate instance
        // note: this does not expire!
        $token = get_user_key('blocks/quickmail', $user_teacher->id, $alternate->get('id'));

        $alternate = alternate_manager::confirm_alternate_for_user($alternate->get('id'), $token, $user_teacher);

        $this->assertEquals(1, $alternate->get('is_validated'));
    }

    public function test_does_not_delete_alternate_for_non_setup_user()
    {
        $this->resetAfterTest(true);

        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $alternate = alternate_email::create_new([
            'setup_user_id' => $user_teacher->id,
            'firstname' => 'Firsty',
            'lastname' => 'Lasty',
            'allowed_role_ids' => '',
            'course_id' => $course->id,
            'user_id' => $user_teacher->id,
            'email' => $user_teacher->email,
            'is_validated' => false
        ]);
        
        $this->expectException(validation_exception::class);

        alternate_manager::delete_alternate_email_for_user($alternate->get('id'), $user_students[0]);
    }

    public function test_deletes_alternate_for_setup_user()
    {
        $this->resetAfterTest(true);

        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $alternate = alternate_email::create_new([
            'setup_user_id' => $user_teacher->id,
            'firstname' => 'Firsty',
            'lastname' => 'Lasty',
            'allowed_role_ids' => '',
            'course_id' => $course->id,
            'user_id' => $user_teacher->id,
            'email' => $user_teacher->email,
            'is_validated' => false
        ]);
        
        $result = alternate_manager::delete_alternate_email_for_user($alternate->get('id'), $user_teacher);

        $this->assertTrue($result);
        $this->assertEquals(0, $alternate->get('timedeleted'));
    }

}