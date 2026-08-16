<?php

namespace App\Filament\Widgets;

use App\Models\Video;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class TopVideosWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = 'الأكثر مشاهدة';

    public static function canView(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->videosQuery())
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('الفيديو')->limit(30),
                Tables\Columns\TextColumn::make('creator.username')->label('صانع المحتوى'),
                Tables\Columns\TextColumn::make('play_count')->label('المشاهدات')->numeric()->sortable(),
            ])
            ->paginated(false)
            ->emptyStateHeading('لا توجد videos')
            ->emptyStateIcon('heroicon-o-film');
    }

    protected function videosQuery(): Builder
    {
        if (! Schema::hasTable('videos')) {
            return Video::query()->whereRaw('1 = 0');
        }

        return Video::query()->published()->mostViewed()->limit(5);
    }
}
