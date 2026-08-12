<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CreatorResource\Pages;
use App\Filament\Resources\CreatorResource\RelationManagers;
use App\Models\Creator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CreatorResource extends Resource
{
    use Translatable;

    protected static ?string $model = Creator::class;

    protected static ?string $navigationIcon = 'heroicon-o-microphone';

    protected static ?string $recordRouteKeyName = 'uuid';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('Creators');
    }

    public static function getNavigationLabel(): string
    {
        return __('Content Creators');
    }

    public static function getModelLabel(): string
    {
        return __('Content Creator');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Content Creators');
    }

    protected static ?string $recordTitleAttribute = 'username';

    public static function getGloballySearchableAttributes(): array
    {
        return ['username', 'uuid', 'user.name', 'user.email'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return array_filter([
            __('User') => $record->user?->name,
            __('Status') => $record->status,
        ]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('ملف صانع المحتوى')->schema([
                Forms\Components\Select::make('user_id')
                    ->label('حساب المستخدم')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\TextInput::make('username')
                    ->label('اسم المستخدم (username)')
                    ->helperText('يُستخدم في رابط الملف الشخصي')
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('bio')
                    ->label('نبذة تعريفية')
                    ->rows(4)
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('avatar')
                    ->label('الصورة')
                    ->image()
                    ->disk('public')
                    ->directory('creators/avatars')
                    ->visibility('public')
                    ->imagePreviewHeight('150')
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('followers_count')
                    ->label('عدد المتابعين')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),

                Forms\Components\Select::make('status')
                    ->label('الحالة')
                    ->options(['active' => 'نشط', 'inactive' => 'غير نشط'])
                    ->default('active')
                    ->required(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->label('')
                    ->disk('public')
                    ->circular()
                    ->height(40),
                Tables\Columns\TextColumn::make('username')->label('اسم المستخدم')->searchable(),
                Tables\Columns\TextColumn::make('user.name')->label('المستخدم')->searchable(),
                Tables\Columns\TextColumn::make('followers_count')->label('المتابعون')->numeric()->sortable(),
                Tables\Columns\BadgeColumn::make('status')->label('الحالة')
                    ->colors(['success' => 'active', 'danger' => 'inactive'])
                    ->formatStateUsing(fn (string $state) => $state === 'active' ? 'نشط' : 'غير نشط'),
                Tables\Columns\TextColumn::make('updated_at')->label('آخر تحديث')->dateTime('Y-m-d')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(['active' => 'نشط', 'inactive' => 'غير نشط']),
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\SocialsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCreators::route('/'),
            'create' => Pages\CreateCreator::route('/create'),
            'edit' => Pages\EditCreator::route('/{record}/edit'),
        ];
    }
}
