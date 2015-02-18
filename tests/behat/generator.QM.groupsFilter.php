<?php

require_once('behat_generator/BehatGenerator.php');
require_once('behat_generator.QM.php');

class groupsFilterScenario extends QMScenario {

    public function steps(){
        $str = '';
        $onlyTheseUsers = array('t4', 't1', 's4', 't5');
        foreach($this->feature->users as $u){
            if(!in_array($u->username, $onlyTheseUsers)){
                continue;
            }
            $str .= $this->logInAs($u->username);
            $str .= $this->follow('Course One');
            $str .= $this->clickOnComposeEmail($u);
            if(!$this->config->userRoleAllowed($u)){
                $str .= $this->logOut();
                continue;
            }
            $str .= $this->exerciseFilters($u);
            $str .= $this->logOut();
        }
        return $str;
    }

    public function exerciseFilters(User $u){
        $str ='';
        $groupsFilter = new QMGroupsFilter();
        $groupsFilter->addOptions($u->groups);
        $rolesFilter  = new QMRolesFilter();
        $rolesFilter->addOptions(array_values(Role::$map));
        $possibleGroupoptions = array_merge($u->groups, $this->commonGroups);

        foreach($possibleGroupoptions as $group){
            if(is_string($group)){
                $groupsFilter->setSelection($group);
            }else{
                $groupsFilter->setSelection($group->name);
            }

            foreach($rolesFilter->options as $role){
                $allMyUsers   = $this->usersVisibleTo($u);
                $rolesFilter->setSelection($role);

                $str .= $rolesFilter->configure();
                $str .= $groupsFilter->configure();
                $str .= $this->iPress('Add', 'And', 3);

                $selected = $this->selected($u, $groupsFilter, $rolesFilter);
                foreach($selected as $s){
                    $name = $this->userDisplayString($u, $s);
                    $str .= $this->shouldSeeWhere($name, '#mail_users', 'css_element', false, 'And', 3);
                    unset($allMyUsers[$s->username]);
                }
                foreach($allMyUsers as $au){
                    $name = $this->userDisplayString($u, $au);
                    $str .= $this->shouldSeeWhere($name, '#from_users', 'css_element', false, 'And', 3);
                }
                $str .= $this->iPress('Remove All', 'Then');
            }
        }
        return $str;
    }
    public function clickGroupFilter(User $u){

        $str = '';

        $allMyUsers = $this->usersVisibleTo($u);
        foreach($u->groups() as $g){
            $members = $this->selected($u, array($g));

            if(!empty($this->feature->groups[$g->name])){
                $str.=$this->setFields(array(array('groups', $g->name)));

                $str .= $this->iPress('Add', 'And', 3);
                foreach($members as $m){
                    $name = $this->userDisplayString($u, $m);
                    $str .= $this->shouldSeeWhere($name, '#mail_users', 'css_element', false, 'And', 3);
                    unset($allMyUsers[$m->username]);
                }

                foreach($allMyUsers as $au){
                    $str .= $this->shouldSeeWhere($au, '#from_users', 'css_element', false, 'And', 3);
                }
                $str .= $this->iPress('Remove All', 'Then');
            }
        }
        return $str;
    }

    public function selected(User $user, QMGroupsFilter $groups, QMRolesFilter $roles){
        $allUsers = array();
        foreach($groups->selection as $groupName){
            if(array_key_exists($groupName, $this->feature->groups) && in_array($groupName, $user->groups)){
                $group = $this->feature->groups[$groupName];
            }elseif($groupName === 'All Groups'){
                $group = new Group('all', $this->allGroupMembers($user));
            }
            elseif($groupName === 'All Users'){
                $group = new Group('all users', $this->usersVisibleTo($user));
            }else{

                if($user->role === Role::EDITINGTEACHER){
                    $group = $this->feature->groups['Not in a group'];
                }else{
                    $group = new Group('empty', array());
                }
            }
            $allUsers = array_merge($allUsers, $group->members);
        }

        $filter = function($user) use($roles) {
            if($roles->selection[0] === 'No filter'){
                return true;
            }
            return in_array(Role::name($user->role), $roles->selection);
        };
        $filtered = array_filter($allUsers, $filter);
        return array_diff_assoc($filtered, array($user->username => $user));
    }
}

class groupsFilterFeature extends QMFeature {
    public function __construct($params = array()) {
        parent::__construct($params);
        $comment = sprintf("#%s", $this->file);

        $this->appendComment($comment);
    }
}


$featureParams = array(
    'file' => "tests/behat/groupsFilter.feature",
    'title'=> "Ensure that the groups filter control behaves as intended.",
);

$f = new QMFeature($featureParams);
$f->addTag('ui');
$f->addTag('javascript');
$f->background = new QMBackground($f);
$configs = QMConfig::getConfigs();

foreach($configs as $c){
    $s = new groupsFilterScenario();
    $s->config = $c;
    $f->addScenario($s);
}
echo $f->string();
$f->toFile();