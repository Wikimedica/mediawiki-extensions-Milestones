<?php
/**
 * Milestones extension main class.
 *
 * @file
 * @ingroup Extensions
 * @author Antoine Mercier-Linteau
 * @license GPL 3.0
 */

namespace MediaWiki\Extension\Milestones;

use MediaWiki\MediaWikiServices;
use MediaWiki\Extension\Milestones\Milestone;

/**
 * Milestones extension class.
 */
class Milestones
{	
	/**
	 * This hook is run when saving a page. Applying milestones is done upon saving pages (but should rather be a job)
	 */
	public static function onMultiContentSave( $renderedRevision, $user, $summary, $flags, $hookStatus ) { 
		return self::_applyMilestones($user);
	}
	
	/**
     * Allows registration of custom Echo events
     * @param array $echoNotifications for custom Echo event
     * @param array $echoNotificationCategories for custom Echo categories
     * @param array $echoNotiticationIcons replace or add custom icons
     * @return bool
     */
    public static function onBeforeCreateEchoEvent( &$echoNotifications, &$echoNotificationCategories, &$echoNotificationIcons )
    {
        // Not needed for now, the system category is fine (forces web notifications on).
        $echoNotificationCategories['milestones-promotion-reached'] = [
            'tooltip' => 'echo-pref-tooltip-milestones-promotion-reached',
            'priority' => 2, // High priority.
            'no-dismiss' => ['web']
        ];
		$echoNotificationCategories['milestones-reward-gained'] = [
            'tooltip' => 'echo-pref-tooltip-milestones-reward-gained',
            'priority' => 2, // High priority.
            'no-dismiss' => ['web']
        ];
        
        /* Enable email alerts by default. 
         * Also defined in extension.json, which seems superfluous, but sometimes, this event is not called
         * before notifications are cached in \Echo\NotifUser. Consequently \Echo\AttributeManager->getUserEnabledEvents() is 
         * called without those user default options returns that this event is not enabled for this user.
         * */
        global $wgDefaultUserOptions;
        $wgDefaultUserOptions["echo-subscriptions-email-milestones-promotion-reached"] = true;
        $wgDefaultUserOptions["echo-subscriptions-web-milestones-promotion-reached"] = true;
		$wgDefaultUserOptions["echo-subscriptions-email-milestones-reward-gained"] = true;
        $wgDefaultUserOptions["echo-subscriptions-web-milestones-reward-gained"] = true;
        
        global $wgNotifyTypeAvailabilityByCategory; // Allow users to control notifications.
        $wgNotifyTypeAvailabilityByCategory['milestones-promotionreached'] = ['web' => true, 'email' => true];
		$wgNotifyTypeAvailabilityByCategory['milestones-reward-gained'] = ['web' => true, 'email' => true];
        
        global $wgEchoUseJobQueue;
        $wgEchoUseJobQueue = !(defined('ENVIRONMENT') && ENVIRONMENT == 'development'); // Use the job queue if not in a development environment.
        
        $echoNotifications['milestones-promotion-reached'] = [
            'category' => 'milestones-promotion-reached',
            'section' => 'alert',
            'group' => 'positive',
			'canNotifyAgent' => true, // This event is processed as a self notification.
            'presentation-model' => \MediaWiki\Extension\Milestones\PromotionReachedPresentationModel::class,
            'user-locators' => ['\MediaWiki\Extension\Milestones\PromotionReachedPresentationModel::locateUser'],
            'immediate' => true //defined(ENVIRONMENT) && ENVIRONMENT == 'development' // Use the job queue if not in a development environment.
        ];

		$echoNotifications['milestones-reward-gained'] = [
            'category' => 'milestones-reward-gained',
            'section' => 'alert',
            'group' => 'positive',
			'canNotifyAgent' => true, // This event is processed as a self notification.
            'presentation-model' => \MediaWiki\Extension\Milestones\RewardGainedPresentationModel::class,
            'user-locators' => ['\MediaWiki\Extension\Milestones\RewardGainedPresentationModel::locateUser'],
            'immediate' => true //defined(ENVIRONMENT) && ENVIRONMENT == 'development' // Use the job queue if not in a development environment.
        ];
        
        $echoNotificationIcons['milestones-promotion-reached']['url'] = 'https://upload.wikimedia.org/wikipedia/commons/c/cb/OOjs_UI_icon_halfStar-ltr.svg';
		$echoNotificationIcons['milestones-reward-gained']['url'] = 'https://upload.wikimedia.org/wikipedia/commons/c/cb/OOjs_UI_icon_halfStar-ltr.svg';
    }

	/**
	 * Event handler definition.
	 * */
	public static function onGetPreferences($user, &$preferences ) 
	{
		global $wgMilestones;

		// Iterate over each milestone class to see if it applies.
		foreach($wgMilestones as $class) {

			if(!($milestone = Milestone::instantiate($class, $user))) { continue; } // Skip if class is invalid.

			if(!$message = $milestone->getMessage()) { continue; } // If that milestone does not display messages in Preferences.

			$preferences[$milestone->getId()] = [
				'type' => 'info',
				'raw' => true,
				'section' => 'milestones',
				//'label' => $milestone->getName(),
				'default' => "<b>".$milestone->getName()."</b>: ".$message
			];

		}

		/* Needed to correctly display the fields because in 
		 * mediawiki.special.preferences.styles.ooui.less there is an override in normal ooui
		 * styling for the preferences page. */
		/*global $wgOut;
		$wgOut->addInlineStyle(
		    '#mw-htmlform-professionnal-infos > .oo-ui-fieldLayout-align-top > .oo-ui-fieldLayout-body > .oo-ui-fieldLayout-header {
        		display: inline-block;
        		width: 20%;
        		padding: 0;
        		vertical-align: middle;
        	}
        
        	#mw-htmlform-professionnal-infos > .oo-ui-fieldLayout-align-top .oo-ui-fieldLayout-help {
        		margin-right: 0;
        	}
        
        	#mw-htmlform-professionnal-infos > .oo-ui-fieldLayout.oo-ui-fieldLayout-align-top > .oo-ui-fieldLayout-body > .oo-ui-fieldLayout-field {
        		display: inline-block;
        		width: 80%;
        		vertical-align: middle;
        	}'
	    );*/
	}

	/** Attempts to apply milestones to a user.
	 * @param User $user
	 */
	private static function _applyMilestones($user) {
		\DeferredUpdates::addCallableUpdate( function () use ( $user ) {
			global $wgMilestones;

			// Iterate over each milestone class to see if it applies.
			foreach($wgMilestones as $class) {
				if(!($milestone = Milestone::instantiate($class, $user))) { continue; } // Skip if class is invalid.

				if($milestone->hasMilestone()) { continue; } // If this milestone has been achieved, skip it.

				if(!$milestone->canApply()) { continue; } // The milestone cannot be applied.

				$milestone->apply();

				$milestone->launchNotification();
			}
		} );
	}

	/**
	 * Add icon for Special:Preferences mobile layout
	 *
	 * @param array &$iconNames Array of icon names for their respective sections.
	 */
	public static function onPreferencesGetIcon( &$iconNames ) {
		$iconNames[ 'milestones' ] = 'halfStar';
	}
}
