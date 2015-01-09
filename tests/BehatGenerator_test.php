<?php
defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/question/editlib.php');
require_once($CFG->dirroot . '/question/category_class.php');
require_once($CFG->dirroot .'/blocks/quickmail/tests/behat/generateFerpaSteps.php');

class BehatTestcase extends advanced_testcase {

    public function testConfigStudentsCanUse(){
        $config1 = new Config(array(
            "allowStudentsGlobal" => Config::ALLOW_STUDENTS_GLOBAL_YES,
            "allowStudentsCourse" => Config::ALLOW_STUDENTS_COURSE_YES,
            "groupsGlobal"        => Config::GRP_GLOBAL_IGNORE,
            "groupsCourse"        => Config::GRP_COURSE_NONE,
        ));

        $this->assertTrue($config1->studentsCanUse());

        $config2 = new Config(array(
            "allowStudentsGlobal" => Config::ALLOW_STUDENTS_GLOBAL_NO,
            "allowStudentsCourse" => Config::ALLOW_STUDENTS_COURSE_YES,
            "groupsGlobal"        => Config::GRP_GLOBAL_IGNORE,
            "groupsCourse"        => Config::GRP_COURSE_NONE,
        ));

        $this->assertTrue($config2->studentsCanUse());

        $config3 = new Config(array(
            "allowStudentsGlobal" => Config::ALLOW_STUDENTS_GLOBAL_NO,
            "allowStudentsCourse" => Config::ALLOW_STUDENTS_COURSE_NO,
            "groupsGlobal"        => Config::GRP_GLOBAL_IGNORE,
            "groupsCourse"        => Config::GRP_COURSE_NONE,
        ));

        $this->assertFalse($config3->studentsCanUse());

        $config4 = new Config(array(
            "allowStudentsGlobal" => Config::ALLOW_STUDENTS_GLOBAL_YES,
            "allowStudentsCourse" => Config::ALLOW_STUDENTS_COURSE_NO,
            "groupsGlobal"        => Config::GRP_GLOBAL_IGNORE,
            "groupsCourse"        => Config::GRP_COURSE_NONE,
        ));

        $this->assertFalse($config4->studentsCanUse());

        $config5 = new Config(array(
            "allowStudentsGlobal" => Config::ALLOW_STUDENTS_GLOBAL_NVR,
            "allowStudentsCourse" => Config::ALLOW_STUDENTS_COURSE_YES,
            "groupsGlobal"        => Config::GRP_GLOBAL_IGNORE,
            "groupsCourse"        => Config::GRP_COURSE_NONE,
        ));

        $this->assertFalse($config5->studentsCanUse());

    }

    public function testScenario_clickOnComposeEmail(){
        $teacher = new User('t1');
        $student = new User('s1');

        $config1 = new Config(array(
            "allowStudentsGlobal" => Config::ALLOW_STUDENTS_GLOBAL_YES,
            "allowStudentsCourse" => Config::ALLOW_STUDENTS_COURSE_YES,
            "groupsGlobal"        => Config::GRP_GLOBAL_IGNORE,
            "groupsCourse"        => Config::GRP_COURSE_NONE,
        ));
        $this->assertTrue($config1->studentsCanUse());


        $scenario1 = new Scenario(array('config' => $config1));

        $this->assertTrue($config1->userCanUse($teacher));
        //$bool1 = $scenario1->clickOnComposeEmail($teacher);
        //$this->assertTrue($bool1);

        $this->assertTrue($config1->userCanUse($student));
        //$bool1 = $scenario1->clickOnComposeEmail($student);
        //$this->assertTrue($bool1);


        $config4 = new Config(array(
            "allowStudentsGlobal" => Config::ALLOW_STUDENTS_GLOBAL_YES,
            "allowStudentsCourse" => Config::ALLOW_STUDENTS_COURSE_NO,
            "groupsGlobal"        => Config::GRP_GLOBAL_IGNORE,
            "groupsCourse"        => Config::GRP_COURSE_NONE,
        ));

        $this->assertFalse($config4->studentsCanUse());

        $scenario1 = new Scenario(array('config' => $config4));

        $this->assertTrue($config4->userCanUse($teacher));

        $this->assertFalse($config4->userCanUse($student));
    }

    public function testScenario_userCanUse(){
        $student = new User('s1');
        $this->assertEquals(User::STUDENT, $student->role);

        $config1 = new Config(array(
            "allowStudentsGlobal" => Config::ALLOW_STUDENTS_GLOBAL_NVR,
            "allowStudentsCourse" => Config::ALLOW_STUDENTS_COURSE_YES,
            "groupsGlobal"        => Config::GRP_GLOBAL_IGNORE,
            "groupsCourse"        => Config::GRP_COURSE_NONE,
        ));

        $this->assertFalse($config1->studentsCanUse());
        $this->assertFalse($config1->userCanUse($student));


        $config2 = new Config(array(
            "allowStudentsGlobal" => Config::ALLOW_STUDENTS_GLOBAL_NO,
            "allowStudentsCourse" => Config::ALLOW_STUDENTS_COURSE_YES,
            "groupsGlobal"        => Config::GRP_GLOBAL_IGNORE,
            "groupsCourse"        => Config::GRP_COURSE_NONE,
        ));

        $this->assertTrue($config2->studentsCanUse());
        $this->assertTrue($config2->userCanUse($student));


        $config3 = new Config(array(
            "allowStudentsGlobal" => Config::ALLOW_STUDENTS_GLOBAL_NVR,
            "allowStudentsCourse" => '-',
            "groupsGlobal"        => Config::GRP_GLOBAL_IGNORE,
            "groupsCourse"        => Config::GRP_COURSE_NONE,
        ));

        $this->assertFalse($config3->studentsCanUse());
        $this->assertFalse($config3->userCanUse($student));

    }

}