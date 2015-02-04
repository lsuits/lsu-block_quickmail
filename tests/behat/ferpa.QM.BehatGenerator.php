<?php

require_once('behat_generator/BehatGenerator.php');
require_once('QM.BehatGenerator.php');

class ferpaScenario extends QMScenario {

    public function steps(){
        $str = '';
        foreach($this->feature->users as $u){

            $str .= $this->logInAs($u->username);
            $str .= $this->follow('Course One');
            $str .= $this->clickOnComposeEmail($u);
            if(!$this->config->userRoleAllowed($u)){
                $str .= $this->logOut();
                continue;
            }
            $str .= $this->thenIShouldSee($u);
            $str .= $this->groupsFilterContents($u);
            $str .= $this->logOut();
        }
        return $str;
    }

    public function groupsFilterContents($u) {
        $str = '';
        if($this->config->everyoneVisible()){
            $myGroups = array_merge($this->commonGroups, array_keys($this->feature->groups));
        }else{
            $myGroups    = array_merge($this->commonGroups, $u->groups);
        }
        $allGroups   = array_merge($this->commonGroups, array_keys($this->feature->groups));
        $otherGroups = array_diff($allGroups, $myGroups);

        $str .= $this->seeWhoWhere($myGroups, '#groups', 'css_element', false);
        $str .= $this->seeWhoWhere($otherGroups, '#groups', 'css_element', true);
        return $str;
    }

    /**
     * Starting with a list of all users (except the User passed in),
     * create a list of users that $u can see and a list for those he can't.
     *
     * @param User $u represents the current logged-in user
     * @return string Mink strings corresponding to 'And I should [not] see ..."
     */
    public function thenIShouldSee(User $u) {

        $str = '';
        if(!$this->config->userRoleAllowed($u)){
            return $str;
        }

        if($this->notInAGroup($u)){
            $str.= $this->shouldSee("There are no users in your group capable of being emailed.", false, 'Then');
            return $str;
        }

        $allButMe = array_diff_assoc($this->feature->users, array($u->username => $u));
        $allUsers = $shouldNotSee = $allButMe;
        $shouldSee = array();


        if($this->config->everyoneVisible() || $u->role == Role::EDITINGTEACHER){
            $shouldSee = $allUsers;
            $shouldNotSee = array();
        }else{
            foreach($u->groups() as $g){
                if(!empty($g->members)){
                    foreach($g->members as $m){
                        if(!array_key_exists($m->username, $shouldSee) && $m->username !== $u->username){
                            $shouldSee[$m->username] = $this->feature->users[$m->username];
                        }
                        unset($shouldNotSee[$m->username]);
                    }
                }
            }
        }

        foreach($shouldSee as $user){
            $str.= $this->shouldSeeWhere($this->userDisplayString($u, $user), "#from_users", "css_element", false, 'Then', 3);
        }
        foreach($shouldNotSee as $user){
            $name = sprintf("%s %s", $user->firstname, $user->lastname);
            $str .= $this->shouldSeeWhere($name, "#from_users", "css_element", true, 'Then', 3);
        }
        return $str;
    }

}

class ferpaFeature extends QMFeature {
    public function __construct($params = array()) {
        parent::__construct($params);
        $comment = sprintf("#%s

# Tests that the visibility of students/teachers enrolled in a course adheres to the FERPA controls and the
# Student usage controls available at the block and admin level.

# A set of %d scenarios.
# These scenarios cover a minimal set of
# the possible configurations for quickmail. Given 2 administrative settings with 3 options each, and 2 block-level
# settings with 2 and 3 options each, there are a total of 3*3*3*2=54 configuration combinations.
# Because settings at the administrative level override those at the block level, 29 of those can be removed,
# leaving the 25 given in the test.

# NB: Because of the additional complexity, the %s test does not account for the groups filter.", $this->file, count(QMConfig::getConfigs()), $this->file);

        $this->appendComment($comment);
        $this->addTag('ferpa');
        $this->addTag('javascript');
    }
}

$cwd = dirname(__FILE__);
$featureParams = array(
    'file' => $cwd."/ferpa.feature",
    'title'=> "Ensure enrollee visibility behaves in accordance with ferpa settings.",
);

$f = new ferpaFeature($featureParams);
$f->background = new QMBackground($f);
$configs = QMConfig::getConfigs();

foreach($configs as $c){
    $s = new ferpaScenario();
    $s->config = $c;
    $f->addScenario($s);
}
echo $f->string();
$f->toFile();