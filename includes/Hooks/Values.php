<?php

namespace MediaWiki\Extension\UserWords\Hooks;

use MediaWiki\Extension\UserWords\UserWords;
use MediaWiki\Hook\ParserGetVariableValueSwitchHook;

class Values implements ParserGetVariableValueSwitchHook {
    public function __construct(
        private readonly UserWords $magicWords
    ) {}

    public function onParserGetVariableValueSwitch( $parser, &$variableCache, $magicWordId, &$ret, $frame ): void {
        $user = $this->magicWords->getUser($parser->getPage());
        if ( !$user ) {
            return;
        }

        switch ( $magicWordId ) {
            case UserWords::MAGIC_USER_REGISTRATION: {
                $parser->incrementExpensiveFunctionCount();
                $ret = $variableCache[$magicWordId] = $this->magicWords->getUserRegistration($parser, $user);
                break;
            }
            case UserWords::MAGIC_USER_GROUPS: {
                $parser->incrementExpensiveFunctionCount();
                $ret = $variableCache[$magicWordId] = $this->magicWords->getUserGroups($parser, $user, false);
                break;
            }
            case UserWords::MAGIC_USER_FIRST_REVISION: {
                $parser->incrementExpensiveFunctionCount();
                $ret = $variableCache[$magicWordId] = $this->magicWords->getUserFirstRevision($parser, $user);
                break;
            }
            case UserWords::MAGIC_USER_LANGUAGE_CODE: {
                $ret = $variableCache[$magicWordId] = $this->magicWords->getUserLanguageCode($parser, $user);
                break;
            }
        }
    }
}