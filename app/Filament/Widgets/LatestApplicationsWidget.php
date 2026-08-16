<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CreatorJoinRequestResource;
use App\Models\CreatorJoinRequest;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class LatestApplicationsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = 'طلبات انضمام بانتظار المراجعة';

    public static function canView(): bool
    {
        return Schema::hasTable('creator_join_requests');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->requestsQuery())
            ->columns([
                Tables\Columns\TextColumn::make('full_name')->label('الاسم'),
                Tables\Columns\TextColumn::make('email')->label('البريد')->limit(24),
                Tables\Columns\TextColumn::make('created_at')->label('تاريخ التقديم')->since(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('مراجعة')
                    ->url(fn (CreatorJoinRequest $record) => CreatorJoinRequestResource::getUrl('view', ['record' => $record])),
            ])
            ->paginated(false)
            ->emptyStateHeading('لا توجد طلبات معلقة')
            ->emptyStateIcon('heroicon-o-inbox');
    }

    protected function requestsQuery(): Builder
    {
        if (! Schema::hasTable('creator_join_requests')) {
            return CreatorJoinRequest::query()->whereRaw('1 = 0');
        }

        return CreatorJoinRequest::query()->pending()->latest()->limit(5);
    }
}
