<?php
class Base {
    public function __construct($params = array()) {
        if(is_object($params)){
            $params = (array)$params;
        }
        $fields = array_keys(get_object_vars($this));

        foreach($params as $k => $v){
            if(in_array($k, $fields)){
                $this->$k = $v;
            }
        }
    }
}
class User {

    const EDITINGTEACHER = 1;
    const TEACHER = 3;
    const STUDENT = 0;

    public $username, $groups = array(), $role, $firstname, $lastname;

    public function __construct($username) {

        if(!empty($username)){
            $this->username = $this->firstname = $username;

            if(substr($username, 0, 1) === 't'){
                $this->lastname  = "Teacher";

                if($this->username === 't4'){
                    $this->role = self::EDITINGTEACHER;
                }else{
                    $this->role = self::TEACHER;
                }
            }else{
                $this->role = self::STUDENT;
                $this->lastname = "Student";
                $this->role = self::STUDENT;
            }
        }
    }

    public function roleName(){
        if($this->role === self::EDITINGTEACHER){
            return 'editingteacher';
        }elseif($this->role === self::TEACHER){
            return 'teacher';
        }else{
            return 'student';
        }
    }

    public function isTeacher(){
        return $this->role == self::EDITINGTEACHER || $this->role == self::TEACHER;
    }

    public function __toString() {
        $str = "$this->firstname $this->lastname";

        $groups = array();
        if(!empty($this->groups)){
            foreach($this->groups as $g){
                $groups[] = $g->name;
            }

            $grpStr = implode(',', $groups);
            $str .= " ($grpStr)";
        }

        return $str;
    }
}

class Group {

    public $name, $members;

    public function __construct($name = '', $members = array()) {
        if(!empty($name)){
            $this->name = $name;
        }
        if(!empty($members)){
            $this->members = array();
            foreach($members as $m){
                $this->members[$m->username] = $m;
            }
        }
    }
}

class GroupMembership {

    public static function getMembership(){
        $u = function($username){
            return new User($username);
        };
        $g = function($name, $members){
            return new Group($name, $members);
        };
        return array(
            $g('group1', array($u('t1'), $u('t4'), $u('s1'), $u('s4'))),
            $g('group2', array($u('t2'), $u('t4'), $u('s2'), $u('s4'))),
            $g('group3', array($u('t3'), $u('t4'), $u('s3'))),
            $g('Not in a group',array($u('t5'), $u('s5'))),
        );
    }

}

class Config extends Base{

    const ALLOW_STUDENTS_GLOBAL_YES = 1;
    const ALLOW_STUDENTS_GLOBAL_NO  = 0;
    const ALLOW_STUDENTS_GLOBAL_NVR = -1;

    const GRP_GLOBAL_IGNORE =  1;
    const GRP_GLOBAL_COURSE =  0;
    const GRP_GLOBAL_SEP    = -1;

    const ALLOW_STUDENTS_COURSE_YES = 1;
    const ALLOW_STUDENTS_COURSE_NO  = 0;

    const GRP_COURSE_VIS    =  1;
    const GRP_COURSE_NONE   =  0;
    const GRP_COURSE_SEP    = -1;

    public $allowStudentsGlobal, $groupsGlobal, $groupsCourse, $allowStudentsCourse;
    static $langMap = array(
        'allowStudentsGlobal' => array(
            'name' => 'Allow students to use Quickmail',
            'values' => array('-1'=> 'Never', '0' => 'No', '1' => 'Yes')),

        'groupsGlobal' => array(
            'name' => 'FERPA Mode',
            'values' => array('-1' => 'Always Separate Groups', '0' => 'Respect Course Mode', '1' => 'No Group Respect')),

        'groupsCourse' => array(
            'name' => 'Group mode',
            'values' => array('-1' => 'Separate groups', '0' => 'No groups', '1' => 'Visible groups')),

        'allowStudentsCourse' => array(
            'name' => 'Allow students to use Quickmail',
            'values' => array('0' => 'No', '1' => 'Yes'))
    );

    public function __construct($params = array()){
        parent::__construct($params);
        foreach(get_object_vars($this) as $k => $v){
            if(is_numeric($v)){
                $this->$k = intval($v);
            }
        }
    }
    public function allowStudentsGlobalToString($i){
        switch($i){
            case '1':
                return "\t# Students globally allowed to use Quickmail.";
            case '0':
                return "\t# Students globally disallowed from using Quickmail.";
            case '-1':
                return "\t# Students globally PROHIBITED from using Quickmail.";
            default:
                return "\t# unrecognized setting for allowStudentsGlobal.";
        }
    }
    public function allowStudentsCourseToString($i){
        switch($i){
            case '1':
                return "\t# Students allowed to use Quickmail at the course level.";
            case '0':
                return "\t# Students disallowed from using Quickmail at the course level.";
            default:
                return "\t# unrecognized setting for allowStudentsCourse.";
        }
    }

    public function groupsGlobalToString($i){
        switch($i){
            case '1':
                return "\t# Globally ignoring groups";
            case '0':
                return "\t# Globally repsecting course group mode.";
            case '-1':
                return "\t# Globally enforcing separate groups always.";
            default:
                return "\t# unrecognized setting for groupsGlobal.";
        }
    }

    public function groupsCourseToString($i){
        switch($i){
            case '1':
                return "\t# Groups set to 'visible' at the course level.";
            case '0':
                return "\t# 'No groups' set at course level.";
            case '-1':
                return "\t# 'separate groups' set at the course level.";
            default:
                return "\t# unrecognized setting for groupsCourse.";
        }
    }

    public function __toString() {
        $vars = get_object_vars($this);
        $str  = "\n\n\t# Configuration details:\n";
        foreach(array_keys($vars) as $v){
            if($v == 'langMap') continue;

            $fn = $v."ToString";
            $str.= $this->$fn($this->$v)."\n";
        }
        return $str;
    }

    public function adminSettings(){
        $str  = "\t\tGiven I log in as \"admin\"\n";
        $str  .= "\t\tAnd I set the following administration settings values:\n";

        $allowName  = self::$langMap['allowStudentsGlobal']['name'];
        $allowValue = self::$langMap['allowStudentsGlobal']['values'][$this->allowStudentsGlobal];
        $str .= sprintf("\t\t\t|%s|%s|\n", $allowName, $allowValue);

        $groupsName  = self::$langMap['groupsGlobal']['name'];
        $groupsValue = self::$langMap['groupsGlobal']['values'][$this->groupsGlobal];
        $str .= sprintf("\t\t\t|%s|%s|\n", $groupsName, $groupsValue);

        $str .= "\t\tAnd I log out\n\n";
        return $str;
    }

    public function courseSettings(){
        $str = '';

        if(($this->allowStudentsCourse === '-') && ($this->groupsCourse === '-')) {
            return $str;
        }

        $str .= "\t\tGiven I log in as \"t4\"\n";
        $str .= "\t\tAnd I follow \"Course One\"\n";

        if($this->groupsCourse !== '-'){

            $groupModeLang = self::$langMap['groupsCourse']['name'];
            $groupMode = self::$langMap['groupsCourse']['values'][$this->groupsCourse];

            $str .= "\t\tAnd I follow \"Edit settings\"\n";
            $str .= "\t\tAnd I expand all fieldsets\n";
            $str .= "\t\tAnd I set the following fields to these values:\n";
            $str .= sprintf("\t\t\t|%s|%s|\n", $groupModeLang, $groupMode);

        }

        if($this->allowStudentsCourse !== '-'){

            $allowStudentsValue = self::$langMap['allowStudentsCourse']['values'][$this->allowStudentsCourse];
            $allowStudentsName  = self::$langMap['allowStudentsCourse']['name'];
            $str .= "\n\t\t# NOTE: Editingteachers and Teachers are able to see all users in all roles - is this correct ?\n";
            $str .= "\t\tThen I set the following fields to these values:\n";
            $str .= sprintf("\t\t\t|%s|%s|\n", $allowStudentsName, $allowStudentsValue);
            $str .= "\t\tAnd I press \"Save changes\"\n";
        }

        $str .= "\t\tAnd I log out\n\n";

        return $str;
    }

    public function setupToString(){
        return $this->adminSettings().$this->courseSettings();
    }

    public function studentsCanUse(){
        $prohibited = $this->allowStudentsGlobal === self::ALLOW_STUDENTS_GLOBAL_NVR;

        if($prohibited){
            return false;
        }else{
            return $this->allowStudentsCourse === self::ALLOW_STUDENTS_COURSE_YES ? true : false;
        }
    }

    public function userCanUse(User $user){

        if(!$user->isTeacher()){
            return $this->studentsCanUse();
        }
        return true;
    }

    public function canSeeEveryone(){
        $prohibited = $this->groupsGlobal === self::GRP_GLOBAL_SEP;
        $ignored    = $this->groupsGlobal === self::GRP_GLOBAL_IGNORE;

        if($prohibited){
            return false;
        }elseif($ignored){
            return true;
        }else{
            return $this->groupsCourse > -1;
        }
    }

    public function groupsIgnored(){
        if($this->groupsGlobal === self::GRP_GLOBAL_IGNORE){
            return true;
        }

        if($this->groupsGlobal === self::GRP_GLOBAL_COURSE && $this->groupsCourse === self::GRP_COURSE_NONE){
            return true;
        }
    }

    public function openGroups(){
        return $this->groupsGlobal === self::GRP_GLOBAL_COURSE && $this->groupsCourse === self::GRP_COURSE_VIS;
    }

    public function separateGroups(){
        if($this->groupsGlobal === self::GRP_GLOBAL_SEP){
            return true;
        }

        if($this->groupsGlobal === self::GRP_GLOBAL_COURSE && $this->groupsCourse === self::GRP_COURSE_SEP){
            return true;
        }

        return false;
    }

    public static function getConfigs() {
        $raw = array(
            array('1','-1','-','1'),
                array('1','-1','-','0'),
                array('1','0','0','1'),
                array('1','0','0','0'),
                array('1','0','-1','1'),
                array('1','0','-1','0'),
                array('1','0','1','1'),
                array('1','0','1','0'),
                array('1','1','-','1'),
                array('1','1','-','0'),
                array('0','-1','-','1'),
                array('0','-1','-','0'),
                array('0','0','0','1'),
                array('0','0','0','0'),
                array('0','0','-1','1'),
                array('0','0','-1','0'),
                array('0','0','1','1'),
                array('0','0','1','0'),
                array('0','1','-','1'),
                array('0','1','-','0'),
                array('-1','-1','-','-'),
                array('-1','0','0','-'),
                array('-1','0','-1','-'),
                array('-1','0','1','-'),
                array('-1','1','-','-'),
        );
        $keys = array_keys(get_object_vars(new self()));
        $configs = array();
        foreach($raw as $r){
            $configs[] = new Config(array_combine($keys, $r));
        }
        return $configs;
    }
}

class Background extends Base{

    public $users, $groups;
    public $courseFull = "Course One";
    public $courseShort = "C1";

    public function __construct($params = array()){
        parent::__construct($params);
        $this->init();
    }

    public function init(){
        $this->groups = GroupMembership::getMembership();
        // pretty awkward, but...
        $this->users  = $this->allUsers();
    }

    public function allUsers() {
        if(empty($this->users)){
            $tmp = array();
            foreach($this->groups as $g){
                foreach($g->members as $username => $m){
                    if(!array_key_exists($username, $tmp)){
                        $tmp[$username] = $m;
                    }
                    $tmp[$username]->groups[$g->name] = $g;
                }
            }
            $this->users = $tmp;
        }
        return $this->users;
    }

    public function __toString() {
        return "\tBackground:\n".$this->buildCoursesStr().$this->buildUsersStr().$this->buildCourseEnrollmentsStr().$this->buildGroupsStr().$this->buildGroupMembersStr().$this->enableQuickmail();
    }

    public function buildCoursesStr(){
        $catgry = 0;
        return "\tGiven the following \"courses\" exist:\n"
                . "\t\t|fullname|shortname|category|\n"
                . "\t\t|$this->courseFull|$this->courseShort|$catgry|\n";
    }

    public function buildUsersStr(){
        $str = "\tAnd the following \"users\" exist:\n"
                . "\t\t|username|firstname|lastname|\n";
        foreach($this->users as $name => $user){
            $str .= "\t\t|$name|$user->firstname|$user->lastname|\n";
        }
        return $str;
    }

    public function buildCourseEnrollmentsStr(){
        $str = "\tAnd the following \"course enrolments\" exist:\n"
                . "\t\t| user | course | role |\n";
        foreach($this->users as $user){
            $str .= "\t\t|$user->username|$this->courseShort|{$user->roleName()}|\n";
        }
        return $str;
    }

    public function buildGroupsStr(){

        $str = "\tGiven the following \"groups\" exist:\n"
                . "\t\t| name | course | idnumber|\n";
        foreach($this->groups as $g){
            if($g->name === 'Not in a group'){
                continue;
            }
            $str .= "\t\t|$g->name|$this->courseShort|$g->name|\n";
        }
        return $str;
    }

    public function buildGroupMembersStr(){
        $str = "\tGiven the following \"group members\" exist:\n"
                . "\t\t| user      | group  |\n";
        foreach($this->users as $user){
            foreach($user->groups as $g){
                if($g->name === 'Not in a group'){
                    continue;
                }
                $str .= "\t\t|$user->username|$g->name|\n";
            }
        }
        return $str;
    }

    public function enableQuickmail(){
        return "\tGiven I log in as \"t4\"
            \tAnd I follow \"Course One\"
            \tAnd I turn editing mode on
            \tWhen I add the \"Quickmail\" block
            \tThen I should see \"Compose New Email\" in the \"Quickmail\" \"block\"
            \tAnd I log out\n";
    }
}

class Scenario extends Background {
    public $config;
    static $counter = 1;

    public function userCanSeeWho(User $user){
        $localUser = $this->users[$user->username];
        if(!empty($localUser)){
            return $this->usersFromGroups($localUser->groups);
        }
        throw new Exception(sprintf("User %s not found in local variable.", $user->username));
    }

    public function usersFromGroups(array $groups){
        $ret = array();
        foreach($groups as $g){
            foreach($g->members as $m){
                if(!array_key_exists($m->username, $ret)){
                    $ret[$m->username] = $m;
                }
            }
        }
        return $ret;
    }

    public function __toString() {
        $header = sprintf("\t%s\n\tScenario: %d\n", $this->config, self::$counter++);
        $str = "";
        $str.=$header;
        $str.= $this->config->setupToString();

        foreach($this->users as $u){
            $str .= $this->logInAs($u);
            $str .= $this->clickOnComposeEmail($u);
            $str .= $this->thenIShouldSee($u);
            $str .= $this->andILogOut();
        }
        return $str."\n\n";
    }

    public function logInAs($who){
        return sprintf("\t\tGiven I log in as \"%s\"\n\t\tAnd I follow \"Course One\"\n", $who->username);
    }

    public function noUsersInYourGroup(User $u){
        if(count($u->groups) === 1){
            $keys = array_keys($u->groups);

        $nogroup  = $u->groups[$keys[0]]->name === 'Not in a group';
            if($this->config->separateGroups() && $nogroup){
                return true;
            }
        }
        return false;
    }

    public function clickOnComposeEmail(User $user){

        $canUse = $this->config->userCanUse($user);
        if($canUse){
            $s = "\t\tWhen I click on \"Compose New Email\" \"link\" in the \"Quickmail\" \"block\"\n";
            if($this->noUsersInYourGroup($user)){
                return $s."\t\tThen I should see \"There are no users in your group capable of being emailed.\"\n";
            }
            return $s;
        }
        return "\t\tThen I should not see \"Quickmail\"\n";
    }

    public function andILogOut() {
        return "\t\tAnd I log out\n\n";
    }

    public function userDisplayString(User $viewer, User $viewed){
        $allGroups = function() use($viewed){
            $groups = array();
                foreach($viewed->groups as $vg){
                    $groups[] = $vg->name;
                }
                $grpStr = implode(',',$groups);
                return sprintf("%s %s (%s)", $viewed->firstname, $viewed->lastname, $grpStr);
        };

        $intersectGroups = function() use($viewer, $viewed){
            $viewerGroups     = array();
            foreach($viewer->groups as $vg){
                $viewerGroups[] = $vg->name;
            }

            $viewedGroups  = array();
            foreach($viewed->groups as $tg){
                $viewedGroups[] = $tg->name;
            }
            $commonGroups = array_intersect($viewerGroups, $viewedGroups);

            $groupsNamesStr = ' ('.implode(',',$commonGroups).')';
            return sprintf("%s %s%s", $viewed->firstname, $viewed->lastname, $groupsNamesStr);
        };

        if($this->config->groupsIgnored()){
            //printf("==========ignoring groups\n");
            return sprintf("%s %s", $viewed->firstname, $viewed->lastname);
        }

        if($this->config->openGroups() || $viewer->isTeacher()){
            return $allGroups();
        }

        if($this->config->separateGroups()){
            return $intersectGroups();
        }

        if($this->config->noGroups()){

        }

        return "ERROR!!";
    }

    public function thenIShouldSee(User $u) {

        $str = '';
        if(!$this->config->userCanUse($u) || $this->noUsersInYourGroup($u)){
            return $str;
        }

        $allButMe = array_diff_assoc($this->allUsers(), array($u->username => $u));
        $allUsers = $shouldNotSee = $allButMe;
        $shouldSee = array();


        if($this->config->canSeeEveryone() || $u->role == User::EDITINGTEACHER){
            $shouldSee = $allUsers;
            $shouldNotSee = array();
        }else{
            foreach($u->groups as $g){
                if(!empty($g->members)){
                    foreach($g->members as $m){
                        if(!array_key_exists($m->username, $shouldSee) && $m->username !== $u->username){
                            $shouldSee[$m->username] = $this->users[$m->username];
                        }
                        unset($shouldNotSee[$m->username]);
                    }
                }
            }
        }

        foreach($shouldSee as $user){
            $str.= sprintf("\t\tThen I should see \"%s\" in the \"#from_users\" \"css_element\"\n", $this->userDisplayString($u, $user));
        }
        foreach($shouldNotSee as $user){
            $str.= sprintf("\t\tThen I should not see \"%s %s\" in the \"#from_users\" \"css_element\"\n", $user->firstname, $user->lastname);
        }
        return $str;
    }

}

class Writer {
    public $conf, $background, $scenarios;

    public function __construct() {
        $this->conf = Config::getConfigs();
        $this->background = new Background();
        foreach($this->conf as $c){
            $this->scenarios[] = new Scenario(array('config' => $c));
        }
    }

    public function scenariosToFile(){
        $file = 'tests/behat/ferpa.feature';
        $feature  = "@ferpa @javascript\nFeature: Verify Ferpa controls WRT groups\n\n";
        $feature .= "#ferpa.feature

            #Tests that the visibility of students/teachers enrolled in a course adheres to the FERPA controls and the
            #Student usage controls available at the block and admin level.

            #A set of 25 scenarios generated by `generateFerpaSteps.php`. These scenarios cover a minimal set of
            #the possible configurations for quickmail. Given 2 administrative settings with 3 options each, and 2 block-level
            #settings with 2 and 3 options each, there are a total of 3*3*3*2=54 configuration combinations.
            #Because settings at the administrative level override those at the block level, 29 of those can be removed,
            #leaving the 25 given in the test.

            #NB: Because of the additional complexity, the ferpa.feature test does not account for the groups filter.";

        $background = new Background();
        $header = $feature.$background;

        if(($handle = fopen($file, 'w')) !== false){
            fwrite($handle, $header);
            foreach($this->scenarios as $s){
                fwrite($handle, $s);
            }
            fclose($handle);
        }
    }
}

$w = new Writer();
$w->scenariosToFile();