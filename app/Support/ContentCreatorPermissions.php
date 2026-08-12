<?php

namespace App\Support;

/**
 * Content creators = same website powers as users + profile extras.
 * Extra data lives on `creators` + `creator_socials` (bio, photo, followers, social links).
 */
class ContentCreatorPermissions
{
    /**
     * @return list<string>
     */
    public static function extras(): array
    {
        return [
            'creator.profile.view',
            'creator.profile.update',
            'creator.socials.manage',
            'creator.stats.view.own',
        ];
    }

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return array_values(array_unique([
            ...WebsiteUserPermissions::all(),
            ...self::extras(),
        ]));
    }
}
