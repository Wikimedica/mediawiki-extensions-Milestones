<?php
/**
 * Milestone MilestonePresentationModel.
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
abstract class MilestonePresentationModel extends \EchoEventPresentationModel
{    
    /**
     * @param \EchoEvent $event
     * @return array
     */
    public static function locateUser(\EchoEvent $event) {
        return [$event->getAgent()];
    }
    
    /**
     * @inheritdoc
     */
    public function getHeaderMessage() {
        return new \Message($this->getHeaderMessageKey(), [$this->event->getTitle()->getFullText()]);
    }
    
    /**
     * @inheritdoc
     * */
    public function getBodyMessage() { 
        $body = $this->event->getExtraParam( 'message', false );
		if ($body) {
			// Create a dummy message to contain the excerpt.
			$msg = new \RawMessage( '$1' );
			$msg->plaintextParams( $body );
			return $msg;
        }
    }

    /**
     * @inheritdoc
     * */
    public function getIconType() { return 'milestones-promotion-reached'; }
    
    /**
     * @inheritdoc
     * */
    public function getPrimaryLink() {
        return false;
    }
}