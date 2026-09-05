<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CreatorJoinRequestResource;
use App\Filament\Resources\DonationResource;
use App\Filament\Resources\MediaConsultationRequestResource;
use App\Filament\Resources\SupportRequestResource;
use App\Models\CollaborationJoinRequest;
use App\Models\CreatorJoinRequest;
use App\Models\Donation;
use App\Models\MediaConsultationRequest;
use App\Models\SupportRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Top dashboard row — pending work that needs admin attention today.
 */
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
            $pendingConsultations = Schema::hasTable('media_consultation_requests')
                ? MediaConsultationRequest::query()->pending()->count()
                : 0;

            $pendingCreatorJoins = Schema::hasTable('creator_join_requests')
                ? CreatorJoinRequest::query()->pending()->count()
                : 0;

            $pendingSupport = Schema::hasTable('support_requests')
                ? SupportRequest::query()->pending()->count()
                : 0;

            $pendingCollab = Schema::hasTable('collaboration_join_requests')
                ? CollaborationJoinRequest::query()->pending()->count()
                : 0;

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

            return [
                Stat::make(__('استشارات ميديا معلّقة'), $pendingConsultations)
                    ->description(__('بانتظار المراجعة'))
                    ->descriptionIcon('heroicon-m-calendar-days')
                    ->color($pendingConsultations > 0 ? 'warning' : 'gray')
                    ->url(MediaConsultationRequestResource::getUrl('index')),

                Stat::make(__('طلبات انضمام معلّقة'), $pendingCreatorJoins)
                    ->description(__('صنّاع المحتوى'))
                    ->descriptionIcon('heroicon-m-user-plus')
                    ->color($pendingCreatorJoins > 0 ? 'warning' : 'gray')
                    ->url(CreatorJoinRequestResource::getUrl('index')),

                Stat::make(__('طلبات دعم معلّقة'), $pendingSupport)
                    ->description($pendingCollab.' '.__('طلبات تعاون معلّقة'))
                    ->descriptionIcon('heroicon-m-heart')
                    ->color($pendingSupport > 0 ? 'danger' : 'gray')
                    ->url(SupportRequestResource::getUrl('index')),

                Stat::make(__('تبرعات هذا الشهر'), number_format($donationsThisMonth, 2).' $')
                    ->description($trendDescription)
                    ->descriptionIcon($donationsTrend !== null && $donationsTrend < 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-arrow-trending-up')
                    ->color($donationsTrend !== null && $donationsTrend < 0 ? 'danger' : 'success')
                    ->url(DonationResource::getUrl('index')),
            ];
        } catch (Throwable) {
            return [
                Stat::make(__('استشارات ميديا معلّقة'), 0)->color('gray'),
                Stat::make(__('طلبات انضمام معلّقة'), 0)->color('gray'),
                Stat::make(__('طلبات دعم معلّقة'), 0)->color('gray'),
                Stat::make(__('تبرعات هذا الشهر'), '0.00 $')->color('success'),
            ];
        }
    }
}
