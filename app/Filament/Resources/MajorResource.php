<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MajorResource\Pages;
use App\Models\Major;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class MajorResource extends Resource
{
    use Translatable;

    protected static ?string $model = Major::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $recordRouteKeyName = 'uuid';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('Team');
    }

    public static function getNavigationLabel(): string
    {
        return __('Majors');
    }

    public static function getModelLabel(): string
    {
        return __('Major');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Majors');
    }

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'slug', 'uuid'];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('الاسم'))
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                        if ($operation === 'create' && filled($state)) {
                            $slug = Str::slug((string) $state);
                            $set('slug', $slug !== '' ? $slug : Str::random(6));
                        }
                    })
                    ->maxLength(255)
                    ->helperText(__('مثال: فريق التصميم — يظهر في تبويبات صفحة الفريق')),

                Forms\Components\TextInput::make('slug')
                    ->label(__('الرابط (Slug)'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->helperText(__('مثال: design')),

                Forms\Components\TextInput::make('sort_order')
                    ->label(__('ترتيب العرض'))
                    ->numeric()
                    ->default(0),

                Forms\Components\Toggle::make('is_active')
                    ->label(__('مفعّل'))
                    ->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(__('الاسم'))->searchable(),
                Tables\Columns\TextColumn::make('slug')->label('Slug')->searchable(),
                Tables\Columns\TextColumn::make('members_count')->label(__('عدد الأعضاء'))->counts('members'),
                Tables\Columns\IconColumn::make('is_active')->label(__('مفعّل'))->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->label(__('الترتيب'))->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
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
            'index' => Pages\ListMajors::route('/'),
            'create' => Pages\CreateMajor::route('/create'),
            'edit' => Pages\EditMajor::route('/{record}/edit'),
        ];
    }
}
