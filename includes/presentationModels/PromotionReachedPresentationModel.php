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
                'url' => \Title::newFromText("Politiques/Niveaux_éditoriaux", NS_PROJECT)->getFullUrl().'#Procédure_d\'obtention',
                'label' => 'Appliquer pour la promotion',
                'icon' => false
            ]
        ];
    }
}