<?php

namespace App\Filament\Widgets;

use App\Models\Donation;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Schema;
use Throwable;

class DonationsChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 2,
    ];

    public function getHeading(): ?string
    {
        return __('التبرعات خلال آخر 6 أشهر');
    }

    public static function canView(): bool
    {
        return true;
    }

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i));

        try {
            $totals = $months->map(function ($month) {
                if (! Schema::hasTable('donations')) {
                    return 0;
                }

                return (float) Donation::succeeded()
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->sum('amount');
            });
        } catch (Throwable) {
            $totals = $months->map(fn () => 0);
        }

        return [
            'datasets' => [
                [
                    'label' => __('إجمالي التبرعات ($)'),
                    'data' => $totals->toArray(),
                    'borderColor' => '#22c55e',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.15)',
                    'fill' => true,
                ],
            ],
            'labels' => $months->map(fn ($m) => $m->translatedFormat('M Y'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
