<?php
/**
 * Redacteur seniorMilestone
 *
 * @file
 * @ingroup Extensions
 * @author Antoine Mercier-Linteau
 * @license GPL 3.0
 */

namespace MediaWiki\Extension\Milestones;

use MediaWiki\MediaWikiServices;
use MediaWiki\Extension\Milestones\Milestone;

class RedacteurSenior extends NiveauEditorial{

    /** @inheritdoc */
    public function __construct($user) { parent:: __construct($user, 1000, 5); }

    /** @inheritdoc */
    public function getName() { return 'Rédacteur senior'; }
}
