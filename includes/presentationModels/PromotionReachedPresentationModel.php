<?php
/**
 * Milestone PromotionReachedPresentationModel.
 *
 * @file
 * @ingroup Extensions
 * @author Antoine Mercier-Linteau
 * @license gpl-3.0
 */

namespace MediaWiki\Extension\Milestones;

/**
 * Defines a presentation model for the promotion-reached event.
 * */
class PromotionReachedPresentationModel extends \MediaWiki\Extension\Milestones\MilestonePresentationModel
{    
    /**
     * @inheritdoc
     * */
    public function getIconType() { return 'milestones-promotion-reached'; }

    /**
     * @inheritdoc
     * */
    public function getSecondaryLinks() {        
        return [
            [  
                'url' => \Title::makeTitle(NS_PROJECT, "Politiques/Niveaux_éditoriaux", 'Procédure_d\'obtention')->getFullUrl(),
                'label' => 'Appliquer pour la promotion',
                'icon' => false
            ]
        ];
    }
}