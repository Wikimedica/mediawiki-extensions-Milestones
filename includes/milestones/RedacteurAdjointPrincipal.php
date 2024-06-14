<?php
/**
 * Redacteur adjoint principal Milestone
 *
 * @file
 * @ingroup Extensions
 * @author Antoine Mercier-Linteau
 * @license GPL 3.0
 */

namespace MediaWiki\Extension\Milestones;

use MediaWiki\MediaWikiServices;
use MediaWiki\Extension\Milestones\Milestone;

class RedacteurAdjointPrincipal extends NiveauEditorial {

    /** @inheritdoc */
    public function __construct($user) { parent:: __construct($user, 100, 2); }

    /** @inheritdoc */
    public function getName() { return 'Rédacteur adjoint principal'; }
}
