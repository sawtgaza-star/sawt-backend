<?php

namespace App\Filament\Resources\CollaborationJoinRequestResource\Pages;

use App\Filament\Resources\CollaborationJoinRequestResource;
use App\Models\CollaborationJoinRequest;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCollaborationJoinRequests extends ListRecords
{
    protected static string $resource = CollaborationJoinRequestResource::class;

    public function getTabs(): array
    {
        $base = CollaborationJoinRequest::query();

        $counts = (clone $base)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return [
            'all' => Tab::make(__('All'))->badge((clone $base)->count()),
            'pending' => Tab::make(__('Pending review'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'))
                ->badge($counts['pending'] ?? 0),
            'approved' => Tab::make(__('Approved'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'approved'))
                ->badge($counts['approved'] ?? 0),
            'rejected' => Tab::make(__('Rejected'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'rejected'))
                ->badge($counts['rejected'] ?? 0),
        ];
    }

    public function getDefaultActiveTab(): string
    {
        return 'pending';
    }
}
