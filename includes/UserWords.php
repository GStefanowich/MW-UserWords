<?php

namespace MediaWiki\Extension\UserWords;

use MediaWiki\Config\Config;
use MediaWiki\MainConfigNames;
use MediaWiki\Page\PageReference;
use MediaWiki\Parser\Parser;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\RevisionStore;
use MediaWiki\Title\Title;
use MediaWiki\User\Options\UserOptionsLookup;
use MediaWiki\User\User;
use MediaWiki\User\UserFactory;
use MediaWiki\User\UserGroupManager;
use Wikimedia\Rdbms\LBFactory;
use Wikimedia\Timestamp\ConvertibleTimestamp;
use Wikimedia\Timestamp\TimestampException;

class UserWords {
    public const MAGIC_USER_GROUPS = 'MAG_USERGROUPS';
    public const MAGIC_USER_REGISTRATION = 'MAG_USERREGISTRATIONSTAMP';
    public const MAGIC_USER_FIRST_REVISION = 'MAG_USERFIRSTREVISIONSTAMP';
    public const MAGIC_USER_LANGUAGE_CODE = 'MAG_USERLANGUAGECODE';

    public const SERVICE_NAME = 'ExtUserWords';

    /**
     * Array of all Magic Variables and their config name
     */
    public const ALL_WORDS = [
        UserWords::MAGIC_USER_GROUPS => 'UserGroups',
        UserWords::MAGIC_USER_REGISTRATION => 'UserRegistrationStamp',
        UserWords::MAGIC_USER_FIRST_REVISION => 'UserFirstRevisionStamp',
        UserWords::MAGIC_USER_LANGUAGE_CODE => 'UserLanguageCode',
    ];

    public function __construct(
        private readonly Config            $config,
        private readonly UserFactory       $users,
        private readonly UserGroupManager  $groups,
        private readonly UserOptionsLookup $options,
        private readonly RevisionStore     $revisions,
        private readonly LBFactory         $db,
    ) {}

    /**
     * Get all the enabled Magic Variables
     * @return string[]
     */
    public function getEnabled(): array {
        $keys = [];

        foreach ( array_keys(self::ALL_WORDS) as $key ) {
            if ( $this->isEnabled($key) ) {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    /**
     * Check if a Magic Variable is enabled
     * @param string $key
     * @return bool
     */
    public function isEnabled( string $key ): bool {
        return array_key_exists($key, self::ALL_WORDS) && $this->config->get('UserWords' . self::ALL_WORDS[$key]);
    }

    /**
     * Find the user given a page reference
     * @param ?PageReference $reference
     * @return ?User
     */
    public function getUser( ?PageReference $reference ): ?User {
        if ( $reference instanceof Title && $reference->getNamespace() === NS_USER ) {
            $user = $this->users->newFromName($reference->getBaseText());
            if ( $user && $user->isNamed() && !$user->isHidden() ) {
                return $user;
            }
        }

        return null;
    }

    /**
     * Get the timestamp of when the user registered on the wiki
     * @param Parser $parser
     * @param bool $implicit
     * @return string
     */
    public function getUserGroupsFromParser( Parser $parser, bool $implicit ): string {
        if ( $parser->incrementExpensiveFunctionCount() ) {
            $user = $this->getUser($parser->getPage());
            if ( $user ) {
                return $this->getUserGroups($parser, $user, $implicit);
            }
        }

        return '';
    }

    /**
     * Get the timestamp of when the user registered on the wiki
     * @param Parser $parser
     * @param User $user
     * @return string
     */
    public function getUserRegistration( Parser $parser, User $user ): string {
        $registered = $user->getRegistration();

        if ( $registered ) {
            // Convert the MW-time (Database format YYYYMMDD) to a Unix timestamp
            return $this->mwTimeToUnix($registered) ?? '';
        }

        return '';
    }

    /**
     * Get a comma separated list of groups that the user is a member of
     * @param Parser $parser
     * @param User $user
     * @param bool $implicit
     * @return string
     */
    public function getUserGroups( Parser $parser, User $user, bool $implicit ): string {
        if ( $implicit ) {
            $groups = $this->groups->getUserEffectiveGroups($user);
        } else {
            $groups = $this->groups->getUserGroups($user);
        }

        return implode(',', $groups);
    }

    /**
     * Find the users first Revision in the wikis revision table
     * @param Parser $parser
     * @param User $user
     * @return string
     */
    public function getUserFirstRevision( Parser $parser, User $user ): string {
        // Get the replica db
        $db = $this->db->getReplicaDatabase();

        // Query revisions created by the User
        $query = $this->revisions->newSelectQueryBuilder( $db )
            ->where( [
                'actor_user' => $user->getId(),
            ] )
            ->andWhere($db->bitAnd('rev_deleted', RevisionRecord::DELETED_USER) . ' != ' . RevisionRecord::DELETED_USER)
            ->orderBy('rev_timestamp', 'ASC')
            ->limit(1);

        foreach ( $query->fetchResultSet() as $row ) {
            // Convert the MW-time (Database format YYYYMMDD) to a Unix timestamp
            return $this->mwTimeToUnix($row->rev_timestamp) ?? '';
        }

        return '';
    }

    /**
     * Get the users language code, or fallback to the wikis default if one is not specified
     * @param Parser $parser
     * @param User $user
     * @return string
     */
    public function getUserLanguageCode( Parser $parser, User $user ): string {
        return $this->options->getOption($user, 'language') ?? $this->config->get(MainConfigNames::LanguageCode);
    }

    /**
     * Convert a MW timestamp 'YYYYMMDD' to a Unix timestamp
     * @param string $input MW formatted timestamp
     * @return ?string Parsed timestamp value, null if an error occurs
     */
    private function mwTimeToUnix( string $input ): ?string {
        try {
            $convertible = new ConvertibleTimestamp( $input );
            return $convertible->getTimestamp();
        } catch ( TimestampException $e ) {
            return null;
        }
    }
}