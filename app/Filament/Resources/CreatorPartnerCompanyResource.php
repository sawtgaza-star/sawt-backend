<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CreatorPartnerCompanyResource\Pages;
use App\Models\CreatorPartnerCompany;
use App\Support\MediaUrl;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CreatorPartnerCompanyResource extends Resource
{
    use Translatable;

    protected static ?string $model = CreatorPartnerCompany::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $recordRouteKeyName = 'uuid';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('Creators');
    }

    public static function getNavigationLabel(): string
    {
        return __('Partner Companies');
    }

    public static function getModelLabel(): string
    {
        return __('Partner Company');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Partner Companies');
    }

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('اسم الشركة')
                ->required()
                ->maxLength(255),

            Forms\Components\FileUpload::make('logo')
                ->label('الشعار')
                ->image()
                ->disk('public')
                ->directory('creators/partners')
                ->visibility('public')
                ->imagePreviewHeight('120')
                ->columnSpanFull(),

            Forms\Components\TextInput::make('url')
                ->label('الموقع / الرابط')
                ->url()
                ->maxLength(255),

            Forms\Components\Select::make('creators')
                ->label('صناع المحتوى المرتبطون')
                ->relationship('creators', 'username')
                ->multiple()
                ->searchable()
                ->preload()
                ->helperText('يظهرون كصور صغيرة أسفل بطاقة الشركة')
                ->columnSpanFull(),

            Forms\Components\TextInput::make('sort_order')
                ->label('ترتيب العرض')
                ->numeric()
                ->default(0),

            Forms\Components\Toggle::make('is_active')
                ->label('مفعّل')
                ->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                MediaUrl::tableImageColumn('logo', '')->height(40),
                Tables\Columns\TextColumn::make('name')->label('الشركة')->searchable(),
                Tables\Columns\TextColumn::make('creators_count')
                    ->label('صناع المحتوى')
                    ->counts('creators'),
                Tables\Columns\TextColumn::make('sort_order')->label('الترتيب')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('مفعّل')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
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
            'index' => Pages\ListCreatorPartnerCompanies::route('/'),
            'create' => Pages\CreateCreatorPartnerCompany::route('/create'),
            'edit' => Pages\EditCreatorPartnerCompany::route('/{record}/edit'),
        ];
    }
}
