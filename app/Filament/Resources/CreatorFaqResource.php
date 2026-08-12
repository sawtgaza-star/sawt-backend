<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CreatorFaqResource\Pages;
use App\Models\CreatorFaq;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CreatorFaqResource extends Resource
{
    use Translatable;

    protected static ?string $model = CreatorFaq::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $recordRouteKeyName = 'uuid';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('Creators');
    }

    public static function getNavigationLabel(): string
    {
        return __('Creator FAQs');
    }

    public static function getModelLabel(): string
    {
        return __('FAQ');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Creator FAQs');
    }

    protected static ?string $recordTitleAttribute = 'question';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('question')
                ->label('السؤال')
                ->required()
                ->maxLength(500)
                ->columnSpanFull(),

            Forms\Components\Textarea::make('answer')
                ->label('الإجابة')
                ->required()
                ->rows(5)
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
                Tables\Columns\TextColumn::make('question')->label('السؤال')->limit(60)->searchable(),
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
            'index' => Pages\ListCreatorFaqs::route('/'),
            'create' => Pages\CreateCreatorFaq::route('/create'),
            'edit' => Pages\EditCreatorFaq::route('/{record}/edit'),
        ];
    }
}
