<?php

namespace App\Filament\Resources\CourseJoinRequestResource\Pages;

use App\Filament\Resources\CourseJoinRequestResource;
use App\Models\CourseJoinRequest;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

/** Lists course join / waitlist requests with status tabs. */
class ListCourseJoinRequests extends ListRecords
{
    protected static string $resource = CourseJoinRequestResource::class;

    public function getTabs(): array
    {
        $base = CourseJoinRequest::query();

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
