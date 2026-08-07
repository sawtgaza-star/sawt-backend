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
                Tables\Columns\TextColumn::make('created_at')->label('التاريخ')->dateTime('Y-m-d H:i')->sortable(),
                Tables\Columns\TextColumn::make('donor_name')->label('المتبرع')->searchable()->placeholder('ضيف'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money(fn ($record) => $record->currency ?? 'USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->label('القسم')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => SupportOptions::categories()[$state] ?? $state)
                    ->colors(['warning' => 'electronic', 'success' => 'transfer', 'info' => 'crypto']),
                Tables\Columns\TextColumn::make('method.name')->label('الوسيلة')->placeholder('—')->limit(22),
                Tables\Columns\TextColumn::make('interval')
                    ->label('الدورية')
                    ->formatStateUsing(fn (string $state) => SupportOptions::intervals()[$state] ?? $state)
                    ->badge()->color('gray'),
                Tables\Columns\TextColumn::make('proofs_count')
                    ->label('الإثباتات')
                    ->counts('proofs')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => SupportOptions::requestStatuses()[$state] ?? $state)
                    ->color(fn (string $state) => SupportOptions::requestStatusColors()[$state] ?? 'gray'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(SupportOptions::requestStatuses())
                    ->default('pending'),
                Tables\Filters\SelectFilter::make('category')
                    ->label('القسم')
                    ->options(SupportOptions::categories()),
                Tables\Filters\SelectFilter::make('support_method_id')
                    ->label('الوسيلة')
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
            Infolists\Components\Section::make('ملخّص الطلب')->schema([
                Infolists\Components\TextEntry::make('uuid')->label('المعرّف')->copyable(),
                Infolists\Components\TextEntry::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => SupportOptions::requestStatuses()[$state] ?? $state)
                    ->color(fn (string $state) => SupportOptions::requestStatusColors()[$state] ?? 'gray'),
                Infolists\Components\TextEntry::make('amount')->label('المبلغ')->money(fn ($record) => $record->currency ?? 'USD'),
                Infolists\Components\TextEntry::make('interval')
                    ->label('الدورية')
                    ->formatStateUsing(fn (string $state) => SupportOptions::intervals()[$state] ?? $state),
                Infolists\Components\TextEntry::make('category')
                    ->label('القسم')
                    ->formatStateUsing(fn (string $state) => SupportOptions::categories()[$state] ?? $state),
                Infolists\Components\TextEntry::make('method.name')->label('الوسيلة')->placeholder('—'),
                Infolists\Components\TextEntry::make('current_step')
                    ->label('تقدّم الويزارد')
                    ->formatStateUsing(fn ($state) => "الخطوة {$state} من ".count(SupportOptions::STEPS)),
                Infolists\Components\TextEntry::make('submitted_at')->label('تاريخ الإرسال')->dateTime('Y-m-d H:i')->placeholder('—'),
            ])->columns(3),

            Infolists\Components\Section::make('بيانات التحويل')->schema([
                Infolists\Components\TextEntry::make('transfer_reference')->label('رقم العملية')->copyable()->placeholder('—'),
                Infolists\Components\TextEntry::make('transfer_date')->label('تاريخ التحويل')->date('Y-m-d')->placeholder('—'),
                Infolists\Components\TextEntry::make('sender_name')->label('اسم المُرسِل')->placeholder('—'),

                Infolists\Components\RepeatableEntry::make('proofs')
                    ->label('لقطات الإثبات')
                    ->schema([
                        Infolists\Components\TextEntry::make('original_name')
                            ->label('الملف')
                            ->formatStateUsing(fn ($state, $record) => new HtmlString(sprintf(
                                '<a href="%s" target="_blank" class="text-primary-600 underline">%s</a>',
                                route('support.proofs.download', ['uuid' => $record->uuid]),
                                e($state ?: basename($record->path)),
                            )))
                            ->html(),
                        Infolists\Components\TextEntry::make('size')
                            ->label('الحجم')
                            ->formatStateUsing(fn (?int $state) => $state ? round($state / 1024).' KB' : '—'),
                        Infolists\Components\TextEntry::make('created_at')->label('وقت الرفع')->dateTime('Y-m-d H:i'),
                    ])
                    ->columns(3)
                    ->columnSpanFull()
                    ->placeholder('لا توجد إثباتات مرفوعة'),
            ])->columns(3),

            Infolists\Components\Section::make('المتبرع ووسيلة التواصل')->schema([
                Infolists\Components\TextEntry::make('donor_name')->label('الاسم')->placeholder('ضيف'),
                Infolists\Components\TextEntry::make('donor_email')->label('البريد')->copyable()->placeholder('—'),
                Infolists\Components\TextEntry::make('donor_phone')->label('الهاتف')->copyable()->placeholder('—'),
                Infolists\Components\TextEntry::make('contact_preference')
                    ->label('وسيلة التواصل المفضّلة')
                    ->formatStateUsing(fn (?string $state) => SupportOptions::contactPreferences()[$state] ?? '—'),
                Infolists\Components\TextEntry::make('contact_value')->label('قيمة التواصل')->placeholder('—'),
                Infolists\Components\IconEntry::make('is_anonymous')->label('تبرع مجهول')->boolean(),
                Infolists\Components\IconEntry::make('subscribe_newsletter')->label('اشترك بالنشرة')->boolean(),
            ])->columns(3),

            Infolists\Components\Section::make('دعم الفريق')->schema([
                Infolists\Components\TextEntry::make('major.name')->label('القسم')->placeholder('—'),
                Infolists\Components\TextEntry::make('teamMember.name')->label('عضو الفريق')->placeholder('—'),
                Infolists\Components\TextEntry::make('message')->label('رسالة المتبرع')->placeholder('—')->columnSpanFull(),
            ])->columns(2),

            Infolists\Components\Section::make('المراجعة')->schema([
                Infolists\Components\TextEntry::make('reviewer.name')->label('المراجِع')->placeholder('—'),
                Infolists\Components\TextEntry::make('reviewed_at')->label('تاريخ المراجعة')->dateTime('Y-m-d H:i')->placeholder('—'),
                Infolists\Components\TextEntry::make('donation.uuid')->label('التبرع المرتبط')->placeholder('—'),
                Infolists\Components\TextEntry::make('admin_note')->label('ملاحظة إدارية')->placeholder('—')->columnSpanFull(),
                Infolists\Components\TextEntry::make('rejection_reason')->label('سبب الرفض')->placeholder('—')->columnSpanFull(),
            ])->columns(3),
        ]);
    }

    /** اعتماد الطلب: يوثّق التبرع ويحدّث رصيد الحملة. */
    public static function approveAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('approve')
            ->label('اعتماد')
            ->icon('heroicon-o-check-badge')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('اعتماد طلب الدعم')
            ->modalDescription('سيتم إنشاء تبرع موثّق بهذا المبلغ وإضافته لرصيد الحملة إن وُجدت.')
            ->form([
                Forms\Components\Textarea::make('admin_note')->label('ملاحظة إدارية (اختياري)')->rows(3),
            ])
            ->visible(fn (SupportRequest $record) => in_array($record->status, ['pending', 'under_review'], true))
            ->action(function (SupportRequest $record, array $data): void {
                try {
                    app(SupportRequestService::class)->approve($record, auth()->user(), $data['admin_note'] ?? null);
                } catch (Throwable $e) {
                    Notification::make()->title('تعذّر اعتماد الطلب')->body($e->getMessage())->danger()->send();

                    return;
                }

                Notification::make()->title('تم اعتماد الطلب وتوثيق التبرع')->success()->send();
            });
    }

    public static function rejectAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('reject')
            ->label('رفض')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->form([
                Forms\Components\Textarea::make('rejection_reason')
                    ->label('سبب الرفض')
                    ->required()
                    ->rows(3),
            ])
            ->visible(fn (SupportRequest $record) => in_array($record->status, ['pending', 'under_review'], true))
            ->action(function (SupportRequest $record, array $data): void {
                app(SupportRequestService::class)->reject($record, auth()->user(), $data['rejection_reason']);

                Notification::make()->title('تم رفض الطلب')->warning()->send();
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
