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
        if ( !in_array($magicWordId, $this->variables) ) {
            return;
        }

        // Increment the expensive parser count
        if ( !$parser->incrementExpensiveFunctionCount() ) {
            $ret = $variableCache[$magicWordId] = '';
            return;
        }

        // Try getting the user from the current page
        $user = $this->magicWords->getUser($parser->getPage());
        if ( !$user ) {
            $ret = $variableCache[$magicWordId] = '';
            return;
        }

        $ret = $variableCache[$magicWordId] = match ( $magicWordId ) {
            UserWords::MAGIC_USER_REGISTRATION => $this->magicWords->getUserRegistration($parser, $user),
            UserWords::MAGIC_USER_GROUPS => $this->magicWords->getUserGroups($parser, $user, false),
            UserWords::MAGIC_USER_FIRST_REVISION => $this->magicWords->getUserFirstRevision($parser, $user),
            UserWords::MAGIC_USER_LANGUAGE_CODE => $this->magicWords->getUserLanguageCode($parser, $user),
            default => '',
        };
    }
}