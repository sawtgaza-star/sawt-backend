<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupportSubscriptionResource\Pages;
use App\Models\SupportSubscription;
use App\Services\SupportSubscriptionService;
use App\Support\SupportOptions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Throwable;

/**
 * اشتراكات الدعم الدورية (PayPal Billing) — عرض ومزامنة وإلغاء.
 * الإنشاء يتم من الواجهة فقط، فالشاشة للقراءة والإدارة.
 */
class SupportSubscriptionResource extends Resource
{
    protected static ?string $model = SupportSubscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected static ?string $recordRouteKeyName = 'uuid';

    protected static ?int $navigationSort = 6;

    public static function getNavigationGroup(): ?string
    {
        return __('Finance');
    }

    public static function getNavigationLabel(): string
    {
        return __('Support Subscriptions');
    }

    public static function getModelLabel(): string
    {
        return __('Support Subscription');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Support Subscriptions');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['subscriber_name', 'subscriber_email', 'gateway_subscription_id', 'uuid'];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = SupportSubscription::query()->active()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('plan');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('التاريخ')->dateTime('Y-m-d')->sortable(),
                Tables\Columns\TextColumn::make('subscriber_name')->label('المشترك')->searchable()->placeholder('—'),
                Tables\Columns\TextColumn::make('subscriber_email')->label('البريد')->searchable()->limit(24)->placeholder('—'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money(fn ($record) => $record->currency ?? 'USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('interval')
                    ->label('الدورية')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => SupportOptions::intervals()[$state] ?? $state)
                    ->colors(['success' => 'monthly', 'warning' => 'yearly']),
                Tables\Columns\TextColumn::make('cycles_completed')->label('دورات مُحصَّلة')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('total_paid')
                    ->label('الإجمالي المحصَّل')
                    ->money(fn ($record) => $record->currency ?? 'USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('next_billing_at')->label('التحصيل القادم')->date('Y-m-d')->placeholder('—'),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => SupportOptions::subscriptionStatuses()[$state] ?? $state)
                    ->color(fn (string $state) => SupportOptions::subscriptionStatusColors()[$state] ?? 'gray'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(SupportOptions::subscriptionStatuses()),
                Tables\Filters\SelectFilter::make('interval')
                    ->label('الدورية')
                    ->options(['monthly' => 'شهري', 'yearly' => 'سنوي']),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('sync')
                    ->label('مزامنة')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->visible(fn (SupportSubscription $record) => filled($record->gateway_subscription_id))
                    ->action(function (SupportSubscription $record): void {
                        try {
                            app(SupportSubscriptionService::class)->activate($record);
                        } catch (Throwable $e) {
                            Notification::make()->title('تعذّرت المزامنة مع PayPal')->body($e->getMessage())->danger()->send();

                            return;
                        }

                        Notification::make()->title('تمت مزامنة حالة الاشتراك')->success()->send();
                    }),

                Tables\Actions\Action::make('cancel')
                    ->label('إلغاء')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\TextInput::make('reason')->label('السبب')->default('Cancelled by admin'),
                    ])
                    ->visible(fn (SupportSubscription $record) => $record->isCancellable())
                    ->action(function (SupportSubscription $record, array $data): void {
                        try {
                            app(SupportSubscriptionService::class)->cancel($record, $data['reason'] ?? 'Cancelled by admin');
                        } catch (Throwable $e) {
                            Notification::make()->title('تعذّر إلغاء الاشتراك')->body($e->getMessage())->danger()->send();

                            return;
                        }

                        Notification::make()->title('تم إلغاء الاشتراك')->warning()->send();
                    }),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('الاشتراك')->schema([
                Infolists\Components\TextEntry::make('uuid')->label('المعرّف')->copyable(),
                Infolists\Components\TextEntry::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => SupportOptions::subscriptionStatuses()[$state] ?? $state)
                    ->color(fn (string $state) => SupportOptions::subscriptionStatusColors()[$state] ?? 'gray'),
                Infolists\Components\TextEntry::make('amount')->label('المبلغ')->money(fn ($record) => $record->currency ?? 'USD'),
                Infolists\Components\TextEntry::make('interval')
                    ->label('الدورية')
                    ->formatStateUsing(fn (string $state) => SupportOptions::intervals()[$state] ?? $state),
                Infolists\Components\TextEntry::make('gateway_subscription_id')->label('معرّف PayPal')->copyable()->placeholder('—'),
                Infolists\Components\TextEntry::make('gateway_plan_id')->label('خطة PayPal')->copyable()->placeholder('—'),
            ])->columns(3),

            Infolists\Components\Section::make('المشترك')->schema([
                Infolists\Components\TextEntry::make('subscriber_name')->label('الاسم')->placeholder('—'),
                Infolists\Components\TextEntry::make('subscriber_email')->label('البريد')->copyable()->placeholder('—'),
                Infolists\Components\TextEntry::make('user.name')->label('الحساب المرتبط')->placeholder('ضيف'),
            ])->columns(3),

            Infolists\Components\Section::make('التحصيل')->schema([
                Infolists\Components\TextEntry::make('started_at')->label('تاريخ البدء')->dateTime('Y-m-d H:i')->placeholder('—'),
                Infolists\Components\TextEntry::make('next_billing_at')->label('التحصيل القادم')->dateTime('Y-m-d H:i')->placeholder('—'),
                Infolists\Components\TextEntry::make('cancelled_at')->label('تاريخ الإلغاء')->dateTime('Y-m-d H:i')->placeholder('—'),
                Infolists\Components\TextEntry::make('cycles_completed')->label('عدد الدورات'),
                Infolists\Components\TextEntry::make('total_paid')->label('الإجمالي المحصَّل')->money(fn ($record) => $record->currency ?? 'USD'),
            ])->columns(3),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupportSubscriptions::route('/'),
            'view' => Pages\ViewSupportSubscription::route('/{record}'),
        ];
    }
}
