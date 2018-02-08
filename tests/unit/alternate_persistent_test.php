<?php
 
require_once(dirname(__FILE__) . '/traits/unit_testcase_traits.php');

use block_quickmail\persistents\alternate_email;

class block_quickmail_alternate_persistent_testcase extends advanced_testcase {
    
    use has_general_helpers,
        sets_up_courses;

    public function test_getters_before_confirmed()
    {
        $this->resetAfterTest(true);

        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        // create "only", not-confirmed
        $alternate = $this->create_alternate($user_teacher, $course);

        $this->assertEquals(0, $alternate->get('is_validated'));
        $this->assertEquals(0, $alternate->get('timedeleted'));
        $this->assertEquals('Firsty Lasty', $alternate->get_fullname());
        $this->assertEquals(\block_quickmail_plugin::_s('waiting'), $alternate->get_status());
    }

    public function test_get_status()
    {
        $this->resetAfterTest(true);

        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $alternate = $this->create_alternate($user_teacher, $course, 'only', 'email@one.com');

        $this->assertEquals(\block_quickmail_plugin::_s('waiting'), $alternate->get_status());

        $alternate->set('is_validated', 1);
        $alternate->update();

        $this->assertEquals(\block_quickmail_plugin::_s('confirmed'), $alternate->get_status());
    }

    public function test_get_scope()
    {
        $this->resetAfterTest(true);

        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $alternate = $this->create_alternate($user_teacher, $course, 'only', 'email@one.com');

        $this->assertEquals(\block_quickmail_plugin::_s('alternate_availability_only'), $alternate->get_scope());

        $alternate = $this->create_alternate($user_teacher, $course, 'user', 'email@two.com');

        $this->assertEquals(\block_quickmail_plugin::_s('alternate_availability_user'), $alternate->get_scope());

        $alternate = $this->create_alternate($user_teacher, $course, 'course', 'email@three.com');

        $this->assertEquals(\block_quickmail_plugin::_s('alternate_availability_course'), $alternate->get_scope());
    }

    public function test_get_domain()
    {
        $this->resetAfterTest(true);

        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $alternate = $this->create_alternate($user_teacher, $course, 'only', 'email@a-big-email-domain-with-dashes.com');

        $this->assertEquals('a-big-email-domain-with-dashes.com', $alternate->get_domain());
    }

    public function test_gets_all_for_user()
    {
        $this->resetAfterTest(true);

        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $alternate = $this->create_alternate($user_teacher, $course, 'only', 'email@one.com');
        $alternate = $this->create_alternate($user_teacher, $course, 'user', 'email@two.com');
        $alternate = $this->create_alternate($user_teacher, $course, 'course', 'email@three.com');

        $alternates = alternate_email::get_all_for_user($user_teacher->id);

        $this->assertCount(3, $alternates);

        $alternates = alternate_email::get_all_for_user($user_students[0]->id);

        $this->assertCount(0, $alternates);
    }
    
    public function test_get_flat_array_for_course_user()
    {
        $this->resetAfterTest(true);

        list($course, $user_teacher, $user_students) = $this->setup_course_with_teacher_and_students();

        $student1 = $user_students[0];
        $student2 = $user_students[1];

        $alternate = $this->create_alternate($user_teacher, $course, 'only', 'uncofirmed@one.com');
        $alternate = $this->create_alternate($user_teacher, $course, 'user', 'uncofirmed@two.com');
        $alternate = $this->create_alternate($user_teacher, $course, 'course', 'uncofirmed@three.com');

        $alternate = $this->create_alternate($user_teacher, $course, 'only', 'teacher@one.com', true);
        $alternate = $this->create_alternate($user_teacher, $course, 'user', 'teacher@two.com', true);
        $alternate = $this->create_alternate($user_teacher, $course, 'course', 'teacher@three.com', true);

        $alternate = $this->create_alternate($student1, $course, 'only', 'student1@one.com', true);
        $alternate = $this->create_alternate($student1, $course, 'user', 'student1@two.com', true);
        $alternate = $this->create_alternate($student1, $course, 'course', 'student1@three.com', true);

        $alternate = $this->create_alternate($student2, $course, 'only', 'student2@one.com', true);
        $alternate = $this->create_alternate($student2, $course, 'user', 'student2@two.com', true);
        $alternate = $this->create_alternate($student2, $course, 'course', 'student2@three.com', true);

        $alternates = alternate_email::get_flat_array_for_course_user($course->id, $user_teacher);

        $this->assertInternalType('array', $alternates);
        $this->assertCount(6, $alternates);
    }
    
    ///////////////////////////////////
    ///
    /// HELPERS
    /// 
    ///////////////////////////////////
    
    // only
    // user
    // course
    private function create_alternate($setup_user, $course, $availability = 'only', $email = '', $confirmed = false)
    {
        $course_id = $availability !== 'user'
            ? $course->id
            : 0;

        $user_id = $availability !== 'course'
            ? $setup_user->id
            : 0;

        return alternate_email::create_new([
            'setup_user_id' => $setup_user->id,
            'firstname' => 'Firsty',
            'lastname' => 'Lasty',
            'course_id' => $course_id,
            'user_id' => $user_id,
            'email' => $email ?: 'some@email.com',
            'is_validated' => (int) $confirmed
        ]);
    }
}