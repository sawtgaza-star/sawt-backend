<?php

namespace App\Filament\Resources\Concerns;

use App\Enums\CollaborationTypeKey;
use App\Models\CollaborationJoinRequest;
use App\Services\CollaborationJoinRequestService;
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
            Forms\Components\Section::make('بيانات الطلب')->schema([
                Forms\Components\Select::make('type')
                    ->label('نوع التعاون')
                    ->options(collect(CollaborationTypeKey::cases())->mapWithKeys(
                        fn (CollaborationTypeKey $type) => [$type->value => $type->labelAr()]
                    )->all())
                    ->disabled()
                    ->dehydrated(),
                Forms\Components\TextInput::make('company_name')
                    ->label(fn (?CollaborationJoinRequest $record) => match ($record?->type) {
                        CollaborationTypeKey::Creator => 'الاسم الكامل',
                        CollaborationTypeKey::Other => 'الاسم / اسم المؤسسة',
                        default => 'اسم الشركة / المؤسسة',
                    })
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')->label('البريد')->email()->required()->maxLength(255),
                Forms\Components\TextInput::make('phone')->label('الهاتف')->required()->maxLength(40),
                Forms\Components\TextInput::make('country_code')->label('رمز الدولة')->maxLength(8),
                Forms\Components\TextInput::make('website')->label('الموقع')->maxLength(500),
            ])->columns(2),
            Forms\Components\Section::make('تفاصيل الرعاية / التمويل')
                ->visible(fn (?CollaborationJoinRequest $record) => $record?->type === CollaborationTypeKey::Sponsorship)
                ->schema([
                    Forms\Components\TagsInput::make('payload.support_types')
                        ->label('أنواع الدعم')
                        ->formatStateUsing(fn ($state, ?CollaborationJoinRequest $record) => $record?->payload['support_types'] ?? [])
                        ->disabled()
                        ->dehydrated(false),
                    Forms\Components\Textarea::make('payload.organization_bio')
                        ->label('نبذة عن المؤسسة')
                        ->formatStateUsing(fn ($state, ?CollaborationJoinRequest $record) => $record?->payload['organization_bio'] ?? '')
                        ->disabled()
                        ->dehydrated(false)
                        ->rows(4)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('payload.conditions_notes')
                        ->label('شروط أو مقترحات')
                        ->formatStateUsing(fn ($state, ?CollaborationJoinRequest $record) => $record?->payload['conditions_notes'] ?? '')
                        ->disabled()
                        ->dehydrated(false)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('payload.additional_notes')
                        ->label('ملاحظات إضافية')
                        ->formatStateUsing(fn ($state, ?CollaborationJoinRequest $record) => $record?->payload['additional_notes'] ?? '')
                        ->disabled()
                        ->dehydrated(false)
                        ->columnSpanFull(),
                ]),
            Forms\Components\Section::make('تفاصيل صانع المحتوى')
                ->visible(fn (?CollaborationJoinRequest $record) => $record?->type === CollaborationTypeKey::Creator)
                ->schema([
                    Forms\Components\TagsInput::make('payload.content_types')
                        ->label('أنواع المحتوى')
                        ->formatStateUsing(fn ($state, ?CollaborationJoinRequest $record) => $record?->payload['content_types'] ?? [])
                        ->disabled()
                        ->dehydrated(false),
                    Forms\Components\TextInput::make('payload.followers_count')
                        ->label('عدد المتابعين')
                        ->formatStateUsing(fn ($state, ?CollaborationJoinRequest $record) => (string) ($record?->payload['followers_count'] ?? ''))
                        ->disabled()
                        ->dehydrated(false),
                    Forms\Components\Textarea::make('payload.content_bio')
                        ->label('نبذة عن المحتوى')
                        ->formatStateUsing(fn ($state, ?CollaborationJoinRequest $record) => $record?->payload['content_bio'] ?? '')
                        ->disabled()
                        ->dehydrated(false)
                        ->rows(4)
                        ->columnSpanFull(),
                    Forms\Components\KeyValue::make('payload.socials')
                        ->label('مواقع التواصل')
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
                        ->label('ملاحظات إضافية')
                        ->formatStateUsing(fn ($state, ?CollaborationJoinRequest $record) => $record?->payload['additional_notes'] ?? '')
                        ->disabled()
                        ->dehydrated(false)
                        ->columnSpanFull(),
                    Forms\Components\Placeholder::make('creator_attachment')
                        ->label('فيديو / ملف تعريفي')
                        ->content(fn (?CollaborationJoinRequest $record) => $record?->attachment_url ?: '—')
                        ->columnSpanFull(),
                ]),
            Forms\Components\Section::make('تفاصيل الشراكة الاستراتيجية')
                ->visible(fn (?CollaborationJoinRequest $record) => $record?->type === CollaborationTypeKey::Partnership)
                ->schema([
                    Forms\Components\TagsInput::make('payload.partnership_types')
                        ->label('أنواع الشراكة')
                        ->formatStateUsing(fn ($state, ?CollaborationJoinRequest $record) => $record?->payload['partnership_types'] ?? [])
                        ->disabled()
                        ->dehydrated(false),
                    Forms\Components\Textarea::make('payload.partnership_goal')
                        ->label('نبذة عن المؤسسة وهدف الشراكة')
                        ->formatStateUsing(fn ($state, ?CollaborationJoinRequest $record) => $record?->payload['partnership_goal'] ?? '')
                        ->disabled()
                        ->dehydrated(false)
                        ->rows(4)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('payload.additional_notes')
                        ->label('ملاحظات إضافية')
                        ->formatStateUsing(fn ($state, ?CollaborationJoinRequest $record) => $record?->payload['additional_notes'] ?? '')
                        ->disabled()
                        ->dehydrated(false)
                        ->columnSpanFull(),
                    Forms\Components\Placeholder::make('partnership_attachment')
                        ->label('ملف مرفق')
                        ->content(fn (?CollaborationJoinRequest $record) => $record?->attachment_url ?: '—')
                        ->columnSpanFull(),
                ]),
            Forms\Components\Section::make('تفاصيل تعاون آخر')
                ->visible(fn (?CollaborationJoinRequest $record) => $record?->type === CollaborationTypeKey::Other)
                ->schema([
                    Forms\Components\Textarea::make('payload.collaboration_idea')
                        ->label('فكرة التعاون')
                        ->formatStateUsing(fn ($state, ?CollaborationJoinRequest $record) => $record?->payload['collaboration_idea'] ?? '')
                        ->disabled()
                        ->dehydrated(false)
                        ->rows(4)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('payload.additional_notes')
                        ->label('ملاحظات إضافية')
                        ->formatStateUsing(fn ($state, ?CollaborationJoinRequest $record) => $record?->payload['additional_notes'] ?? '')
                        ->disabled()
                        ->dehydrated(false)
                        ->columnSpanFull(),
                    Forms\Components\Placeholder::make('other_attachment')
                        ->label('ملف مرفق')
                        ->content(fn (?CollaborationJoinRequest $record) => $record?->attachment_url ?: '—')
                        ->columnSpanFull(),
                ]),
            Forms\Components\Section::make('الحالة')->schema([
                Forms\Components\Placeholder::make('status_display')
                    ->label('الحالة')
                    ->content(fn (?CollaborationJoinRequest $record) => match ($record?->status) {
                        'pending' => 'بانتظار المراجعة',
                        'approved' => 'مقبول',
                        'rejected' => 'مرفوض',
                        default => '—',
                    }),
                Forms\Components\Textarea::make('admin_note')->label('ملاحظة الإدارة')->columnSpanFull(),
            ])->columns(1),
        ]);
    }

    public static function collaborationJoinRequestInfolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('بيانات الطلب')->schema([
                Infolists\Components\TextEntry::make('type')
                    ->label('نوع التعاون')
                    ->formatStateUsing(fn (CollaborationTypeKey|string $state) => $state instanceof CollaborationTypeKey ? $state->labelAr() : CollaborationTypeKey::tryFrom((string) $state)?->labelAr() ?? $state),
                Infolists\Components\TextEntry::make('company_name')
                    ->label(fn (CollaborationJoinRequest $record) => match ($record->type) {
                        CollaborationTypeKey::Creator => 'الاسم الكامل',
                        CollaborationTypeKey::Other => 'الاسم / اسم المؤسسة',
                        default => 'اسم الشركة / المؤسسة',
                    }),
                Infolists\Components\TextEntry::make('email')->label('البريد'),
                Infolists\Components\TextEntry::make('phone')->label('الهاتف'),
                Infolists\Components\TextEntry::make('country_code')->label('رمز الدولة'),
                Infolists\Components\TextEntry::make('website')->label('الموقع')->url(fn (?string $state) => $state)->placeholder('—'),
            ])->columns(2),
            Infolists\Components\Section::make('تفاصيل الرعاية / التمويل')
                ->visible(fn (CollaborationJoinRequest $record) => $record->type === CollaborationTypeKey::Sponsorship)
                ->schema([
                    Infolists\Components\TextEntry::make('payload.support_types')
                        ->label('أنواع الدعم')
                        ->formatStateUsing(fn (CollaborationJoinRequest $record) => collect($record->payload['support_types'] ?? [])
                            ->map(fn (string $key) => CollaborationJoinRequest::sponsorshipSupportTypeLabel($key))
                            ->implode('، ') ?: '—'),
                    Infolists\Components\TextEntry::make('payload.organization_bio')
                        ->label('نبذة عن المؤسسة')
                        ->formatStateUsing(fn (CollaborationJoinRequest $record) => $record->payload['organization_bio'] ?? '—')
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('payload.conditions_notes')
                        ->label('شروط أو مقترحات')
                        ->formatStateUsing(fn (CollaborationJoinRequest $record) => $record->payload['conditions_notes'] ?? '—')
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('payload.additional_notes')
                        ->label('ملاحظات إضافية')
                        ->formatStateUsing(fn (CollaborationJoinRequest $record) => $record->payload['additional_notes'] ?? '—')
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('attachment_url')
                        ->label('ملف مرفق')
                        ->url(fn (?string $state) => $state)
                        ->openUrlInNewTab()
                        ->placeholder('—')
                        ->columnSpanFull(),
                ]),
            Infolists\Components\Section::make('تفاصيل صانع المحتوى')
                ->visible(fn (CollaborationJoinRequest $record) => $record->type === CollaborationTypeKey::Creator)
                ->schema([
                    Infolists\Components\TextEntry::make('payload.content_types')
                        ->label('أنواع المحتوى')
                        ->formatStateUsing(fn (CollaborationJoinRequest $record) => collect($record->payload['content_types'] ?? [])->implode('، ') ?: '—'),
                    Infolists\Components\TextEntry::make('payload.followers_count')
                        ->label('عدد المتابعين')
                        ->formatStateUsing(fn (CollaborationJoinRequest $record) => (string) ($record->payload['followers_count'] ?? '—')),
                    Infolists\Components\TextEntry::make('payload.content_bio')
                        ->label('نبذة عن المحتوى')
                        ->formatStateUsing(fn (CollaborationJoinRequest $record) => $record->payload['content_bio'] ?? '—')
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('payload.socials')
                        ->label('مواقع التواصل')
                        ->formatStateUsing(function (CollaborationJoinRequest $record) {
                            $socials = $record->payload['socials'] ?? [];

                            return collect($socials)
                                ->map(fn (array $social) => ($social['platform'] ?? '').': '.($social['url'] ?? ''))
                                ->implode("\n") ?: '—';
                        })
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('payload.additional_notes')
                        ->label('ملاحظات إضافية')
                        ->formatStateUsing(fn (CollaborationJoinRequest $record) => $record->payload['additional_notes'] ?? '—')
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('attachment_url')
                        ->label('فيديو / ملف تعريفي')
                        ->url(fn (?string $state) => $state)
                        ->openUrlInNewTab()
                        ->placeholder('—')
                        ->columnSpanFull(),
                ]),
            Infolists\Components\Section::make('تفاصيل الشراكة الاستراتيجية')
                ->visible(fn (CollaborationJoinRequest $record) => $record->type === CollaborationTypeKey::Partnership)
                ->schema([
                    Infolists\Components\TextEntry::make('payload.partnership_types')
                        ->label('أنواع الشراكة')
                        ->formatStateUsing(fn (CollaborationJoinRequest $record) => collect($record->payload['partnership_types'] ?? [])
                            ->map(fn (string $key) => CollaborationJoinRequest::partnershipTypeLabel($key))
                            ->implode('، ') ?: '—'),
                    Infolists\Components\TextEntry::make('payload.partnership_goal')
                        ->label('نبذة عن المؤسسة وهدف الشراكة')
                        ->formatStateUsing(fn (CollaborationJoinRequest $record) => $record->payload['partnership_goal'] ?? '—')
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('payload.additional_notes')
                        ->label('ملاحظات إضافية')
                        ->formatStateUsing(fn (CollaborationJoinRequest $record) => $record->payload['additional_notes'] ?? '—')
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('attachment_url')
                        ->label('ملف مرفق')
                        ->url(fn (?string $state) => $state)
                        ->openUrlInNewTab()
                        ->placeholder('—')
                        ->columnSpanFull(),
                ]),
            Infolists\Components\Section::make('تفاصيل تعاون آخر')
                ->visible(fn (CollaborationJoinRequest $record) => $record->type === CollaborationTypeKey::Other)
                ->schema([
                    Infolists\Components\TextEntry::make('payload.collaboration_idea')
                        ->label('فكرة التعاون')
                        ->formatStateUsing(fn (CollaborationJoinRequest $record) => $record->payload['collaboration_idea'] ?? '—')
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('payload.additional_notes')
                        ->label('ملاحظات إضافية')
                        ->formatStateUsing(fn (CollaborationJoinRequest $record) => $record->payload['additional_notes'] ?? '—')
                        ->columnSpanFull(),
                    Infolists\Components\TextEntry::make('attachment_url')
                        ->label('ملف مرفق')
                        ->url(fn (?string $state) => $state)
                        ->openUrlInNewTab()
                        ->placeholder('—')
                        ->columnSpanFull(),
                ]),
            Infolists\Components\Section::make('الحالة')->schema([
                Infolists\Components\TextEntry::make('status')->label('الحالة')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending' => 'بانتظار المراجعة',
                        'approved' => 'مقبول',
                        'rejected' => 'مرفوض',
                        default => $state,
                    }),
                Infolists\Components\TextEntry::make('created_at')->label('تاريخ الطلب')->dateTime(),
                Infolists\Components\TextEntry::make('admin_note')->label('ملاحظة الإدارة')->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function collaborationJoinRequestTable(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('type')
                    ->label('النوع')
                    ->formatStateUsing(fn (CollaborationTypeKey|string $state) => $state instanceof CollaborationTypeKey ? $state->labelAr() : CollaborationTypeKey::tryFrom((string) $state)?->labelAr() ?? $state)
                    ->colors([
                        'success' => CollaborationTypeKey::Creator->value,
                        'info' => CollaborationTypeKey::Sponsorship->value,
                        'warning' => CollaborationTypeKey::Partnership->value,
                        'gray' => CollaborationTypeKey::Other->value,
                    ]),
                Tables\Columns\TextColumn::make('company_name')->label('الجهة')->searchable(),
                Tables\Columns\TextColumn::make('email')->label('البريد')->searchable(),
                Tables\Columns\TextColumn::make('phone')->label('الهاتف'),
                Tables\Columns\BadgeColumn::make('status')->label('الحالة')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending' => 'بانتظار المراجعة',
                        'approved' => 'مقبول',
                        'rejected' => 'مرفوض',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')->label('التاريخ')->dateTime('Y-m-d')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('نوع التعاون')
                    ->options(collect(CollaborationTypeKey::cases())->mapWithKeys(
                        fn (CollaborationTypeKey $type) => [$type->value => $type->labelAr()]
                    )->all()),
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'بانتظار المراجعة',
                        'approved' => 'مقبول',
                        'rejected' => 'مرفوض',
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
                ->label('قبول')
                ->color('success')
                ->icon('heroicon-o-check')
                ->visible(fn (CollaborationJoinRequest $record) => $record->status === 'pending')
                ->requiresConfirmation()
                ->action(function (CollaborationJoinRequest $record) {
                    $service = app(CollaborationJoinRequestService::class);
                    $service->approve($record, auth()->id());

                    if ($service->lastEmailError) {
                        Notification::make()
                            ->title('تم قبول الطلب، لكن تعذر إرسال البريد')
                            ->body($service->lastEmailError)
                            ->warning()
                            ->send();

                        return;
                    }

                    Notification::make()->title('تم قبول الطلب وإرسال البريد للجهة')->success()->send();
                }),
            Tables\Actions\Action::make('reject')
                ->label('رفض')
                ->color('danger')
                ->icon('heroicon-o-x-mark')
                ->visible(fn (CollaborationJoinRequest $record) => $record->status === 'pending')
                ->form([
                    Forms\Components\Textarea::make('admin_note')->label('سبب الرفض')->required(),
                ])
                ->action(function (CollaborationJoinRequest $record, array $data) {
                    app(CollaborationJoinRequestService::class)->reject(
                        $record,
                        auth()->id(),
                        $data['admin_note'] ?? null,
                    );
                    Notification::make()->title('تم رفض الطلب')->success()->send();
                }),
            Tables\Actions\DeleteAction::make(),
        ];
    }
}
