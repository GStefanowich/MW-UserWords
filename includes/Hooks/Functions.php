<?php

namespace MediaWiki\Extension\UserWords\Hooks;

use MediaWiki\Extension\UserWords\UserWords;
use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\Parser\Parser;

class Functions implements ParserFirstCallInitHook {
    public function __construct(
        private readonly UserWords $magicWords
    ) {}

    /**
     * @inheritdoc
     */
    public function onParserFirstCallInit( $parser ): void {
        $this->setFunctionHook($parser, UserWords::MAGIC_USER_GROUPS, 'getUserGroupsFromParser');
    }

    /**
     * Run a check to see if a Magic Variable is enabled, and if enabled then register it
     * @param Parser $parser
     * @param string $id
     * @param string $method
     * @return void
     */
    private function setFunctionHook( Parser $parser, string $id, string $method ): void {
        if ( $this->magicWords->isEnabled($id) ) {
            $parser->setFunctionHook($id, [ $this->magicWords, $method ], SFH_NO_HASH);
        }
    }
}