<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupportPlanResource\Pages;
use App\Models\SupportPlan;
use App\Support\SupportOptions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * باقات الدعم — المبالغ الجاهزة (50/100/150/250) بدورياتها الثلاث.
 * الدوريات (شهري/سنوي) تُنفَّذ كاشتراكات PayPal Billing.
 */
class SupportPlanResource extends Resource
{
    use Translatable;

    protected static ?string $model = SupportPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $recordRouteKeyName = 'uuid';

    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): ?string
    {
        return __('Finance');
    }

    public static function getNavigationLabel(): string
    {
        return __('Support Plans');
    }

    public static function getModelLabel(): string
    {
        return __('Support Plan');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Support Plans');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('interval')
                ->label(__('الدورية'))
                ->options(SupportOptions::intervals())
                ->default('monthly')
                ->required()
                ->live(),

            Forms\Components\TextInput::make('amount')
                ->label(__('المبلغ'))
                ->numeric()->minValue(1)->prefix('$')
                ->required(),

            Forms\Components\TextInput::make('currency')
                ->label(__('العملة'))->default('USD')->maxLength(3)->required(),

            Forms\Components\TextInput::make('sort_order')
                ->label(__('الترتيب'))->numeric()->default(0),

            Forms\Components\TextInput::make('label')
                ->label(__('نص بديل للمبلغ (اختياري)'))
                ->placeholder(__('مثال: باقة الداعم الذهبي')),

            Forms\Components\Toggle::make('is_featured')
                ->label(__('باقة مميّزة'))
                ->helperText(__('تظهر مُبرَزة بالواجهة')),

            Forms\Components\Toggle::make('is_active')->label(__('مفعّلة'))->default(true),

            Forms\Components\Textarea::make('description')
                ->label(__('وصف الباقة'))->rows(3)->columnSpanFull(),

            Forms\Components\TextInput::make('paypal_plan_id')
                ->label(__('معرّف خطة PayPal'))
                ->helperText(__('اتركه فارغاً ليُنشأ آلياً عند أول اشتراك — أو ألصق معرّف خطة جاهزة'))
                ->visible(fn (Get $get) => in_array($get('interval'), ['monthly', 'yearly'], true))
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('interval')
                    ->label(__('الدورية'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => SupportOptions::intervals()[$state] ?? $state)
                    ->colors([
                        'gray' => 'one_time',
                        'success' => 'monthly',
                        'warning' => 'yearly',
                    ]),
                Tables\Columns\TextColumn::make('amount')
                    ->label(__('المبلغ'))
                    ->money(fn ($record) => $record->currency ?? 'USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('label')->label(__('التسمية'))->placeholder('—')->limit(30),
                Tables\Columns\TextColumn::make('paypal_plan_id')->label(__('خطة PayPal'))->placeholder('—')->limit(24)->copyable(),
                Tables\Columns\IconColumn::make('is_featured')->label(__('مميّزة'))->boolean(),
                Tables\Columns\ToggleColumn::make('is_active')->label(__('مفعّلة')),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('interval')
                    ->label(__('الدورية'))
                    ->options(SupportOptions::intervals()),
                Tables\Filters\TernaryFilter::make('is_active')->label(__('الحالة')),
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
            'index' => Pages\ListSupportPlans::route('/'),
            'create' => Pages\CreateSupportPlan::route('/create'),
            'edit' => Pages\EditSupportPlan::route('/{record}/edit'),
        ];
    }
}
