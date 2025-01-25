<?php
/**
 * Niveau Editorial abstract Milestone
 *
 * @file
 * @ingroup Extensions
 * @author Antoine Mercier-Linteau
 * @license GPL 3.0
 */

namespace MediaWiki\Extension\Milestones;

use MediaWiki\MediaWikiServices;
use MediaWiki\Extension\Milestones\Milestone;

abstract class NiveauEditorial extends Milestone {

    /** @param int the edit count to reach this editorial level */
    protected $editCount;

    /** @param int the numerical rank of that level */
    protected $level;

    /** Class constructor
     * @param User $user
     * @param int $editCount the edit count to reach this editorial level
     * @param int $level the numerical rank of that level
     */
    public function __construct($user, $editCount, $level) {
        parent::__construct($user);
        $this->editCount = $editCount;
        $this->level = $level;
    }
   
    /** @inheritdoc */
    public function canApply() { return $this->user->getEditCount() > $this->editCount; }

    /** @inheritdoc */
    protected function getNotificationMessage() { return 'Appliquez sur le niveau éditorial de '.lcfirst($this->getName()).' !'; }

    /** @inheritdoc */
    public function getType() { return Milestone::PROMOTION; }

    /** @inheritdoc */
    public function getMessage() { return null; }

    /** @inheritdoc */
    public function hasMilestone() {
        // Override to check if the user was given this editorial level through other means or if it was set before this extension was operationnal.

        if(parent::hasMilestone()) { return true; }

        $userPage = \WikiPage::factory($this->user->getUserPage());

		$content = $userPage->getContent( \MediaWiki\Revision\RevisionRecord::RAW );
		try { $text = \ContentHandler::getContentText( $content ); } 
		catch ( \Exception $e ) { return false; }

		$template = parseForTemplateCall($text, 'Utilisateur');

        if(intval($template[0]['niveau']) >= $this->level) {
            $this->apply(); // The user already has that editorial level.

            return true;
        }

        return false;
    }
}

/**
 * Parses a string for a template call.
 * @param string $content
 * @param string $name the name of the template structure to retrieve.
 * @return array() the structure of the template call and the raw template text.
 * */
function parseForTemplateCall($content, $name)
{
    $matches;
    
    $delimiter_wrap  = '~';
    $delimiter_left  = '{{';/* put YOUR left delimiter here.  */
    $delimiter_right = '}}';/* put YOUR right delimiter here. */
    
    $delimiter_left  = preg_quote( $delimiter_left,  $delimiter_wrap );
    $delimiter_right = preg_quote( $delimiter_right, $delimiter_wrap );
    $pattern = $delimiter_wrap . $delimiter_left
    . '((?:[^' . $delimiter_left . $delimiter_right . ']++|(?R))*)'
            . $delimiter_right . $delimiter_wrap;
            
    //preg_match_all('/{{(.*?)}}/', $content, $matches);
    preg_match_all($pattern, $content, $matches);
    
    if(empty($matches[1])) // If no template calls were found.
    {
        return [false, false];
    }
    
    $matches = $matches[1];
    $raw;
    $template;
    
    foreach($matches as $match)
    {
        $raw = '{{'.$match.'}}';
        $match = trim($match);
        if(strpos($match, $name) === 0) // Find the wanted template
        {
            $template = $match;
            break;
        }
    }
    
    if(!$template)
    {
        return [false, $raw];
    }
    
    $args; // Extract the parameters.
    preg_match_all('/\|((.|\n)*?)\=/', $template, $args);
    $vals; // Extract the values.
    preg_match_all('/\=((.|\n)*?)(\||\z)/', $template, $vals);
    
    if(empty($args[1])) // If no parameters were passed to the template.
    {
        return [[], $raw];
    }
    
    $data = [];
    
    foreach($args[1] as $i => $arg)
    {
        $data[trim($arg)] = trim($vals[1][$i], " \n");
    }
    
    return [$data, $raw];
}
