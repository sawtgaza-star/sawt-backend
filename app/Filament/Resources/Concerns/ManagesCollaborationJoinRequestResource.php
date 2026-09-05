<?php

namespace App\Filament\Resources\Concerns;

use App\Enums\CollaborationTypeKey;
use App\Models\CollaborationJoinRequest;
use App\Services\CollaborationJoinRequestService;
use App\Support\LocaleText;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

trait ManagesCollaborationJoinRequestResource
{
    public static function collaborationJoinRequestForm(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('بيانات الطلب'))->schema([
                Forms\Components\Select::make('type')
                    ->label(__('نوع التعاون'))
                    ->options(collect(CollaborationTypeKey::cases())->mapWithKeys(
                        fn (CollaborationTypeKey $type) => [$type->value => $type->label()]
                    )->all())
                    ->disabled()
                    ->dehydrated(),
                Forms\Components\TextInput::make('company_name')
                    ->label(fn (?CollaborationJoinRequest $record) => match ($record?->type) {
                        CollaborationTypeKey::Creator => __('الاسم الكامل'),
                        CollaborationTypeKey::Other => __('الاسم / اسم المؤسسة'),
                        default => __('اسم الشركة / المؤسسة'),
                    })
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')->label(__('البريد'))->email()->required()->maxLength(255),
                Forms\Components\TextInput::make('phone')->label(__('الهاتف'))->required()->maxLength(40),
                Forms\Components\TextInput::make('country_code')->label(__('رمز الدولة'))->maxLength(8),
                Forms\Components\TextInput::make('website')->label(__('الموقع'))->maxLength(500),
            ])->columns(2),
            Forms\Components\Section::make(__('تفاصيل الرعاية / التمويل'))
                ->visible(fn (?CollaborationJoinRequest $record) => $record?->type === CollaborationTypeKey::Sponsorship)
                ->schema([
                    Forms\Components\TagsInput::make('payload.support_types')
                        ->label(__('أنواع الدعم'))
                        ->formatStateUsing(fn ($state, ?CollaborationJoinRequest $record) => $record?->payload['support_types'] ?? [])
                        ->disabled()
                        ->dehydrated(false),
                    Forms\Components\Textarea::make('payload.organization_bio')
                        ->label(__('نبذة عن المؤسسة'))
                        ->formatStateUsing(fn ($state, ?CollaborationJoinRequest $record) => $record?->payload['organization_bio'] ?? '')
                        ->disabled()
                        ->dehydrated(false)
                        ->rows(4)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('payload.conditions_notes')
                        ->label(__('شروط أو مقترحات'))
                        ->formatStateUsing(fn ($state, ?CollaborationJoinRequest $record) => $record?->payload['conditions_notes'] ?? '')
                        ->disabled()
                        ->dehydrated(false)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('payload.additional_notes')
                        ->label(__('ملاحظات إضافية'))
                        ->formatStateUsing(fn ($state, ?CollaborationJoinRequest $record) => $record?->payload['additional_notes'] ?? '')
                        ->disabled()
                        ->dehydrated(false)
                        ->columnSpanFull(),
                ]),
            Forms\Components\Section::make(__('تفاصيل صانع المحتوى'))
                ->visible(fn (?CollaborationJoinRequest $record) => $record?->type === CollaborationTypeKey::Creator)
                ->schema([
                    Forms\Components\TagsInput::make('payload.content_types')
                        ->label(__('أنواع المحتوى'))
                        ->formatStateUsing(fn ($state, ?CollaborationJoinRequest $record) => $record?->payload['content_types'] ?? [])
                        ->disabled()
                        ->dehydrated(false),
                    Forms\Components\TextInput::make('payload.followers_count')
                        ->label(__('عدد المتابعين'))
                        ->formatStateUsing(fn ($state, ?CollaborationJoinRequest $record) => (string) ($record?->payload['followers_count'] ?? ''))
                        ->disabled()
                        ->dehydrated(false),
                    Forms\Components\Textarea::make('payload.content_bio')
                        ->label(__('نبذة عن المحتوى'))
                        ->formatStateUsing(fn ($state, ?CollaborationJoinRequest $record) => $record?->payload['content_bio'] ?? '')
                        ->disabled()
                        ->dehydrated(false)
                        ->rows(4)
                        ->columnSpanFull(),
                    Forms\Components\KeyValue::make('payload.socials')
                        ->label(__('مواقع التواصل'))
                        ->formatStateUsing(function ($state, ?CollaborationJoinRequest $record) {
                            $socials = $record?->payload['socials'] ?? [];

                            return collect($socials)->mapWithKeys(fn (array $social) => [
                                (string) ($social['platform'] ?? '') => (string) ($social['url'] ?? ''),
                            ])->filter(fn ($url, $platform) => $platform !== '')->all();
                        })
                        ->disabled()
                        ->dehydrated(false)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('payload.additional_notes')
                        ->label(__('ملاحظات إضافية'))
                        ->formatStateUsing(fn ($state, ?CollaborationJoinRequest $record) => $record?->payload['additional_notes'] ?? '')
                        ->disabled()
                        ->dehydrated(false)
                        ->columnSpanFull(),
                    Forms\Components\Placeholder::make('creator_attachment')
                        ->label(__('فيديو / ملف تعريفي'))
                        ->content(fn (?CollaborationJoinRequest $record) => $record?->attachment_url ?: '—')
                        ->columnSpanFull(),
                ]),
            Forms\Components\Section::make(__('تفاصيل الشراكة الاستراتيجية'))
                ->visible(fn (?CollaborationJoinRequest $record) => $record?->type === CollaborationTypeKey::Partnership)
                ->schema([
                    Forms\Components\TagsInput::make('payload.partnership_types')
                        ->label(__('أنواع الشراكة'))
                        ->formatStateUsing(fn ($state, ?CollaborationJoinRequest $record) => $record?->payload['partnership_types'] ?? [])
                        ->disabled()
                        ->dehydrated(false),
                    Forms\Components\Textarea::make('payload.partnership_goal')
                        ->label(__('نبذة عن المؤسسة وهدف الشراكة'))
                        ->formatStateUsing(fn ($state, ?CollaborationJoinRequest $record) => $record?->payload['partnership_goal'] ?? '')
                        ->disabled()
                        ->dehydrated(false)
                        ->rows(4)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('payload.additional_notes')
                        ->label(__('ملاحظات إضافية'))
                        ->formatStateUsing(fn ($state, ?CollaborationJoinRequest $record) => $record?->payload['additional_notes'] ?? '')
                        ->disabled()
                        ->dehydrated(false)
                        ->columnSpanFull(),
                    Forms\Components\Placeholder::make('partnership_attachment')
                        ->label(__('ملف مرفق'))
                        ->content(fn (?CollaborationJoinRequest $record) => $record?->attachment_url ?: '—')
                        ->columnSpanFull(),
                ]),
            Forms\Components\Section::make(__('تفاصيل تعاون آخر'))
                ->visible(fn (?CollaborationJoinRequest $record) => $record?->type === CollaborationTypeKey::Other)
                ->schema([
                    Forms\Components\Textarea::make('payload.collaboration_idea')
                        ->label(__('فكرة التعاون'))
                        ->formatStateUsing(fn ($state, ?CollaborationJoinRequest $record) => $record?->payload['collaboration_idea'] ?? '')
                        ->disabled()
                        ->dehydrated(false)
                        ->rows(4)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('payload.additional_notes')
                        ->label(__('ملاحظات إضافية'))
                        ->formatStateUsing(fn ($state, ?CollaborationJoinRequest $record) => $record?->payload['additional_notes'] ?? '')
                        ->disabled()
                        ->dehydrated(false)
                        ->columnSpanFull(),
                    Forms\Components\Placeholder::make('other_attachment')
                        ->label(__('ملف مرفق'))
                        ->content(fn (?CollaborationJoinRequest $record) => $record?->attachment_url ?: '—')
                        ->columnSpanFull(),
                ]),
            Forms\Components\Section::make(__('الحالة'))->schema([
                Forms\Components\Placeholder::make('status_display')
                    ->label(__('الحالة'))
                    ->content(fn (?CollaborationJoinRequest $record) => match ($record?->status) {
                        'pending' => __('بانتظار المراجعة'),
                        'approved' => __('مقبول'),
                        'rejected' => __('مرفوض'),
                        default => '—',
                    }),
                Forms\Components\Textarea::make('admin_note')->label(__('ملاحظة الإدارة'))->columnSpanFull(),
            ])->columns(1),
        ]);
    }

    public static function collaborationJoinRequestInfolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make(__('بيانات الطلب'))->schema([
                Infolists\Components\TextEntry::make('type')
                    ->label(__('نوع التعاون'))
                    ->formatStateUsing(fn (CollaborationTypeKey|string $state) => $state instanceof CollaborationTypeKey ? $state->label() : CollaborationTypeKey::tryFrom((string) $state)?->label() ?? $state),
                Infolists\Components\TextEntry::make('company_name')
                    ->label(fn (CollaborationJoinRequest $record) => match ($record->type) {
                        CollaborationTypeKey::Creator => __('الاسم الكامل'),
                        CollaborationTypeKey::Other => __('الاسم / اسم المؤسسة'),
                        default => __('اسم الشركة / المؤسسة'),
                    }),
                Infolists\Components\TextEntry::make('email')->label(__('البريد')),
                Infolists\Components\TextEntry::make('phone')->label(__('الهاتف')),
                Infolists\Components\TextEntry::make('country_code')->label(__('رمز الدولة')),
                Infolists\Components\TextEntry::make('website')->label(__('الموقع'))->url(fn (?string $state) => $state)->placeholder('—'),
            ])->columns(2),
            Infolists\Components\Section::make(__('تفاصيل الرعاية / التمويل'))
                ->visible(fn (CollaborationJoinRequest $record) => $record->type === CollaborationTypeKey::Sponsorship)
                ->schema([
                    Infolists\Components\TextEntry::make('payload.support_types')
                        ->label(__('أنواع الدعم'))
                        ->formatStateUsing(fn (CollaborationJoinRequest $record) => collect($record->payload['support_types'] ?? [])
                            ->map(fn (string $key) => CollaborationJoinRequest::sponsorshipSupportTypeLabel($key))
                            ->implode('، ') ?: '—'),
                    Infolists\Components\TextEntry::make('payload.organization_bio')
                        ->label(__('نبذة عن المؤسسة'))
                        ->formatStateUsing(fn (CollaborationJoinRequest $record) => $record->payload['organization_bio'] ?? '—')
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('payload.conditions_notes')
                        ->label(__('شروط أو مقترحات'))
                        ->formatStateUsing(fn (CollaborationJoinRequest $record) => $record->payload['conditions_notes'] ?? '—')
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('payload.additional_notes')
                        ->label(__('ملاحظات إضافية'))
                        ->formatStateUsing(fn (CollaborationJoinRequest $record) => $record->payload['additional_notes'] ?? '—')
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('attachment_url')
                        ->label(__('ملف مرفق'))
                        ->url(fn (?string $state) => $state)
                        ->openUrlInNewTab()
                        ->placeholder('—')
                        ->columnSpanFull(),
                ]),
            Infolists\Components\Section::make(__('تفاصيل صانع المحتوى'))
                ->visible(fn (CollaborationJoinRequest $record) => $record->type === CollaborationTypeKey::Creator)
                ->schema([
                    Infolists\Components\TextEntry::make('payload.content_types')
                        ->label(__('أنواع المحتوى'))
                        ->formatStateUsing(fn (CollaborationJoinRequest $record) => collect($record->payload['content_types'] ?? [])->implode('، ') ?: '—'),
                    Infolists\Components\TextEntry::make('payload.followers_count')
                        ->label(__('عدد المتابعين'))
                        ->formatStateUsing(fn (CollaborationJoinRequest $record) => (string) ($record->payload['followers_count'] ?? '—')),
                    Infolists\Components\TextEntry::make('payload.content_bio')
                        ->label(__('نبذة عن المحتوى'))
                        ->formatStateUsing(fn (CollaborationJoinRequest $record) => $record->payload['content_bio'] ?? '—')
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('payload.socials')
                        ->label(__('مواقع التواصل'))
                        ->formatStateUsing(function (CollaborationJoinRequest $record) {
                            $socials = $record->payload['socials'] ?? [];

                            return collect($socials)
                                ->map(fn (array $social) => ($social['platform'] ?? '').': '.($social['url'] ?? ''))
                                ->implode("\n") ?: '—';
                        })
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('payload.additional_notes')
                        ->label(__('ملاحظات إضافية'))
                        ->formatStateUsing(fn (CollaborationJoinRequest $record) => $record->payload['additional_notes'] ?? '—')
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('attachment_url')
                        ->label(__('فيديو / ملف تعريفي'))
                        ->url(fn (?string $state) => $state)
                        ->openUrlInNewTab()
                        ->placeholder('—')
                        ->columnSpanFull(),
                ]),
            Infolists\Components\Section::make(__('تفاصيل الشراكة الاستراتيجية'))
                ->visible(fn (CollaborationJoinRequest $record) => $record->type === CollaborationTypeKey::Partnership)
                ->schema([
                    Infolists\Components\TextEntry::make('payload.partnership_types')
                        ->label(__('أنواع الشراكة'))
                        ->formatStateUsing(fn (CollaborationJoinRequest $record) => collect($record->payload['partnership_types'] ?? [])
                            ->map(fn (string $key) => CollaborationJoinRequest::partnershipTypeLabel($key))
                            ->implode('، ') ?: '—'),
                    Infolists\Components\TextEntry::make('payload.partnership_goal')
                        ->label(__('نبذة عن المؤسسة وهدف الشراكة'))
                        ->formatStateUsing(fn (CollaborationJoinRequest $record) => $record->payload['partnership_goal'] ?? '—')
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('payload.additional_notes')
                        ->label(__('ملاحظات إضافية'))
                        ->formatStateUsing(fn (CollaborationJoinRequest $record) => $record->payload['additional_notes'] ?? '—')
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('attachment_url')
                        ->label(__('ملف مرفق'))
                        ->url(fn (?string $state) => $state)
                        ->openUrlInNewTab()
                        ->placeholder('—')
                        ->columnSpanFull(),
                ]),
            Infolists\Components\Section::make(__('تفاصيل تعاون آخر'))
                ->visible(fn (CollaborationJoinRequest $record) => $record->type === CollaborationTypeKey::Other)
                ->schema([
                    Infolists\Components\TextEntry::make('payload.collaboration_idea')
                        ->label(__('فكرة التعاون'))
                        ->formatStateUsing(fn (CollaborationJoinRequest $record) => $record->payload['collaboration_idea'] ?? '—')
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('payload.additional_notes')
                        ->label(__('ملاحظات إضافية'))
                        ->formatStateUsing(fn (CollaborationJoinRequest $record) => $record->payload['additional_notes'] ?? '—')
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('attachment_url')
                        ->label(__('ملف مرفق'))
                        ->url(fn (?string $state) => $state)
                        ->openUrlInNewTab()
                        ->placeholder('—')
                        ->columnSpanFull(),
                ]),
            Infolists\Components\Section::make(__('الحالة'))->schema([
                Infolists\Components\TextEntry::make('status')->label(__('Status'))
                    ->formatStateUsing(fn (string $state) => LocaleText::requestStatus($state)),
                Infolists\Components\TextEntry::make('created_at')->label(__('Date'))->dateTime(),
                Infolists\Components\TextEntry::make('admin_note')->label(__('Admin note'))->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function collaborationJoinRequestTable(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('type')
                    ->label(__('النوع'))
                    ->formatStateUsing(fn (CollaborationTypeKey|string $state) => $state instanceof CollaborationTypeKey
                        ? $state->label()
                        : (CollaborationTypeKey::tryFrom((string) $state)?->label() ?? $state))
                    ->colors([
                        'success' => CollaborationTypeKey::Creator->value,
                        'info' => CollaborationTypeKey::Sponsorship->value,
                        'warning' => CollaborationTypeKey::Partnership->value,
                        'gray' => CollaborationTypeKey::Other->value,
                    ]),
                Tables\Columns\TextColumn::make('company_name')->label(__('Name'))->searchable(),
                Tables\Columns\TextColumn::make('email')->label(__('Email'))->searchable(),
                Tables\Columns\TextColumn::make('phone')->label(__('Phone')),
                Tables\Columns\BadgeColumn::make('status')->label(__('Status'))
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn (string $state) => LocaleText::requestStatus($state)),
                Tables\Columns\TextColumn::make('created_at')->label(__('Date'))->dateTime('Y-m-d')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('Collaboration Type'))
                    ->options(collect(CollaborationTypeKey::cases())->mapWithKeys(
                        fn (CollaborationTypeKey $type) => [$type->value => $type->label()]
                    )->all()),
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'pending' => __('Pending review'),
                        'approved' => __('Approved'),
                        'rejected' => __('Rejected'),
                    ]),
            ])
            ->actions(static::collaborationJoinRequestTableActions())
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * @return array<int, Tables\Actions\Action|Tables\Actions\ViewAction|Tables\Actions\EditAction|Tables\Actions\DeleteAction>
     */
    protected static function collaborationJoinRequestTableActions(): array
    {
        return [
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('approve')
                ->label(__('Accept'))
                ->color('success')
                ->icon('heroicon-o-check')
                ->visible(fn (CollaborationJoinRequest $record) => $record->status === 'pending')
                ->requiresConfirmation()
                ->action(function (CollaborationJoinRequest $record) {
                    $service = app(CollaborationJoinRequestService::class);
                    $service->approve($record, auth()->id());

                    if ($service->lastEmailError) {
                        Notification::make()
                            ->title(__('Request accepted, but email could not be sent'))
                            ->body($service->lastEmailError)
                            ->warning()
                            ->send();

                        return;
                    }

                    Notification::make()->title(__('Request accepted and email sent'))->success()->send();
                }),
            Tables\Actions\Action::make('reject')
                ->label(__('Reject'))
                ->color('danger')
                ->icon('heroicon-o-x-mark')
                ->visible(fn (CollaborationJoinRequest $record) => $record->status === 'pending')
                ->form([
                    Forms\Components\Textarea::make('admin_note')->label(__('Rejection reason'))->required(),
                ])
                ->action(function (CollaborationJoinRequest $record, array $data) {
                    app(CollaborationJoinRequestService::class)->reject(
                        $record,
                        auth()->id(),
                        $data['admin_note'] ?? null,
                    );
                    Notification::make()->title(__('Request rejected'))->success()->send();
                }),
            Tables\Actions\DeleteAction::make(),
        ];
    }
}
