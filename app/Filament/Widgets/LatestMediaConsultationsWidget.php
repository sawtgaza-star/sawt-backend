<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\MediaConsultationRequestResource;
use App\Models\MediaConsultationRequest;
use App\Support\LocaleText;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

/**
 * Dashboard inbox — latest pending «احجز استشارتك» requests.
 */
class LatestMediaConsultationsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    /** TableWidget uses getTableHeading() for the card title. */
    protected function getTableHeading(): ?string
    {
        return __('استشارات ميديا معلّقة');
    }

    public static function canView(): bool
    {
        return Schema::hasTable('media_consultation_requests');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->requestsQuery())
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(__('الاسم'))->limit(22),
                Tables\Columns\TextColumn::make('service_title')
                    ->label(__('الخدمة'))
                    ->formatStateUsing(fn ($state, MediaConsultationRequest $record) => LocaleText::translation($record->service, 'title')
                        ?: (string) ($record->service_title ?: '—'))
                    ->limit(20),
                Tables\Columns\TextColumn::make('created_at')->label(__('التاريخ'))->since(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label(__('مراجعة'))
                    ->url(fn (MediaConsultationRequest $record) => MediaConsultationRequestResource::getUrl('view', ['record' => $record])),
            ])
            ->paginated(false)
            ->emptyStateHeading(__('لا توجد طلبات معلقة'))
            ->emptyStateIcon('heroicon-o-calendar-days');
    }

    protected function requestsQuery(): Builder
    {
        if (! Schema::hasTable('media_consultation_requests')) {
            return MediaConsultationRequest::query()->whereRaw('1 = 0');
        }

        return MediaConsultationRequest::query()
            ->pending()
            ->with('service')
            ->latest()
            ->limit(5);
    }
}
