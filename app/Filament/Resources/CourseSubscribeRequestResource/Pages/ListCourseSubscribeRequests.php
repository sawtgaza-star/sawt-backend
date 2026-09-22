<?php

namespace App\Filament\Resources\CourseSubscribeRequestResource\Pages;

use App\Filament\Resources\CourseSubscribeRequestResource;
use App\Models\CourseSubscribeRequest;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

/** Lists guest course subscribe requests with status tabs. */
class ListCourseSubscribeRequests extends ListRecords
{
    protected static string $resource = CourseSubscribeRequestResource::class;

    public function getTabs(): array
    {
        $base = CourseSubscribeRequest::query();

        $counts = (clone $base)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return [
            'all' => Tab::make(__('الكل'))->badge((clone $base)->count()),
            'pending' => Tab::make(__('بانتظار المراجعة'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'))
                ->badge($counts['pending'] ?? 0),
            'accepted' => Tab::make(__('مقبول'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'accepted'))
                ->badge($counts['accepted'] ?? 0),
            'rejected' => Tab::make(__('مرفوض'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'rejected'))
                ->badge($counts['rejected'] ?? 0),
        ];
    }

    public function getDefaultActiveTab(): string
    {
        return 'pending';
    }
}
