<?php

use MediaWiki\Extension\UserWords\UserWords;

$magicWords = [];

// English localization
$magicWords['en'] = [
    UserWords::MAGIC_USER_LANGUAGE_CODE => [ 1, 'USERLANGUAGECODE' ],
    UserWords::MAGIC_USER_FIRST_REVISION => [ 1, 'USERFIRSTREVISIONSTAMP' ],
    UserWords::MAGIC_USER_GROUPS => [ 1, 'USERGROUPS' ],
    UserWords::MAGIC_USER_REGISTRATION => [ 1, 'USERREGISTRATIONSTAMP' ],
];
