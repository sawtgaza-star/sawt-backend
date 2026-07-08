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
            Forms\Components\TextInput::make('donor_name')->label('اسم المتبرع'),
            Forms\Components\TextInput::make('donor_email')->label('بريد المتبرع')->email(),
            Forms\Components\TextInput::make('amount')->label('المبلغ')->numeric()->required(),
            Forms\Components\TextInput::make('currency')->label('العملة')->default('USD')->maxLength(3),
            Forms\Components\Select::make('payment_method')
                ->label('طريقة الدفع')
                ->options(['card' => 'بطاقة', 'bank_transfer' => 'تحويل بنكي', 'paypal' => 'PayPal'])
                ->required(),
            Forms\Components\Select::make('status')
                ->label('الحالة')
                ->options([
                    'pending' => 'قيد الانتظار', 'succeeded' => 'ناجح',
                    'failed' => 'فاشل', 'refunded' => 'مسترجَع',
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
                Tables\Columns\TextColumn::make('donor_name')->label('المتبرع')->placeholder('ضيف'),
                Tables\Columns\TextColumn::make('user.name')->label('حساب مستخدم')->placeholder('—'),
                Tables\Columns\TextColumn::make('amount')->label('المبلغ')->money(fn ($record) => $record->currency ?? 'USD'),
                Tables\Columns\BadgeColumn::make('status')->label('الحالة')
                    ->colors([
                        'warning' => 'pending', 'success' => 'succeeded',
                        'danger' => 'failed', 'gray' => 'refunded',
                    ])
                    ->formatStateUsing(fn (string $state) => [
                        'pending' => 'قيد الانتظار', 'succeeded' => 'ناجح',
                        'failed' => 'فاشل', 'refunded' => 'مسترجَع',
                    ][$state] ?? $state),
                Tables\Columns\TextColumn::make('created_at')->label('التاريخ')->dateTime('Y-m-d H:i')->sortable(),
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
