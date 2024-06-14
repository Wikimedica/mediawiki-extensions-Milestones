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

class RedacteurAdjoint extends NiveauEditorial {

    /** @inheritdoc */
    public function __construct($user) { parent:: __construct($user, 50, 1); }

    /** @inheritdoc */
    public function getName() { return 'Rédacteur adjoint'; }
}
