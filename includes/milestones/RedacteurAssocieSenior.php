<?php
/**
 * Redacteur associé senior Milestone
 *
 * @file
 * @ingroup Extensions
 * @author Antoine Mercier-Linteau
 * @license GPL 3.0
 */

namespace MediaWiki\Extension\Milestones;

use MediaWiki\MediaWikiServices;
use MediaWiki\Extension\Milestones\Milestone;

class RedacteurAssocieSenior extends NiveauEditorial {

    /** @inheritdoc */
    public function __construct($user) { parent:: __construct($user, 50, 4); }

    /** @inheritdoc */
    public function getName() { return 'Rédacteur adjoint associé senior'; }
}
