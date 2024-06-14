<?php
/**
 * Special page to manage user milestones
 *
 * @file
 * @author Antoine Mercier-Linteau
 * @license GPL 3.0
 */

namespace MediaWiki\Extension\Milestones;

use MediaWiki\Extension\Milestones\Milestone;
use Html;
use User;
use Title;

/**
 * @inheritdoc
 * */
class SpecialManageUserMilestones extends \FormSpecialPage 
{ 
    private $_username;
    
    /**
     * @inheritdoc
     * */
    public function __construct() 
    {
        parent::__construct('ManageUserMilestones', 'editinterface'); // editinterface is only for sysops.
        $this->getOutput()->setRobotPolicy('noindex,nofollow'); // Do not index that special page.
    }

    /**
     * @inheritdoc
     **/
    protected function getDisplayFormat() {	return 'ooui'; }

    /**
     * @inheritdoc
     **/
    public function getGroupName() { return 'users'; }

    /**
     * @inheritdoc
     **/
    public function preText() 
    {
        $request = $this->getRequest();
        $user = $this->getUser();
        $this->setHeaders();

        if($val = $request->getVal('success', false))
        {            
            $this->getOutPut()->addHTML(
				Html::successBox(
					Html::element(
						'p',
						[],
						$val
					),
					'mw-notify-success'
				)
			);
        }

        if($val = $request->getVal('error', false))
        {            
            $this->getOutPut()->addHTML(
				Html::errorBox(
					Html::element(
						'p',
						[],
						$val
					)
				)
			);
        }

        if($this->_username) {
            $this->getOutPut()->addHTML(
                Html::element(
                    'p',
                    [],
                    'Vous êtes présentement en train de modifier les accomplissements de Utilisateur:'.$this->_username
                )
			);}
    }

    /**
     * @inheritdoc
     * */
    public function execute($par)
    {   
        // Format the parameters passed to this special page.
        $user = explode('/', $par);
        
        if($user) { $this->_username = $user[0]; }

		$form = $this->getForm();
        $form->show();
    }

    /**
     * @inheritdoc
     **/
    public function getFormFields()
    {        
        $form =  [
            'username' => [
                'type' => 'text',
                'label' => 'Nom de l\'utilisateur (sans "Utilisateur:)"',
                'required' => true,
                'default' => $this->_username
            ]
        ];
        
        if(!$this->_username) { return $form; }

        global $wgMilestones;

        $user = User::newFromName($this->_username);
        $achievedMilestones = [];
        $milestones = array_flip($wgMilestones);

        foreach(Milestone::getAchievedMilestones($user) as $id => $m) {
            $achievedMilestones[$id] = $milestones[$id];
        }

        $form['milestones'] = [
            'type' => 'multiselect',
            'label' => 'Accomplissements',
            'options' => $milestones,
            'default' => $achievedMilestones
        ];

        return $form;
    }
    
    /**
     * @inheritdoc
     * */
    public function onSubmit($data)
    {           
        global $wgMilestones;

        $params = [];

        if(isset($data['milestones'])) {
            $user = User::newFromName($this->_username);

            foreach($wgMilestones as $n => $class) {
                $milestone = Milestone::instantiate($class, $user);
               
                if(!in_array(strval($n), $data['milestones'])) { 
                    $milestone->remove();
                    continue; 
                }

                if($milestone->hasMilestone()) { continue; } // Skip if that milestone was already there.

                // A new milestone has been added to the user.

                $milestone->apply();
                $milestone->launchNotification();
            }

            $params = ['success' => 'Les accomplissements ont été modifiés.'];
        }
        else if(isset($data['username'])) { 

            $user = User::newFromName($data['username']);

            if($user->isAnon()) {
                $this->getOutput()->redirect($this->getFullTitle()->getFullURL(['error' => 'L\'utilisateur '.$data['username'].' n\'existe pas.']));
                return;
            } else {}
        }

        $this->getOutput()->redirect(Title::newFromText($this->getName().'/'.$data['username'], NS_SPECIAL)->getFullURL($params));
    }
}