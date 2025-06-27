<?php

use MediaWiki\Extension\UserWords\UserWords;
use MediaWiki\MediaWikiServices;

return [
    UserWords::SERVICE_NAME => static function(
        MediaWikiServices $services
    ): UserWords {
        return new UserWords(
            $services->getMainConfig(),
            $services->getUserFactory(),
            $services->getUserGroupManager(),
            $services->getUserOptionsManager(),
            $services->getRevisionStore(),
            $services->getDBLoadBalancerFactory(),
        );
    },
];