<?php
/**
 * Redacteur associé Milestone
 *
 * @file
 * @ingroup Extensions
 * @author Antoine Mercier-Linteau
 * @license GPL 3.0
 */

namespace MediaWiki\Extension\Milestones;

use MediaWiki\MediaWikiServices;
use MediaWiki\Extension\Milestones\Milestone;

class RedacteurAssocie extends NiveauEditorial {

    /** @inheritdoc */
    public function __construct($user) { parent:: __construct($user, 250, 3); }

    /** @inheritdoc */
    public function getName() { return 'Rédacteur associé'; }
}
