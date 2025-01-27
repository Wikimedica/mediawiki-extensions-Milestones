<?php
/**
 * Milestone RewardGainedPresentationModel.
 *
 * @file
 * @ingroup Extensions
 * @author Antoine Mercier-Linteau
 * @license gpl-3.0
 */

namespace MediaWiki\Extension\Milestones;

/**
 * Defines a presentation model for the reward-gained event.
 * */
class RewardGainedPresentationModel extends \MediaWiki\Extension\Milestones\MilestonePresentationModel
{ 
    /**
     * @inheritdoc
     * */
    public function getIconType() { return 'milestones-reward-gained'; }

    /**
     * @inheritdoc
     * */
    public function getSecondaryLinks() {        
        return [
            [  
                'url' => \Title::makeTitle(NS_SPECIAL, 'Préférences', 'mw-prefsection-milestones')->getFullURL(),
                'label' => 'Voir mes récompenses',
                'icon' => false
            ]
        ];
    }
}