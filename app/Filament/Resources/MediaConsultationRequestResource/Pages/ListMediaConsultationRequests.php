<?php

namespace App\Filament\Resources\MediaConsultationRequestResource\Pages;

use App\Filament\Resources\MediaConsultationRequestResource;
use App\Models\MediaConsultationRequest;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

/** List + status tabs for media consultation inbox (localized). */
class ListMediaConsultationRequests extends ListRecords
{
    protected static string $resource = MediaConsultationRequestResource::class;

    public function getTabs(): array
    {
        $base = MediaConsultationRequest::query();

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
