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

////////////////////////////////////////////////////
///
///  COURSE SET UP HELPERS
/// 
////////////////////////////////////////////////////

trait sets_up_courses {

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

    public function get_role_list()
    {
        $archetypes = get_role_archetypes();

        $results = [];
        $i = 1;

        foreach ($archetypes as $archetype) {
            $results[$i] = $archetype;
            $i++;
        }

        return $results;
    }

    // manager
    // coursecreator
    // editingteacher
    // teacher
    // student
    // guest
    // user
    // frontpage
    
    // returns [course, context, enrolled_users]
    public function setup_course_with_users($params = [])
    {
        // create a course category
        $category = $this->getDataGenerator()->create_category();

        // create a course
        $course = $this->getDataGenerator()->create_course();

        $roles = $this->get_role_list();
        
        // initialize user results container
        $enrolled_users = [];

        foreach($roles as $role_name) {
            $enrolled_users[$role_name] = [];
        }

        foreach ($roles as $role_id => $role_name) {
            if (array_key_exists($role_name, $params)) {
                foreach (range(1, $params[$role_name]) as $i) {
                    $handle = $role_name . $i;

                    // create a user
                    $user = $this->getDataGenerator()->create_user([
                        'email' => $handle . '@example.com', 
                        'username' => $handle
                    ]);

                    // enroll user in course
                    $this->getDataGenerator()->enrol_user($user->id, $course->id, $role_id, 'manual');

                    $enrolled_users[$role_name][] = $user;
                }
            }
        }

        return [
            $course,
            context_course::instance($course->id),
            $enrolled_users
        ];
    }

    /**
     * Returns a test course with:
     * - 1 editing teacher
     * - 3 teachers
     * - 40 students
     * - "red" group with 11 members (1 teacher, 10 students)
     * - "yellow" group with 15 members (1 teacher, 14 students)
     * - "blue" group with 15 members (1 teacher, 14 students)
     * 
     * @return array [course, course_context, users, groups]
     */
    public function create_course_with_users_and_groups()
    {
        // create course with enrolled users
        list($course, $course_context, $users) = $this->setup_course_with_users([
            'editingteacher' => 1,
            'teacher' => 3,
            'student' => 40,
        ]);

        $groups = [];

        $student_start = 1;

        foreach (['red', 'yellow', 'blue'] as $color) {
            // create a group 
            $groups[$color] = $this->getDataGenerator()->create_group([
                'courseid' => $course->id, 
                'name' => $color
            ]);

            // assign the first teacher to the group
            $this->getDataGenerator()->create_group_member([
                'userid' => $users['teacher'][0]->id, 
                'groupid' => $groups[$color]->id]
            );

            // assign a chunk of 10 unique users to the group
            foreach (range($student_start, $student_start + 9) as $i) {
                $this->getDataGenerator()->create_group_member([
                    'userid' => $users['student'][$i - 1]->id, 
                    'groupid' => $groups[$color]->id]
                );
            }

            $student_start += 10;
        }

        // assign first 4 students to group yellow
        // (these users are in group red as well)
        foreach (range(1, 4) as $i) {
            $this->getDataGenerator()->create_group_member([
                'userid' => $users['student'][$i - 1]->id, 
                'groupid' => $groups['yellow']->id]
            );
        }

        // assign first 4 students to group blue
        // (these users are in group yellow as well)
        foreach (range(11, 14) as $i) {
            $this->getDataGenerator()->create_group_member([
                'userid' => $users['student'][$i - 1]->id, 
                'groupid' => $groups['blue']->id]
            );
        }

        return [$course, $course_context, $users, $groups];
    }

    /*
     * FOR SOME REASON THIS DOES NOT WORK !! :(
     */
    public function assign_configuration_to_course($course, $override_params)
    {
        global $DB, $CFG;

        $params = $this->get_course_config_params($override_params);

        $dataobjects = [];

        // iterate over each given param, inserting each record for this course
        foreach ($params as $name => $value) {
            $config = new \stdClass;
            $config->coursesid = $course->id;
            $config->name = $name;
            $config->value = $value;

            $dataobjects[] = $config;
        }

        $DB->insert_records('block_quickmail_config', $dataobjects);
    }

    public function report_user_access_in_course($user, $course, $time)
    {
        global $DB;

        $record = new stdClass();
        $record->userid = $user->id;
        $record->courseid = $course->id;
        $record->timeaccess = $time;
        
        $DB->insert_record('user_lastaccess', $record);
    }

    public function assign_role_id_to_user_in_course($role_id, $user, $course)
    {
        $course_context = \context_course::instance($course->id);

        role_assign($role_id, $user->id, $course_context->id);
    }

}