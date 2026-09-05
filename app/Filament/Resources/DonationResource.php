<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DonationResource\Pages;
use App\Models\Donation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DonationResource extends Resource
{
    protected static ?string $model = Donation::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $recordRouteKeyName = 'uuid';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('Finance');
    }

    public static function getNavigationLabel(): string
    {
        return __('Donations');
    }

    public static function getModelLabel(): string
    {
        return __('Donation');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Donations');
    }

    protected static ?string $recordTitleAttribute = 'donor_name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['donor_name', 'donor_email', 'uuid', 'campaign.title'];
    }

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string | \Illuminate\Contracts\Support\Htmlable
    {
        return $record->donor_name
            ?: $record->donor_email
            ?: __('Donation') . ' #' . $record->uuid;
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return array_filter([
            __('Campaign') => $record->campaign?->getTranslation('title', app()->getLocale())
                ?: $record->campaign?->getTranslation('title', 'ar'),
            __('Status') => $record->status,
        ]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('campaign_id')
                ->label(__('الحملة'))
                ->relationship('campaign', 'title')
                ->searchable()
                ->preload(),

            Forms\Components\Select::make('user_id')
                ->label(__('حساب مستخدم (اختياري)'))
                ->relationship('user', 'name')
                ->searchable()
                ->preload(),

            Forms\Components\TextInput::make('donor_name')->label(__('اسم المتبرع')),
            Forms\Components\TextInput::make('donor_email')->label(__('بريد المتبرع'))->email(),

            Forms\Components\TextInput::make('amount')->label(__('المبلغ'))->numeric()->required(),
            Forms\Components\TextInput::make('currency')->label(__('العملة'))->default('USD')->maxLength(3),

            Forms\Components\Select::make('payment_method')
                ->label(__('طريقة الدفع'))
                ->options(['card' => __('بطاقة'), 'bank_transfer' => __('تحويل بنكي'), 'paypal' => 'PayPal'])
                ->required(),

            Forms\Components\Select::make('status')
                ->label(__('الحالة'))
                ->options([
                    'pending' => __('قيد الانتظار'), 'succeeded' => __('ناجح'),
                    'failed' => __('فاشل'), 'refunded' => __('مسترجَع'),
                ])
                ->default('pending')
                ->required(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('campaign.title')->label(__('الحملة'))->searchable()->limit(30)->placeholder('—'),
                Tables\Columns\TextColumn::make('donor_name')->label(__('المتبرع'))->searchable()->placeholder(__('ضيف')),
                Tables\Columns\TextColumn::make('amount')->label(__('المبلغ'))->money(fn ($record) => $record->currency ?? 'USD')->sortable(),
                Tables\Columns\TextColumn::make('payment_method')->label(__('طريقة الدفع'))
                    ->formatStateUsing(fn (string $state) => ['card' => __('بطاقة'), 'bank_transfer' => __('تحويل بنكي'), 'paypal' => 'PayPal'][$state] ?? $state),
                Tables\Columns\SelectColumn::make('status')
                    ->label(__('الحالة'))
                    ->options([
                        'pending' => __('قيد الانتظار'), 'succeeded' => __('ناجح'),
                        'failed' => __('فاشل'), 'refunded' => __('مسترجَع'),
                    ]),
                Tables\Columns\TextColumn::make('created_at')->label(__('التاريخ'))->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('الحالة'))
                    ->options([
                        'pending' => __('قيد الانتظار'), 'succeeded' => __('ناجح'),
                        'failed' => __('فاشل'), 'refunded' => __('مسترجَع'),
                    ]),
                Tables\Filters\SelectFilter::make('campaign_id')
                    ->label(__('الحملة'))
                    ->relationship('campaign', 'title'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDonations::route('/'),
            'create' => Pages\CreateDonation::route('/create'),
            'edit' => Pages\EditDonation::route('/{record}/edit'),
        ];
    }
}
