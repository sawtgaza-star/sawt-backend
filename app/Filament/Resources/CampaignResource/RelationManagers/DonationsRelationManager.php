<?php

namespace App\Filament\Resources\CampaignResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class DonationsRelationManager extends RelationManager
{
    protected static string $relationship = 'donations';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Donations');
    }

    protected static function getModelLabel(): ?string
    {
        return __('Donation');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('donor_name')
            ->columns([
                Tables\Columns\TextColumn::make('donor_name')->label(__('المتبرع'))->placeholder(__('ضيف')),
                Tables\Columns\TextColumn::make('user.name')->label(__('حساب مستخدم'))->placeholder('—'),
                Tables\Columns\TextColumn::make('amount')->label(__('المبلغ'))->money(fn ($record) => $record->currency ?? 'USD'),
                Tables\Columns\BadgeColumn::make('status')->label(__('الحالة'))
                    ->colors([
                        'warning' => 'pending', 'success' => 'succeeded',
                        'danger' => 'failed', 'gray' => 'refunded',
                    ])
                    ->formatStateUsing(fn (string $state) => [
                        'pending' => __('قيد الانتظار'), 'succeeded' => __('ناجح'),
                        'failed' => __('فاشل'), 'refunded' => __('مسترجَع'),
                    ][$state] ?? $state),
                Tables\Columns\TextColumn::make('created_at')->label(__('التاريخ'))->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }
}
