<?php

namespace App\Filament\Resources\SupportRequestResource\Pages;

use App\Filament\Resources\SupportRequestResource;
use App\Models\SupportRequest;
use App\Support\SupportOptions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListSupportRequests extends ListRecords
{
    protected static string $resource = SupportRequestResource::class;

    /**
     * تبويبات سريعة حسب حالة الطلب — المراجعة اليومية تبدأ من «بانتظار المراجعة».
     */
    public function getTabs(): array
    {
        $counts = SupportRequest::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $tabs = [
            'all' => Tab::make('الكل')->badge(SupportRequest::query()->count()),
        ];

        foreach (SupportOptions::requestStatuses() as $status => $label) {
            $tabs[$status] = Tab::make($label)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $status))
                ->badge($counts[$status] ?? 0);
        }

        return $tabs;
    }

    public function getDefaultActiveTab(): string
    {
        return 'pending';
    }
}
