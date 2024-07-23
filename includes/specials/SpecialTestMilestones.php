<?php
/**
 * Special page to test milestsones notifications
 *
 * @file
 * @author Antoine Mercier-Linteau
 * @license GPL 3.0
 */

namespace MediaWiki\Extension\Milestones;

use MediaWiki\Extension\Milestones\Milestone;
use Html;

/**
 * @inheritdoc
 * */
class SpecialTestMilestones extends \FormSpecialPage 
{ 
    /**
     * @inheritdoc
     * */
    public function execute($par) {   
		$form = $this->getForm();
        $form->show();
    }
    
    /**
     * @inheritdoc
     * */
    public function __construct() {
        parent::__construct('TestMilestones', 'edit');
        $this->getOutput()->setRobotPolicy('noindex,nofollow'); // Do not index that special page.
    }

    /**
     * @inheritdoc
     **/
    protected function getDisplayFormat() {	return 'ooui'; }
    
    /**
     * @inheritdoc
     **/
    public function preText() {
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
    }

    /**
     * @inheritdoc
     **/
    public function getFormFields() {
        global $wgMilestones;
        
        $form =  [
            'milestone' => [
                'type' => 'radio',
                'label' => 'Accomplissement à tester',
                'options' => array_flip($wgMilestones),
                'required' => true,
                'help' => 'Testez les notifications envoyées lorsqu\'un éditeur atteint un accomplissement. Une notification sera ajoutée à votre compte.',
            ]
        ];
        
        return $form;
    }
    
    /**
     * @inheritdoc
     * */
    public function onSubmit($data) {           
        global $wgMilestones;

        if(isset($data['milestone'])) { 
            $class = $wgMilestones[$data['milestone']];

            $milestone = Milestone::instantiate($class, $this->getUser());
            
            if($milestone->launchNotification()) { // If that milestone wants to launch a notification.
                $success = 'Notification envoyée, peut-être devez vous rafraîchir la page pour qu\'elle s\'affiche';
            }
            else { $success = 'L\'accomplissement ne génère pas de notifications.'; }

            $this->getOutput()->redirect($this->getFullTitle()->getFullURL(['success' => $success]));
        }
    }
}