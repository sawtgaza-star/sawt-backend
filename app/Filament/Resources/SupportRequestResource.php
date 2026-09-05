<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupportRequestResource\Pages;
use App\Models\SupportRequest;
use App\Services\SupportRequestService;
use App\Support\SupportOptions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Throwable;

/**
 * طلبات الدعم القادمة من الويزارد — شاشة المراجعة والاعتماد.
 * الاعتماد يُنشئ تبرعاً موثّقاً ويزيد رصيد الحملة إن وُجدت.
 */
class SupportRequestResource extends Resource
{
    protected static ?string $model = SupportRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $recordRouteKeyName = 'uuid';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('Finance');
    }

    public static function getNavigationLabel(): string
    {
        return __('Support Requests');
    }

    public static function getModelLabel(): string
    {
        return __('Support Request');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Support Requests');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    protected static ?string $recordTitleAttribute = 'donor_name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['donor_name', 'donor_email', 'transfer_reference', 'uuid'];
    }

    /** عدّاد الطلبات المنتظرة للمراجعة بجانب اسم القائمة. */
    public static function getNavigationBadge(): ?string
    {
        $count = SupportRequest::query()->pending()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    /** المسودّات غير المكتملة لا تُعرض افتراضياً — تُظهرها بالفلتر. */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['method', 'proofs', 'major', 'teamMember']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label(__('التاريخ'))->dateTime('Y-m-d H:i')->sortable(),
                Tables\Columns\TextColumn::make('donor_name')->label(__('المتبرع'))->searchable()->placeholder(__('ضيف')),
                Tables\Columns\TextColumn::make('amount')
                    ->label(__('المبلغ'))
                    ->money(fn ($record) => $record->currency ?? 'USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->label(__('القسم'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => SupportOptions::categories()[$state] ?? $state)
                    ->colors(['warning' => 'electronic', 'success' => 'transfer', 'info' => 'crypto']),
                Tables\Columns\TextColumn::make('method.name')->label(__('الوسيلة'))->placeholder('—')->limit(22),
                Tables\Columns\TextColumn::make('interval')
                    ->label(__('الدورية'))
                    ->formatStateUsing(fn (string $state) => SupportOptions::intervals()[$state] ?? $state)
                    ->badge()->color('gray'),
                Tables\Columns\TextColumn::make('proofs_count')
                    ->label(__('الإثباتات'))
                    ->counts('proofs')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('الحالة'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => SupportOptions::requestStatuses()[$state] ?? $state)
                    ->color(fn (string $state) => SupportOptions::requestStatusColors()[$state] ?? 'gray'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('الحالة'))
                    ->options(SupportOptions::requestStatuses())
                    ->default('pending'),
                Tables\Filters\SelectFilter::make('category')
                    ->label(__('القسم'))
                    ->options(SupportOptions::categories()),
                Tables\Filters\SelectFilter::make('support_method_id')
                    ->label(__('الوسيلة'))
                    ->relationship('method', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                static::approveAction(),
                static::rejectAction(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make(__('ملخّص الطلب'))->schema([
                Infolists\Components\TextEntry::make('uuid')->label(__('المعرّف'))->copyable(),
                Infolists\Components\TextEntry::make('status')
                    ->label(__('الحالة'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => SupportOptions::requestStatuses()[$state] ?? $state)
                    ->color(fn (string $state) => SupportOptions::requestStatusColors()[$state] ?? 'gray'),
                Infolists\Components\TextEntry::make('amount')->label(__('المبلغ'))->money(fn ($record) => $record->currency ?? 'USD'),
                Infolists\Components\TextEntry::make('interval')
                    ->label(__('الدورية'))
                    ->formatStateUsing(fn (string $state) => SupportOptions::intervals()[$state] ?? $state),
                Infolists\Components\TextEntry::make('category')
                    ->label(__('القسم'))
                    ->formatStateUsing(fn (string $state) => SupportOptions::categories()[$state] ?? $state),
                Infolists\Components\TextEntry::make('method.name')->label(__('الوسيلة'))->placeholder('—'),
                Infolists\Components\TextEntry::make('current_step')
                    ->label(__('تقدّم الويزارد'))
                    ->formatStateUsing(fn ($state) => "الخطوة {$state} من ".count(SupportOptions::STEPS)),
                Infolists\Components\TextEntry::make('submitted_at')->label(__('تاريخ الإرسال'))->dateTime('Y-m-d H:i')->placeholder('—'),
            ])->columns(3),

            Infolists\Components\Section::make(__('بيانات التحويل'))->schema([
                Infolists\Components\TextEntry::make('transfer_reference')->label(__('رقم العملية'))->copyable()->placeholder('—'),
                Infolists\Components\TextEntry::make('transfer_date')->label(__('تاريخ التحويل'))->date('Y-m-d')->placeholder('—'),
                Infolists\Components\TextEntry::make('sender_name')->label(__('اسم المُرسِل'))->placeholder('—'),

                Infolists\Components\RepeatableEntry::make('proofs')
                    ->label(__('لقطات الإثبات'))
                    ->schema([
                        Infolists\Components\TextEntry::make('original_name')
                            ->label(__('الملف'))
                            ->formatStateUsing(fn ($state, $record) => new HtmlString(sprintf(
                                '<a href="%s" target="_blank" class="text-primary-600 underline">%s</a>',
                                route('support.proofs.download', ['uuid' => $record->uuid]),
                                e($state ?: basename($record->path)),
                            )))
                            ->html(),
                        Infolists\Components\TextEntry::make('size')
                            ->label(__('الحجم'))
                            ->formatStateUsing(fn (?int $state) => $state ? round($state / 1024).' KB' : '—'),
                        Infolists\Components\TextEntry::make('created_at')->label(__('وقت الرفع'))->dateTime('Y-m-d H:i'),
                    ])
                    ->columns(3)
                    ->columnSpanFull()
                    ->placeholder(__('لا توجد إثباتات مرفوعة')),
            ])->columns(3),

            Infolists\Components\Section::make(__('المتبرع ووسيلة التواصل'))->schema([
                Infolists\Components\TextEntry::make('donor_name')->label(__('الاسم'))->placeholder(__('ضيف')),
                Infolists\Components\TextEntry::make('donor_email')->label(__('البريد'))->copyable()->placeholder('—'),
                Infolists\Components\TextEntry::make('donor_phone')->label(__('الهاتف'))->copyable()->placeholder('—'),
                Infolists\Components\TextEntry::make('contact_preference')
                    ->label(__('وسيلة التواصل المفضّلة'))
                    ->formatStateUsing(fn (?string $state) => SupportOptions::contactPreferences()[$state] ?? '—'),
                Infolists\Components\TextEntry::make('contact_value')->label(__('قيمة التواصل'))->placeholder('—'),
                Infolists\Components\IconEntry::make('is_anonymous')->label(__('تبرع مجهول'))->boolean(),
                Infolists\Components\IconEntry::make('subscribe_newsletter')->label(__('اشترك بالنشرة'))->boolean(),
            ])->columns(3),

            Infolists\Components\Section::make(__('دعم الفريق'))->schema([
                Infolists\Components\TextEntry::make('major.name')->label(__('القسم'))->placeholder('—'),
                Infolists\Components\TextEntry::make('teamMember.name')->label(__('عضو الفريق'))->placeholder('—'),
                Infolists\Components\TextEntry::make('message')->label(__('رسالة المتبرع'))->placeholder('—')->columnSpanFull(),
            ])->columns(2),

            Infolists\Components\Section::make(__('المراجعة'))->schema([
                Infolists\Components\TextEntry::make('reviewer.name')->label(__('المراجِع'))->placeholder('—'),
                Infolists\Components\TextEntry::make('reviewed_at')->label(__('تاريخ المراجعة'))->dateTime('Y-m-d H:i')->placeholder('—'),
                Infolists\Components\TextEntry::make('donation.uuid')->label(__('التبرع المرتبط'))->placeholder('—'),
                Infolists\Components\TextEntry::make('admin_note')->label(__('ملاحظة إدارية'))->placeholder('—')->columnSpanFull(),
                Infolists\Components\TextEntry::make('rejection_reason')->label(__('سبب الرفض'))->placeholder('—')->columnSpanFull(),
            ])->columns(3),
        ]);
    }

    /** اعتماد الطلب: يوثّق التبرع ويحدّث رصيد الحملة. */
    public static function approveAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('approve')
            ->label(__('اعتماد'))
            ->icon('heroicon-o-check-badge')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading(__('اعتماد طلب الدعم'))
            ->modalDescription(__('سيتم إنشاء تبرع موثّق بهذا المبلغ وإضافته لرصيد الحملة إن وُجدت.'))
            ->form([
                Forms\Components\Textarea::make('admin_note')->label(__('ملاحظة إدارية (اختياري)'))->rows(3),
            ])
            ->visible(fn (SupportRequest $record) => in_array($record->status, ['pending', 'under_review'], true))
            ->action(function (SupportRequest $record, array $data): void {
                try {
                    app(SupportRequestService::class)->approve($record, auth()->user(), $data['admin_note'] ?? null);
                } catch (Throwable $e) {
                    Notification::make()->title(__('تعذّر اعتماد الطلب'))->body($e->getMessage())->danger()->send();

                    return;
                }

                Notification::make()->title(__('تم اعتماد الطلب وتوثيق التبرع'))->success()->send();
            });
    }

    public static function rejectAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('reject')
            ->label(__('رفض'))
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->form([
                Forms\Components\Textarea::make('rejection_reason')
                    ->label(__('سبب الرفض'))
                    ->required()
                    ->rows(3),
            ])
            ->visible(fn (SupportRequest $record) => in_array($record->status, ['pending', 'under_review'], true))
            ->action(function (SupportRequest $record, array $data): void {
                app(SupportRequestService::class)->reject($record, auth()->user(), $data['rejection_reason']);

                Notification::make()->title(__('تم رفض الطلب'))->warning()->send();
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupportRequests::route('/'),
            'view' => Pages\ViewSupportRequest::route('/{record}'),
        ];
    }
}
