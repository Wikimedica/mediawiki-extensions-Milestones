<?php
/**
 * Base class for a Milestone
 *
 * @file
 * @ingroup Extensions
 * @author Antoine Mercier-Linteau
 * @license GPL 3.0
 */

 namespace MediaWiki\Extension\Milestones;

 use MediaWiki\MediaWikiServices;

 /** Base class for a milestone. */
abstract class Milestone {

    const PROMOTION = 0;

    const REWARD = 1;
    
    protected $user;

	/** @param User $user  
	 * @return list achieved milestones IDs for a user */
	public static function getAchievedMilestones($user) {
			
		if(self::$_achievedMilestones) { return self::$_achievedMilestones; }

        $options = MediaWikiServices::getInstance()->getUserOptionsManager();
		$achieved = $options->getOption($user, 'milestones');
		$achievedMilestones = [];

		if($achieved !== null) {
			$achievedMilestones = json_decode($achieved, true);

			if($achievedMilestones == false) { $achievedMilestones = []; } // If for a reason the json was corrup, start fresh.
		}

		return self::$_achievedMilestones = $achievedMilestones;
	}
	static $_achievedMilestones;


	/** 
	 * @param str the milestone class name (or ID)
	 * @param User $user  
	 * @return Milestone a milestone object*/
	public static function instantiate($class, $user) {
		$dir = dirname(__FILE__).'/milestones/';

		if(!is_file($dir.'/'.$class.'.php')) { return null; } // If the milestone given does not exist, skip it.
				
		include_once ($dir.'/'.$class.'.php');

        $class = "MediaWiki\Extension\Milestones\\".$class;
		
		return new $class( $user );
	}

    /** @param UserIdentity $user the user this milestone potentially applies to */
    public function __construct( $user ) {
        $this->user = $user;
    }

    /** Attempts to apply a milestone to a user.
     * @return bool true if the milestone was applied
     */
    public function apply() {
        self::$_achievedMilestones[$this->getId()] = ["ts_achieved" => wfTimestamp()]; // The milestone has been applied.

        $options = MediaWikiServices::getInstance()->getUserOptionsManager();
        $options->setOption($this->user, 'milestones', json_encode(self::$_achievedMilestones));
        $options->saveOptions($this->user);
    }

    /** Removes that milestone
     */
    public function remove() {
        unset(self::$_achievedMilestones[$this->getId()]);

        $options = MediaWikiServices::getInstance()->getUserOptionsManager();
        $options->setOption($this->user, 'milestones', json_encode(self::$_achievedMilestones));
        $options->saveOptions($this->user);
    }

    /** Attemps to apply a milestone to a user.
     * @return bool true if the milestone can be applied.
     */
    public abstract function canApply();

    /** @return boolean if a notification was launched */
    public function launchNotification() {
        $msg = $this->getNotificationMessage();

        if($msg === false) { return false; }

        \EchoEvent::create([ // Create the event.
            'type' => $this->getType() == self::PROMOTION ? 'milestones-promotion-reached' : 'milestones-reward-gained',
            'title' => $this->user->getUserPage(),
            'extra' => [
                'message' => $msg
            ],
            'agent' => $this->user
        ]);
    }

    /** @return string|false the notification message or false if that milestone does not lauch notifications. */
    protected abstract function getNotificationMessage();

    /** @return str the name of the milestone */
    public abstract function getName();

    /** @return str the ID (or class name) of the milestone */
    public final function getId() {
        $className = get_class($this);
        
        if ($pos = strrpos($className, '\\')) return substr($className, $pos + 1);
        return $pos;
    }

    /** @return int the type of milestone */
    public function getType() { return self::REWARD;}

    /** @return bool true if the user has reached that milestone. */
    public function hasMilestone() { return isset(self::getAchievedMilestones($this->user)[$this->getId()]); }

    /** @return string the message to display in the preferences. */
    public function getMessage() {
        if(!$this->hasMilestone()) { return wfMessage('milestones-milestone-not-achieved')->parse(); }
        
        return wfMessage('milestones-milestone-achieved')->parse();
    }
 }