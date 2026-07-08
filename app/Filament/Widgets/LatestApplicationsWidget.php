<?php

namespace App\Filament\Widgets;

use App\Models\CreatorApplication;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestApplicationsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = 'طلبات انضمام بانتظار المراجعة';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                CreatorApplication::query()->where('status', 'pending')->latest()->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('الاسم'),
                Tables\Columns\TextColumn::make('content_type')->label('نوع المحتوى'),
                Tables\Columns\TextColumn::make('created_at')->label('تاريخ التقديم')->since(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('مراجعة')
                    ->url(fn (CreatorApplication $record) => route('filament.admin.resources.creator-applications.view', $record)),
            ])
            ->paginated(false);
    }
}
