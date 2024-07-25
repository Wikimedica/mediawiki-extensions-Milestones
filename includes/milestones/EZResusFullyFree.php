<?php
/**
 * Redacteur adjoint Milestone
 *
 * @file
 * @ingroup Extensions
 * @author Antoine Mercier-Linteau
 * @license GPL 3.0
 */

namespace MediaWiki\Extension\Milestones;

use MediaWiki\MediaWikiServices;
use MediaWiki\Extension\Milestones\Milestone;

class EZResusFullyFree extends Milestone {
    
   /** @inheritdoc */
    public function canApply() {
        $categories = (new \WikiPage( $this->user->getUserPage()))->getCategories();

        while($categories->valid()) {
            $cat = $categories->current();
            if($cat->getText() == 'Rédacteurs seniors') { return true; }
            $categories->next();
        }
        
        return false;
    }

    /** @inheritdoc */
    protected function getNotificationMessage() { return 'L\'application EZResus complètement gratuite !'; }

    /** @inheritdoc */
    public function getName() { return 'Code de promotion <a href="https://ezresus.com/">EZResus</a> (gratuit à vie)'; }

    /** @inheritdoc */
    public function getMessage() { 
        if(!$this->hasMilestone()) { return 'Vous n\'avez pas accès à cette récompense.'; }

        return wfMessage('milestones-ezresusfullyfree-message')->parse();
    }
}
