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

class EZResus2MonthsFree extends Milestone {
    
   /** @inheritdoc */
    public function canApply() {
        $categories = (new \WikiPage( $this->user->getUserPage()))->getCategories();

        while($categories->valid()) {
            $cat = $categories->current();
            if(strpos($cat->getText(), 'Rédacteur') !== false) { return true; }
            $categories->next();
        }
        
        return false;
    }

    /** @inheritdoc */
    protected function getNotificationMessage() { return '2 mois gratuits pour l\'application EZResus.'; }

    /** @inheritdoc */
    public function getName() { return 'Code de promotion <a href="https://ezresus.com/">EZResus</a> (2 mois gratuits)'; }

    /** @inheritdoc */
    public function getMessage() { 
        if(!$this->hasMilestone()) { return 'Vous n\'avez pas accès à cette récompense.'; }

        return wfMessage('milestones-ezresus2monthsfree-message')->parse();
    }
}
