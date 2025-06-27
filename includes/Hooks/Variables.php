<?php

namespace MediaWiki\Extension\UserWords\Hooks;

use MediaWiki\Extension\UserWords\UserWords;
use MediaWiki\Hook\GetMagicVariableIDsHook;

class Variables implements GetMagicVariableIDsHook {
    /**
     * @inheritdoc
     */
    public function onGetMagicVariableIDs( &$variableIDs ): void {
        $variableIDs = [
            // Spread the original words
            ...$variableIDs,
            
            // Add all of our words
            UserWords::MAGIC_USER_FIRST_REVISION,
            UserWords::MAGIC_USER_GROUPS,
            UserWords::MAGIC_USER_REGISTRATION,
            UserWords::MAGIC_USER_LANGUAGE_CODE,
        ];
    }
}