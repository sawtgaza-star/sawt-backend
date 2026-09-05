<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaConsultationRequestResource\Pages;
use App\Models\MediaConsultationRequest;
use App\Models\MediaServiceItem;
use App\Services\MediaConsultationRequestService;
use App\Support\LocaleText;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Admin inbox for «احجز استشارتك» — view + approve/reject (emails applicant).
 * All chrome labels use __() so AR/EN switcher works.
 */
class MediaConsultationRequestResource extends Resource
{
    protected static ?string $model = MediaConsultationRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $recordRouteKeyName = 'uuid';

    protected static ?string $slug = 'media-consultation-requests';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('Sawt Media');
    }

    public static function getNavigationLabel(): string
    {
        return __('Consultation Requests');
    }

    public static function getModelLabel(): string
    {
        return __('Consultation Request');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Consultation Requests');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = MediaConsultationRequest::query()->pending()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    /** Service title in current UI locale (relation first, then snapshot). */
    public static function serviceLabel(MediaConsultationRequest $record): string
    {
        $fromRelation = LocaleText::translation($record->service, 'title');

        return $fromRelation !== '' ? $fromRelation : (string) ($record->service_title ?: '—');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('Request data'))->schema([
                Forms\Components\TextInput::make('name')->label(__('Full name'))->disabled(),
                Forms\Components\TextInput::make('email')->label(__('Email'))->disabled(),
                Forms\Components\TextInput::make('phone')->label(__('Phone'))->disabled(),
                Forms\Components\TextInput::make('country_code')->label(__('Country code'))->disabled(),
                Forms\Components\Placeholder::make('service_label')
                    ->label(__('Service'))
                    ->content(fn (?MediaConsultationRequest $record) => $record ? static::serviceLabel($record) : '—'),
                Forms\Components\TextInput::make('service_slug')->label(__('Service slug'))->disabled(),
                Forms\Components\Select::make('status')
                    ->label(__('Status'))
                    ->options([
                        'pending' => __('Pending review'),
                        'approved' => __('Approved'),
                        'rejected' => __('Rejected'),
                    ])
                    ->disabled(),
                Forms\Components\Textarea::make('admin_note')->label(__('Admin note'))->rows(3)->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make(__('Applicant data'))->schema([
                Infolists\Components\TextEntry::make('uuid')->label(__('Request number')),
                Infolists\Components\TextEntry::make('name')->label(__('Full name')),
                Infolists\Components\TextEntry::make('email')->label(__('Email')),
                Infolists\Components\TextEntry::make('phone')
                    ->label(__('Phone'))
                    ->formatStateUsing(fn (MediaConsultationRequest $record) => $record->fullPhone()),
                Infolists\Components\TextEntry::make('service_title')
                    ->label(__('Required service'))
                    ->formatStateUsing(fn ($state, MediaConsultationRequest $record) => static::serviceLabel($record))
                    ->placeholder('—'),
                Infolists\Components\TextEntry::make('service_slug')->label(__('Slug'))->placeholder('—'),
                Infolists\Components\TextEntry::make('created_at')->label(__('Date'))->dateTime(),
            ])->columns(2),
            Infolists\Components\Section::make(__('Status'))->schema([
                Infolists\Components\TextEntry::make('status')
                    ->label(__('Status'))
                    ->formatStateUsing(fn (string $state) => LocaleText::requestStatus($state)),
                Infolists\Components\TextEntry::make('reviewed_at')->label(__('Review date'))->dateTime()->placeholder('—'),
                Infolists\Components\TextEntry::make('admin_note')->label(__('Admin note'))->placeholder('—')->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('service'))
            ->columns([
                Tables\Columns\TextColumn::make('uuid')->label(__('Request number'))->searchable()->copyable(),
                Tables\Columns\TextColumn::make('name')->label(__('Name'))->searchable(),
                Tables\Columns\TextColumn::make('email')->label(__('Email'))->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('Phone'))
                    ->formatStateUsing(fn (MediaConsultationRequest $record) => $record->fullPhone()),
                Tables\Columns\TextColumn::make('service_title')
                    ->label(__('Service'))
                    ->formatStateUsing(fn ($state, MediaConsultationRequest $record) => static::serviceLabel($record))
                    ->placeholder('—')
                    ->limit(30),
                Tables\Columns\BadgeColumn::make('status')->label(__('Status'))
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn (string $state) => LocaleText::requestStatus($state)),
                Tables\Columns\TextColumn::make('created_at')->label(__('Date'))->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'pending' => __('Pending review'),
                        'approved' => __('Approved'),
                        'rejected' => __('Rejected'),
                    ]),
                Tables\Filters\SelectFilter::make('media_service_id')
                    ->label(__('Service'))
                    ->options(fn () => MediaServiceItem::query()
                        ->orderBy('sort_order')
                        ->get()
                        ->mapWithKeys(fn (MediaServiceItem $s) => [
                            $s->id => LocaleText::translation($s, 'title') ?: $s->slug,
                        ])),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label(__('Accept'))
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->visible(fn (MediaConsultationRequest $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading(__('Accept consultation request'))
                    ->modalDescription(__('An acceptance email will be sent to the applicant.'))
                    ->action(function (MediaConsultationRequest $record) {
                        $service = app(MediaConsultationRequestService::class);
                        $service->approve($record, auth()->id());
                        static::notifyDecisionResult($service, approved: true);
                    }),
                Tables\Actions\Action::make('reject')
                    ->label(__('Reject'))
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->visible(fn (MediaConsultationRequest $record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('admin_note')
                            ->label(__('Rejection reason'))
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (MediaConsultationRequest $record, array $data) {
                        $service = app(MediaConsultationRequestService::class);
                        $service->reject($record, auth()->id(), $data['admin_note'] ?? null);
                        static::notifyDecisionResult($service, approved: false);
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /** Filament toast after approve/reject (warn if mail failed). */
    public static function notifyDecisionResult(MediaConsultationRequestService $service, bool $approved): void
    {
        if ($service->lastEmailError) {
            Notification::make()
                ->title($approved
                    ? __('Request accepted, but email could not be sent')
                    : __('Request rejected, but email could not be sent'))
                ->body($service->lastEmailError)
                ->warning()
                ->send();

            return;
        }

        Notification::make()
            ->title($approved
                ? __('Request accepted and email sent to the applicant')
                : __('Request rejected and email sent to the applicant'))
            ->success()
            ->send();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMediaConsultationRequests::route('/'),
            'view' => Pages\ViewMediaConsultationRequest::route('/{record}'),
        ];
    }
}
