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
 * Quickmail-specific subclass of the parent behat library.
 *
 * @package    block_quickmail
 * @copyright  2015 Louisiana State University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('generator/BehatGenerator.php');

/**
 * Adds some additional steps to the Background definiton.
 */
class QMBackground extends Background implements Prefix {

    /**
     * Concatenate the steps required to enable the Quickmail block
     * in the test course, 'Course One'.
     * @return string Mink steps
     */
    protected function enableQuickmail(){
        $str = $this->loginAs($this->u('t4')->username, Prefix::GIVEN, 1);
        $str.= $this->follow('Course One');
        $str.= $this->turnEditingOn();
        $str.= $this->addBlock('Quickmail');
        $str.= $this->shouldSeeWhere('Compose New Email', 'Quickmail', 'block');
        $str.= $this->logOut();
        return $str;
    }

    /**
     * @TODO - this is done to more closely mirror the LSU roles setup, therefore
     * it may not be in the alignment with the intended use of Behat in Moodle.
     * Concatenate the actions required to disable the 'Acccess all group'
     * permission for non-editing teachers.
     * @return string
     */
    protected function disableViewAllGroupsForTeachers(){
        $t = $this->t(2);
        $n = $this->n();
        $str = $this->loginAs('admin', Prefix::GIVEN, 1);
        $str.= "{$t}And I navigate to \"Define roles\" node in \"Site administration > Users > Permissions\"$n";
        $str.= $this->iClickOn('Edit', 'link', 'Non-editing teacher', 'table_row');
        $str.= $this->iClickOn("Allow", "checkbox", "Access all groups", "table_row");
        $str.= $this->saveChanges();
        $str.= $this->logOut();
        return $str;
    }

    /**
     * Concatenate the strings built in this class after the
     * main background definition in the parent.
     * @see Background::__toString() Background::__toString()
     * @return string the complete background string
     */
    public function __toString() {
        return parent::__toString().$this->disableViewAllGroupsForTeachers().$this->enableQuickmail();
    }
}

/**
 * Models the configuration settings that affect Quickmail's behavior.
 */
class QMConfig extends Config {


    const KEY_ALLOW_STUDENTS_GLOBAL = 'alstgl';
    const ALLOW_STUDENTS_GLOBAL_YES = 1;
    const ALLOW_STUDENTS_GLOBAL_NO  = 0;
    const ALLOW_STUDENTS_GLOBAL_NVR = -1;

    const KEY_GRP_GLOBAL    =  'grgl';
    const GRP_GLOBAL_IGNORE =  1;
    const GRP_GLOBAL_COURSE =  0;
    const GRP_GLOBAL_SEP    = -1;

    const KEY_ALLOW_STUDENTS_COURSE = 'alstco';
    const ALLOW_STUDENTS_COURSE_YES = 1;
    const ALLOW_STUDENTS_COURSE_NO  = 0;

    const KEY_GRP_COURSE    =  'grco';
    const GRP_COURSE_VIS    =  1;
    const GRP_COURSE_NONE   =  0;
    const GRP_COURSE_SEP    = -1;

    /**
     * Most importantly, this constructor initializes the full set of
     * settings and options that afect Quickmail.
     *
     * As important, it sets the configured value for the Scenario to which
     * this QMConfig object belongs.
     * @see Scenario::$config Scenario::$config
     * @see QMConfig::initSettings() initSettings()
     *
     * @TODO Check whether we need to call parent constructor..
     * @param type $params
     * @throws Exception
     */
    public function __construct($params = array()){
        parent::__construct($params);

        foreach(self::settings() as $setting){
            $s = new Setting($setting['setting']);

            foreach($setting['options'] as $o){
                $s->addOption($o);
            }
            $this->settings[$s->key] = $s;
        }
    }

    /**
     * Contains a map of all QM settings, options and labels and values for each.
     * Initializes the local settings array
     * @todo consider promoting the children of 'setting', removing the 'setting'
     * level.
     * @see QMConfig::$settings $settings
     */
    protected static function settings() {
        return array(
            array(
                'setting' => array(
                    'key' => self::KEY_ALLOW_STUDENTS_GLOBAL,
                    'label' => 'Allow students to use Quickmail',
                    'uniquelabel' => 'Allow students to use Quickmail [global]',
                    ),
                'options' => array(
                    new SettingOption(array(
                        'key' => self::ALLOW_STUDENTS_GLOBAL_NVR,
                        'label' => 'Never',
                    )),
                    new SettingOption(array(
                        'key' => self::ALLOW_STUDENTS_GLOBAL_NO,
                        'label' => 'No',
                    )),
                    new SettingOption(array(
                        'key' => self::ALLOW_STUDENTS_GLOBAL_YES,
                        'label' => 'Yes',
                    ))
                )
            ),
            array(
                'setting' => array(
                    'key' => 'alstco',
                    'label' => 'Allow students to use Quickmail',
                    'uniquelabel' => 'Allow students to use Quickmail [course]',
                    ),
                'options' => array(
                    new SettingOption(array(
                        'key' => self::ALLOW_STUDENTS_COURSE_NO,
                        'label' => 'No',
                    )),
                    new SettingOption(array(
                        'key' => self::ALLOW_STUDENTS_COURSE_YES,
                        'label' => 'Yes',
                    )),
                )
            ),
            array(
                'setting' => array('key' => 'grgl', 'label' => 'FERPA Mode'),
                'options' => array(
                    new SettingOption(array(
                        'key' => self::GRP_GLOBAL_COURSE,
                        'label' => 'Respect Course Mode',
                    )),
                    new SettingOption(array(
                        'key' => self::GRP_GLOBAL_IGNORE,
                        'label' => 'No Group Respect',
                    )),
                    new SettingOption(array(
                        'key' => self::GRP_GLOBAL_SEP,
                        'label' => 'Always Separate Groups',
                    ))
                )
            ),
            array(
                'setting' => array('key' => 'grco', 'label' => 'Course group mode'),
                'options' => array(
                    new SettingOption(array(
                        'key' => self::GRP_COURSE_NONE,
                        'label' => 'No groups',
                    )),
                    new SettingOption(array(
                        'key' => self::GRP_COURSE_SEP,
                        'label' => 'Separate groups',
                    )),
                    new SettingOption(array(
                        'key' => self::GRP_COURSE_VIS,
                        'label' => 'Visible groups',
                    )),
                )
            )
        );
    }

    /**
     * Using the __toString magic method on Setting,
     * build a Mink comment string describing the values of each of the settings.
     * @see Setting::__toString() Setting::__toString()
     * @return string Mink comment string
     */
    public function buildComment() {
        $str  = $this->comment("Configuration details:");
        foreach($this->settings as $s){
            $str.= $this->comment($s);
        }
        return $str.$this->n();
    }

    /**
     * Configure QM administrative settings
     * @see Steps::setFields()
     * @return string Mink steps for setting up admin settings values
     */
    public function adminSettings(){
        $str = $this->loginAs('admin', Prefix::GIVEN, 1);

        $fields = array(
            array(
                $this->settings[self::KEY_ALLOW_STUDENTS_GLOBAL]->label,
                $this->settings[self::KEY_ALLOW_STUDENTS_GLOBAL]->valueString()),
            array(
                $this->settings[self::KEY_GRP_GLOBAL]->label,
                $this->settings[self::KEY_GRP_GLOBAL]->valueString())
            );

        $str.= $this->setFields($fields, true);
        $str.= $this->logOut();
        return $str;
    }

    /**
     * Configure course-level settings.
     * @see Steps::setFields()
     *
     * @return string Mink steps for setting up course-level settings values
     */
    public function courseSettings(){
        $str = '';

        $str.= $this->loginAs('t4');
        $str.= $this->follow('Course One');

        if(!$skipcoursesettinggroup){
            $str.= $this->follow('Edit settings');
            $str.= "\t\tAnd I expand all fieldsets\n";

            $groups = $this->settings[self::KEY_GRP_COURSE];
            $str.= $this->setFields(array(array($groups->label,$groups->valueString())));
            $str.= $this->saveChanges();
        }

        if(!$skipcoursesettingallow){
            $str .= $this->iClickOn("Configuration", 'link', 'Quickmail', 'block');

            $allow = $this->settings[self::KEY_ALLOW_STUDENTS_COURSE];
            $str .= $this->setFields(array(array($allow->label, $allow->valueString())));
            $str .= $this->saveChanges();
        }

        $str .= $this->logOut();

        return $str;
    }

    /**
     * Answers the question: 'Given the current settings configuration, should
     * students be allowed to use QM?'.
     * @see QMConfig::userRoleAllowed()
     * @return boolean true | false whether students can | cannot use Quickmail
     */
    public function allowStudents(){

        // Setting the admin setting to 'Never' should prohibit student use,
        // regardless of the course-level setting.
        $prohibitStudents = $this->settings[self::KEY_ALLOW_STUDENTS_GLOBAL]->value() == self::ALLOW_STUDENTS_GLOBAL_NVR;

        if($prohibitStudents){
            // Admin setting rules.
            return false;
        }else{
            // Defer to course-level setting.
            $allowAtCourseLevel = $this->settings[self::KEY_ALLOW_STUDENTS_COURSE]->value() == self::ALLOW_STUDENTS_COURSE_YES;
            return $allowAtCourseLevel ? true : false;
        }
    }

    /**
     * Client of self::allowStudents() and User::isTeacher(), this fn answers
     * the question: 'Can this \User $user access QM, given the current settings
     * configuration?'.
     *
     * @param User $user
     * @see User::$role User::$role
     * @see User::isTeacher()
     * @see QMConfig::allowStudents()
     * @return boolean  false if student, true if any other role
     */
    public function userRoleAllowed(User $user){

        if(!$user->isTeacher()){
            return $this->allowStudents();
        }
        return true;
    }

    /**
     * Given the current settings configuration, is the notion of
     * 'Separate Groups' being enforced ?
     * @return boolean true for all-visible, false for group-restricted
     */
    public function everyoneVisible(){
        $prohibited = $this->settings[self::KEY_GRP_GLOBAL]->value() == self::GRP_GLOBAL_SEP;
        $ignored    = $this->settings[self::KEY_GRP_GLOBAL]->value() == self::GRP_GLOBAL_IGNORE;

        if($prohibited){
            return false;
        }elseif($ignored){
            return true;
        }else{
            return $this->settings[self::KEY_GRP_COURSE]->value() > -1;
        }
    }

    /**
     * 'no group respect' is an option at the administrative level
     * @see QMConfig::GRP_GLOBAL_IGNORE QMConfig::GRP_GLOBAL_IGNORE
     * @return boolean false if there are group restrictions at any level,
     * true otherwise
     */
    public function groupsIgnored(){
        if($this->settings[self::KEY_GRP_GLOBAL]->value() == self::GRP_GLOBAL_IGNORE){
            return true;
        }

        $respectcourse = $this->settings[self::KEY_GRP_GLOBAL]->value() == self::GRP_GLOBAL_COURSE;
        $nogroups      = $this->settings[self::KEY_GRP_COURSE]->value() == self::GRP_COURSE_NONE;
        if($respectcourse && $nogroups){
            return true;
        }
    }

    /**
     * 'Visible groups' is a course-level groups setting where groups exist
     * in the course, but all groups and their members are visible to all members
     * of other groups...
     * This situation only happens when the admin setting is set to 'respect
     * course' mode, then the course-level setting can take precedence.
     *
     * @return boolean
     */
    public function openGroups(){
        $respectcourse = $this->settings[self::KEY_GRP_GLOBAL]->value() == self::GRP_GLOBAL_COURSE;
        $visiblegroups = $this->settings[self::KEY_GRP_COURSE]->value() == self::GRP_COURSE_VIS;
        return $respectcourse && $visiblegroups;
    }

    /**
     * Separate groups is the most restrictive groups setting and corresponds to
     * @see QMConfig::GRP_GLOBAL_SEP QMConfig::GRP_GLOBAL_SEP
     * @see QMConfig::GRP_COURSE_SEP QMConfig::GRP_COURSE_SEP
     * @return boolean true if either setting has this value, admin takes precedence
     */
    public function separateGroups(){
        if($this->settings[self::KEY_GRP_GLOBAL]->value() == self::GRP_GLOBAL_SEP){
            return true;
        }
        $respectcourse  = $this->settings[self::KEY_GRP_GLOBAL]->value() == self::GRP_GLOBAL_COURSE;
        $separategroups = $this->settings[self::KEY_GRP_COURSE]->value() == self::GRP_COURSE_SEP;
        if($respectcourse && $separategroups){
            return true;
        }

        return false;
    }


    public function __toString() {
        return '';
    }

    public static function filter(\Config $config) {
        return true;
    }

}

class QMScenario extends Scenario implements Prefix {
    public $commonGroups = array('All Users', 'Not in a group', 'All Groups');

    public function headerComment(){
        return $this->config->buildComment();
    }

    public function allGroupMembers(User $user){
        $members = array();
        foreach($user->groups() as $g){
            if($g->name == 'Not in a group'){
//                die();
                continue;
            }
            $members = array_merge($members, $g->members);
        }
        return $members;
    }
    public function userDisplayString(User $viewer, User $viewed) {
        $allGroups = function() use($viewed){
            $groups = $viewed->groups();
            if($viewed->role === Role::EDITINGTEACHER){
                unset($groups['Not in a group']);
            }
            $grpStr = implode(',', array_keys($groups));
            return sprintf("%s %s (%s)", $viewed->firstname, $viewed->lastname, $grpStr);
        };

        $intersectGroups = function() use($viewer, $viewed){
            $commonGroups = array_intersect(array_keys($viewer->groups()), array_keys($viewed->groups()));

            $groupsNamesStr = ' ('.implode(',',$commonGroups).')';
            return sprintf("%s %s%s", $viewed->firstname, $viewed->lastname, $groupsNamesStr);
        };

        if($this->config->groupsIgnored()){
            return sprintf("%s %s", $viewed->firstname, $viewed->lastname);
        }

        if($this->config->openGroups()){
            return $allGroups();
        }

        if($this->config->separateGroups()){
            return $intersectGroups();
        }

        if($this->config->noGroups()){
//            return $allGroups();
        }

        return "ERROR!!";
    }

    protected function seeWhoWhere($users, $where, $whereType, $not){
        $str = '';
        $prefix = Prefix::THEN;
        foreach($users as $u){
            $str .= $this->shouldSeeWhere($u, $where, $whereType, $not, $prefix, 3);
            $prefix = Prefix::_AND;
        }
        return $str;
    }

    public function noUsersInYourGroup(User $u){
        if($this->config->separateGroups() && $this->notInAGroup($u)){
            return true;
        }
        return false;
    }

    public function notInAGroup(User $u){
        return count($u->groups) === 1 && $u->groups[0] === 'Not in a group';
    }

    public function usersVisibleTo(User $u){
        $exclude = array($u->username => $u);
        if($u->role === Role::EDITINGTEACHER){
            return array_diff_assoc($this->feature->users, $exclude);
        }
        $users = array();
        foreach($u->groups() as $g){
            $users = array_merge($users, $g->members);
        }
        return array_diff_assoc($users, $exclude);
    }

    public function clickOnComposeEmail(User $user){
        $canUse = $this->config->userRoleAllowed($user);
        if($canUse){
            return $this->iClickOn("Compose New Email", 'link', 'Quickmail', 'block', 'When');
        }
        return $this->shouldSee('Quickmail', true, Prefix::THEN);
    }
}

class QMFeature extends Feature {

    public function __construct($params = array()){
        parent::__construct($params);
        // consider letting the following two lines happen automatically on the parent...
        $this->initGroupsUsers();
        $this->addCourse(new Course(array('shortname' =>' C1', "name" => "Course One")));

        $this->addTag('block');
        $this->addTag('block_quickmail');
    }
}

class UIControl {
    use Steps;
    public $selector;
    public $type;
    public $options = array();
    public $selection;
}

class QMFilter extends UIControl {
    /**
     *
     * @param array|string $selection if a string, the apram will become a 1-element array
     */
    public function setSelection($selection){
        $selection = is_array($selection) ? $selection : array($selection);
        $this->selection = $selection;
    }
    public function configure(){
        return $this->setFields(array(array($this->selector, implode(',',$this->selection))));
    }
    public function addOptions(array $options){
        $this->options = array_merge($this->options, $options);
    }
}

class QMGroupsFilter extends QMFilter {
    public function __construct(){
        $this->selector = 'groups';
        $this->options  = array('Not in a group', 'All Users', 'All Groups');
    }
}

class QMRolesFilter extends QMFilter {
    public function __construct(){
        $this->selector = 'roles';
        $this->options  = array('No filter');
    }
}