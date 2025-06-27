<?php

namespace MediaWiki\Extension\UserWords\Hooks;

use MediaWiki\Extension\UserWords\UserWords;
use MediaWiki\Hook\ParserFirstCallInitHook;

class Functions implements ParserFirstCallInitHook {
    public function __construct(
        private readonly UserWords $magicWords
    ) {}

    public function onParserFirstCallInit( $parser ): void {
        $parser->setFunctionHook( UserWords::MAGIC_USER_GROUPS, [ $this->magicWords, 'getUserGroupsFromParser' ], SFH_NO_HASH );
    }
}