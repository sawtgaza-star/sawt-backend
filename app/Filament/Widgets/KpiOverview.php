<?php

namespace App\Filament\Widgets;

use App\Models\Creator;
use App\Models\Donation;
use App\Models\User;
use App\Models\Video;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KpiOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $donationsThisMonth = Donation::succeeded()->thisMonth()->sum('amount');
        $donationsLastMonth = Donation::succeeded()
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('amount');

        $donationsTrend = $donationsLastMonth > 0
            ? round((($donationsThisMonth - $donationsLastMonth) / $donationsLastMonth) * 100, 1)
            : null;

        $trendDescription = $donationsTrend === null
            ? __('No comparison data')
            : ($donationsTrend >= 0
                ? "+{$donationsTrend}% " . __('vs last month')
                : "{$donationsTrend}% " . __('vs last month'));

        return [
            Stat::make(__('Active Creators'), Creator::active()->count())
                ->description(Creator::count() . ' ' . __('total'))
                ->descriptionIcon('heroicon-m-microphone')
                ->color('primary'),

            Stat::make(__('Published Videos'), Video::published()->count())
                ->description(Video::count() . ' ' . __('total'))
                ->descriptionIcon('heroicon-m-play-circle')
                ->color('success'),

            Stat::make(__('Donations This Month'), number_format($donationsThisMonth, 2) . ' $')
                ->description($trendDescription)
                ->descriptionIcon($donationsTrend !== null && $donationsTrend < 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-arrow-trending-up')
                ->color($donationsTrend !== null && $donationsTrend < 0 ? 'danger' : 'success'),

            Stat::make(__('Total Users'), User::count())
                ->description(__('All registered accounts'))
                ->descriptionIcon('heroicon-m-users')
                ->color('gray'),
        ];
    }
}
