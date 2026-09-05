<?php

namespace App\Filament\Widgets;

use App\Models\Course;
use App\Models\Creator;
use App\Models\MediaServiceItem;
use App\Models\MediaWork;
use App\Models\User;
use App\Models\Video;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Second dashboard row — platform content volume at a glance.
 */
class ContentStatsOverview extends BaseWidget
{
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return true;
    }

    protected function getStats(): array
    {
        try {
            $users = Schema::hasTable('users') ? User::query()->count() : 0;
            $creators = Schema::hasTable('creators')
                ? Creator::query()->where('status', 'active')->count()
                : 0;
            $videos = Schema::hasTable('videos')
                ? Video::query()->where('status', 'published')->count()
                : 0;
            $courses = Schema::hasTable('courses')
                ? Course::query()->published()->count()
                : 0;
            $services = Schema::hasTable('media_services')
                ? MediaServiceItem::query()->active()->count()
                : 0;
            $works = Schema::hasTable('media_works')
                ? MediaWork::query()->active()->count()
                : 0;

            return [
                Stat::make(__('Total Users'), $users)
                    ->description(__('All registered accounts'))
                    ->descriptionIcon('heroicon-m-users')
                    ->color('gray'),

                Stat::make(__('Active Creators'), $creators)
                    ->description($videos.' '.__('فيديوهات منشورة'))
                    ->descriptionIcon('heroicon-m-microphone')
                    ->color('primary'),

                Stat::make(__('دورات الحاضنة'), $courses)
                    ->description(__('منشورة'))
                    ->descriptionIcon('heroicon-m-academic-cap')
                    ->color('info'),

                Stat::make(__('صوت ميديا'), $services.' / '.$works)
                    ->description(__('خدمات') .' / '.__('أعمال'))
                    ->descriptionIcon('heroicon-m-film')
                    ->color('success'),
            ];
        } catch (Throwable) {
            return [
                Stat::make(__('Total Users'), 0)->color('gray'),
                Stat::make(__('Active Creators'), 0)->color('primary'),
                Stat::make(__('دورات الحاضنة'), 0)->color('info'),
                Stat::make(__('صوت ميديا'), '0 / 0')->color('success'),
            ];
        }
    }
}
