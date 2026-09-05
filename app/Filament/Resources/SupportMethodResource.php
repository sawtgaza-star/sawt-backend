<?php

namespace App\Filament\Resources;

use App\Support\LocaleText;

use App\Filament\Resources\SupportMethodResource\Pages;
use App\Models\SupportMethod;
use App\Support\MediaUrl;
use App\Support\SupportOptions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * وسائل الدعم — من هنا تضيف بنكاً بالآيبان، أو فودافون كاش برقمه،
 * أو محفظة عملات رقمية بصورة QR، وكلها تظهر مباشرة بالفرونت عبر الـ API.
 */
class SupportMethodResource extends Resource
{
    use Translatable;

    protected static ?string $model = SupportMethod::class;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';

    protected static ?string $recordRouteKeyName = 'uuid';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return __('Finance');
    }

    public static function getNavigationLabel(): string
    {
        return __('Support Methods');
    }

    public static function getModelLabel(): string
    {
        return __('Support Method');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Support Methods');
    }

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'provider', 'account_identifier', 'uuid'];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('method')->columnSpanFull()->tabs([

                Forms\Components\Tabs\Tab::make(__('البطاقة'))->icon('heroicon-o-identification')->schema([
                    Forms\Components\Select::make('category')
                        ->label(__('القسم'))
                        ->options(SupportOptions::categories())
                        ->helperText(__('يحدّد تحت أي بطاقة تظهر الوسيلة بصفحة «اختر طريقة الدعم»'))
                        ->default('transfer')
                        ->required()
                        ->live(),

                    Forms\Components\TextInput::make('provider')
                        ->label(__('المعرّف التقني'))
                        ->helperText(__('حروف إنجليزية صغيرة بدون مسافات — مثل: paypal, vodafone_cash, usdt'))
                        ->required()
                        ->maxLength(60)
                        ->rules(['regex:/^[a-z0-9_]+$/']),

                    Forms\Components\TextInput::make('name')
                        ->label(__('اسم الوسيلة'))
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Toggle::make('is_active')->label(__('مفعّل'))->default(true),

                    Forms\Components\Textarea::make('description')
                        ->label(__('وصف مختصر'))
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\FileUpload::make('logo')
                        ->label(__('شعار الوسيلة'))
                        ->image()->disk('public')->directory('support/methods')->visibility('public')
                        ->imageEditor()
                        ->imagePreviewHeight('120'),

                    Forms\Components\TextInput::make('sort_order')
                        ->label(__('الترتيب'))->numeric()->default(0),
                ])->columns(2),

                Forms\Components\Tabs\Tab::make(__('بيانات الحساب'))->icon('heroicon-o-banknotes')->schema([
                    Forms\Components\TextInput::make('account_identifier')
                        ->label(fn (Get $get) => $get('category') === 'crypto' ? 'عنوان المحفظة' : 'رقم الحساب / الآيبان / رقم المحفظة')
                        ->helperText(__('القيمة الأساسية التي ينسخها المتبرع'))
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('account_holder')
                        ->label(__('اسم صاحب الحساب')),

                    Forms\Components\TextInput::make('currency')
                        ->label(__('العملة'))
                        ->maxLength(3)
                        ->placeholder('USD'),

                    Forms\Components\Select::make('network')
                        ->label(__('الشبكة (للعملات الرقمية)'))
                        ->options(SupportOptions::networks())
                        ->searchable()
                        ->visible(fn (Get $get) => $get('category') === 'crypto'),

                    Forms\Components\FileUpload::make('qr_image')
                        ->label(__('صورة QR للمحفظة'))
                        ->helperText(__('ارفع QR محفظة بايننس أو أي محفظة أخرى ليمسحها المتبرع'))
                        ->image()->disk('public')->directory('support/qr')->visibility('public')
                        ->imageEditor()
                        ->imagePreviewHeight('200')
                        ->visible(fn (Get $get) => $get('category') === 'crypto')
                        ->columnSpanFull(),

                    Forms\Components\Repeater::make('fields')
                        ->label(__('حقول إضافية تظهر للمتبرع'))
                        ->helperText(__('مثل: اسم البنك، رمز السويفت، اسم الفرع، رقم الهاتف…'))
                        ->schema([
                            Forms\Components\TextInput::make('label_ar')->label(__('التسمية (عربي)'))->required(),
                            Forms\Components\TextInput::make('label_en')->label('Label (English)'),
                            Forms\Components\TextInput::make('value')->label(__('القيمة'))->required()->columnSpanFull(),
                            Forms\Components\Toggle::make('is_copyable')->label(__('قابل للنسخ'))->default(true)->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => LocaleText::pick($state, 'label', 'حقل') ?: null)
                        ->columnSpanFull(),
                ])->columns(2),

                Forms\Components\Tabs\Tab::make(__('التعليمات والإثبات'))->icon('heroicon-o-document-check')->schema([
                    Forms\Components\Textarea::make('instructions')
                        ->label(__('خطوات التحويل'))
                        ->helperText(__('تظهر للمتبرع بخطوة إثبات التحويل'))
                        ->rows(6)
                        ->columnSpanFull(),

                    Forms\Components\Toggle::make('requires_proof')
                        ->label(__('يتطلب رفع إثبات تحويل'))
                        ->helperText(__('أطفئه لوسائل الدفع الفوري مثل PayPal — تُعتمد آلياً بعد نجاح الدفع'))
                        ->default(true),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                MediaUrl::tableImageColumn('logo', 'الشعار')->height(40),
                Tables\Columns\TextColumn::make('name')->label(__('الوسيلة'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->label(__('القسم'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => SupportOptions::categories()[$state] ?? $state)
                    ->colors([
                        'warning' => 'electronic',
                        'success' => 'transfer',
                        'info' => 'crypto',
                    ]),
                Tables\Columns\TextColumn::make('provider')->label(__('المعرّف'))->badge()->color('gray'),
                Tables\Columns\TextColumn::make('account_identifier')->label(__('الحساب'))->limit(28)->copyable()->placeholder('—'),
                Tables\Columns\IconColumn::make('requires_proof')->label(__('يطلب إثبات'))->boolean(),
                Tables\Columns\ToggleColumn::make('is_active')->label(__('مفعّل')),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label(__('القسم'))
                    ->options(SupportOptions::categories()),
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
            'index' => Pages\ListSupportMethods::route('/'),
            'create' => Pages\CreateSupportMethod::route('/create'),
            'edit' => Pages\EditSupportMethod::route('/{record}/edit'),
        ];
    }
}
