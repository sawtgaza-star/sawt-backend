<?php

namespace App\Services;

use App\Models\TeamMember;
use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Repositories\Contracts\TeamRepositoryInterface;
use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TeamService
{
    public function __construct(
        protected TeamRepositoryInterface $team,
        protected SettingRepositoryInterface $settings,
    ) {}

    /**
     * Full team listing page (hero from settings + majors + members).
     *
     * @return array<string, mixed>
     *
     * @throws ModelNotFoundException
     */
    public function page(?string $majorSlug = null, ?string $majorUuid = null): array
    {
        if (filled($majorUuid)) {
            $major = $this->team->findActiveMajorByUuid($majorUuid);

            if (! $major) {
                throw (new ModelNotFoundException)->setModel(\App\Models\Major::class, [$majorUuid]);
            }
        } elseif (filled($majorSlug)) {
            $major = $this->team->findActiveMajorBySlug($majorSlug);

            if (! $major) {
                throw (new ModelNotFoundException)->setModel(\App\Models\Major::class, [$majorSlug]);
            }
        }

        return [
            'hero' => $this->hero(),
            'filters' => [
                'all_label' => $this->settings->i18n('team_all_label', 'الكل', 'All'),
            ],
            'majors' => $this->team->activeMajorsWithCounts(),
            'members' => $this->team->activeMembers($majorSlug, $majorUuid),
        ];
    }

    /**
     * Single member profile page (hero + member + intro strip + related members).
     *
     * @return array<string, mixed>
     *
     * @throws ModelNotFoundException
     */
    public function member(string $uuid): array
    {
        $member = $this->team->findMemberByUuid($uuid);

        if (! $member) {
            throw (new ModelNotFoundException)->setModel(TeamMember::class, [$uuid]);
        }

        $limit = (int) ($this->settings->get('team_related_limit', 5) ?: 5);

        return [
            'hero' => $this->hero(),
            'member' => $member,
            'labels' => [
                'bio' => $this->settings->i18n('team_bio_label', 'نبذة عنه', 'About'),
                'experience_suffix' => $this->settings->i18n('team_experience_suffix', 'سنوات من الخبرة', 'years of experience'),
                'follow' => $this->settings->i18n('team_follow_label', 'تابعنا على :', 'Follow us on:'),
            ],
            'intro' => [
                'image_url' => MediaUrl::make($this->settings->get('team_detail_intro_image')),
                'body' => $this->settings->i18n('team_detail_intro'),
            ],
            'related' => [
                'title' => $this->settings->i18n('team_members_section_title', 'اعضاء الفريق', 'Team Members'),
                'view_all' => [
                    'label' => $this->settings->i18n('team_view_all_label', 'عرض الكل', 'View all'),
                    'url' => (string) ($this->settings->get('team_view_all_url', '/team') ?: '/team'),
                ],
                'members' => $this->team->relatedMembers($uuid, $limit),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function hero(): array
    {
        return [
            'image_url' => MediaUrl::make($this->settings->get('team_header_bg')),
            'title' => $this->settings->i18n('team_hero_title', 'صناع الأثر.. الفريق خلف منصة صوت', 'Impact Makers.. The Team Behind Sawt'),
            'description' => $this->settings->i18n('team_hero_desc'),
        ];
    }
}
