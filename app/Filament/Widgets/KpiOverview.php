<?php

namespace App\Filament\Widgets;

use App\Models\Creator;
use App\Models\Donation;
use App\Models\User;
use App\Models\Video;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Schema;
use Throwable;

class KpiOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return true;
    }

    protected function getStats(): array
    {
        try {
            $activeCreators = Schema::hasTable('creators')
                ? Creator::query()->where('status', 'active')->count()
                : 0;
            $totalCreators = Schema::hasTable('creators') ? Creator::count() : 0;

            $publishedVideos = Schema::hasTable('videos')
                ? Video::query()->where('status', 'published')->count()
                : 0;
            $totalVideos = Schema::hasTable('videos') ? Video::count() : 0;

            $donationsThisMonth = 0.0;
            $donationsLastMonth = 0.0;

            if (Schema::hasTable('donations')) {
                $donationsThisMonth = (float) Donation::succeeded()->thisMonth()->sum('amount');
                $donationsLastMonth = (float) Donation::succeeded()
                    ->whereMonth('created_at', now()->subMonth()->month)
                    ->whereYear('created_at', now()->subMonth()->year)
                    ->sum('amount');
            }

            $donationsTrend = $donationsLastMonth > 0
                ? round((($donationsThisMonth - $donationsLastMonth) / $donationsLastMonth) * 100, 1)
                : null;

            $trendDescription = $donationsTrend === null
                ? __('No comparison data')
                : ($donationsTrend >= 0
                    ? "+{$donationsTrend}% ".__('vs last month')
                    : "{$donationsTrend}% ".__('vs last month'));

            $totalUsers = Schema::hasTable('users') ? User::count() : 0;

            return [
                Stat::make(__('Total Users'), $totalUsers)
                    ->description(__('All registered accounts'))
                    ->descriptionIcon('heroicon-m-users')
                    ->color('gray'),

                Stat::make(__('Donations This Month'), number_format($donationsThisMonth, 2).' $')
                    ->description($trendDescription)
                    ->descriptionIcon($donationsTrend !== null && $donationsTrend < 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-arrow-trending-up')
                    ->color($donationsTrend !== null && $donationsTrend < 0 ? 'danger' : 'success'),

                Stat::make(__('Published Videos'), $publishedVideos)
                    ->description($totalVideos.' '.__('total'))
                    ->descriptionIcon('heroicon-m-play-circle')
                    ->color('success'),

                Stat::make(__('Active Creators'), $activeCreators)
                    ->description($totalCreators.' '.__('total'))
                    ->descriptionIcon('heroicon-m-microphone')
                    ->color('primary'),
            ];
        } catch (Throwable) {
            return [
                Stat::make(__('Total Users'), 0)->description(__('All registered accounts'))->color('gray'),
                Stat::make(__('Donations This Month'), '0.00 $')->description(__('No comparison data'))->color('success'),
                Stat::make(__('Published Videos'), 0)->description('0 '.__('total'))->color('success'),
                Stat::make(__('Active Creators'), 0)->description('0 '.__('total'))->color('primary'),
            ];
        }
    }
}
