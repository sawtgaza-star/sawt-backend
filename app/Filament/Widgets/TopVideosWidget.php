<?php

namespace App\Filament\Widgets;

use App\Models\Video;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopVideosWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = 'الأكثر مشاهدة';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Video::query()->published()->mostViewed()->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('الفيديو')->limit(30),
                Tables\Columns\TextColumn::make('creator.username')->label('صانع المحتوى'),
                Tables\Columns\TextColumn::make('play_count')->label('المشاهدات')->numeric()->sortable(),
            ])
            ->paginated(false);
    }
}
