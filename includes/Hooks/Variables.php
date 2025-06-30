<?php

namespace MediaWiki\Extension\UserWords\Hooks;

use MediaWiki\Extension\UserWords\UserWords;

class Variables implements
    \MediaWiki\Hook\ParserGetVariableValueSwitchHook,
    \MediaWiki\Hook\GetMagicVariableIDsHook
{
    private readonly array $variables;

    public function __construct(
        private readonly UserWords $magicWords
    ) {
        $this->variables = [
            UserWords::MAGIC_USER_FIRST_REVISION,
            UserWords::MAGIC_USER_GROUPS,
            UserWords::MAGIC_USER_REGISTRATION,
            UserWords::MAGIC_USER_LANGUAGE_CODE,
        ];
    }

    /**
     * @inheritdoc
     */
    public function onGetMagicVariableIDs( &$variableIDs ): void {
        $variableIDs = [
            // Spread the original words
            ...$variableIDs,

            // Add all of our words
            ...$this->variables,
        ];
    }

    /**
     * @inheritdoc
     */
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