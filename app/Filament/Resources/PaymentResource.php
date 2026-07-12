<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('Courses');
    }

    public static function getModelLabel(): string
    {
        return 'دفعة';
    }

    public static function getPluralModelLabel(): string
    {
        return 'المدفوعات';
    }

    // قراءة فقط — المدفوعات يُنشئها النظام عبر PayPal
    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('uuid')->label('المرجع')->searchable()->copyable()->limit(8),
                Tables\Columns\TextColumn::make('payable_type')->label('النوع')->badge()
                    ->formatStateUsing(fn ($state) => str_contains($state, 'Donation') ? 'تبرع' : (str_contains($state, 'Course') ? 'كورس' : class_basename($state)))
                    ->color(fn ($state) => str_contains($state, 'Donation') ? 'info' : 'warning'),
                Tables\Columns\TextColumn::make('amount')->label('المبلغ')->money(fn ($record) => $record->currency)->sortable(),
                Tables\Columns\TextColumn::make('status')->label('الحالة')->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'failed',
                        'gray' => 'refunded',
                    ]),
                Tables\Columns\TextColumn::make('payer_name')->label('الدافع')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('payer_email')->label('البريد')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('gateway_capture_id')->label('PayPal')->searchable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('paid_at')->label('تاريخ الدفع')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('الحالة')->options([
                    'pending' => 'قيد الانتظار',
                    'completed' => 'مكتمل',
                    'failed' => 'فشل',
                    'refunded' => 'مُسترجع',
                ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'view' => Pages\ViewPayment::route('/{record}'),
        ];
    }
}
