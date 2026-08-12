<?php

namespace App\Support;

/**
 * Permissions for website / API end-users (role: user).
 * These are NOT Filament Shield permissions — staff use resource policies via Shield.
 */
class WebsiteUserPermissions
{
    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            // Auth / session
            'api.access',
            'website.access',

            // Profile
            'profile.view',
            'profile.update',

            // Browse
            'pages.view',
            'content.view',
            'team.view',
            'creators.view',
            'videos.view',
            'reels.view',
            'courses.view',
            'campaigns.view',

            // Engage
            'courses.join',
            'videos.like',
            'videos.comment',
            'comments.create',
            'comments.delete.own',
            'likes.create',
            'likes.delete.own',

            // Support / payments
            'donations.create',
            'payments.view.own',
            'notifications.view',
        ];
    }
}
