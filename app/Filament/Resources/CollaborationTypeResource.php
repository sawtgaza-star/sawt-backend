<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CollaborationTypeResource\Pages;
use App\Models\CollaborationType;
use App\Support\MediaUrl;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CollaborationTypeResource extends Resource
{
    use Translatable;

    protected static ?string $model = CollaborationType::class;

    protected static ?string $navigationIcon = 'heroicon-o-hand-raised';

    protected static ?string $recordRouteKeyName = 'uuid';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('Collaboration');
    }

    public static function getNavigationLabel(): string
    {
        return __('Collaboration Types');
    }

    public static function getModelLabel(): string
    {
        return __('Collaboration Type');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Collaboration Types');
    }

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('key')
                ->label(__('المفتاح (key)'))
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(80)
                ->alphaDash()
                ->helperText(__('مثال: creator, sponsorship, partnership — يُستخدم في الواجهة عند اختيار نوع التعاون'))
                ->live(onBlur: true)
                ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('key', Str::slug((string) $state, '_'))),

            Forms\Components\TextInput::make('title')
                ->label(__('العنوان'))
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            Forms\Components\Textarea::make('description')
                ->label(__('الوصف'))
                ->rows(4)
                ->columnSpanFull(),

            Forms\Components\FileUpload::make('icon')
                ->label(__('الأيقونة'))
                ->image()
                ->disk('public')
                ->directory('collaborate/types')
                ->visibility('public')
                ->imageEditor()
                ->columnSpanFull(),

            Forms\Components\TextInput::make('sort_order')
                ->label(__('الترتيب'))
                ->numeric()
                ->default(0),

            Forms\Components\Toggle::make('is_active')
                ->label(__('مفعّل'))
                ->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                MediaUrl::tableImageColumn('icon', 'الأيقونة'),
                Tables\Columns\TextColumn::make('key')
                    ->label(__('المفتاح'))
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('العنوان'))
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('الترتيب'))
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('مفعّل'))
                    ->boolean(),
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
            'index' => Pages\ListCollaborationTypes::route('/'),
            'create' => Pages\CreateCollaborationType::route('/create'),
            'edit' => Pages\EditCollaborationType::route('/{record}/edit'),
        ];
    }
}
