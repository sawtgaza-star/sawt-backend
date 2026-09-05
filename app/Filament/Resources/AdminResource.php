<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdminResource\Pages;
use App\Models\User;
use App\Support\MediaUrl;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AdminResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $slug = 'admins';

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $recordRouteKeyName = 'uuid';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('Administration');
    }

    public static function getNavigationLabel(): string
    {
        return __('Admins');
    }

    public static function getModelLabel(): string
    {
        return __('Admin');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Admins');
    }

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'uuid'];
    }

    public static function getEloquentQuery(): Builder
    {
        // Filament staff only
        return parent::getEloquentQuery()
            ->where(function (Builder $query) {
                $query->where('type', User::TYPE_ADMIN)
                    ->orWhereHas('roles', fn (Builder $q) => $q->whereIn('name', User::FILAMENT_ROLES));
            });
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('مدير لوحة التحكم'))->schema([
                Forms\Components\TextInput::make('name')->label(__('الاسم'))->required(),
                Forms\Components\TextInput::make('email')->label(__('البريد الإلكتروني'))->email()->required()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('phone')->label(__('الهاتف')),

                Forms\Components\TextInput::make('password')
                    ->label(__('كلمة المرور'))
                    ->password()
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $operation) => $operation === 'create'),

                Forms\Components\FileUpload::make('avatar')
                    ->label(__('الصورة الشخصية'))
                    ->image()
                    ->disk('public')
                    ->directory('users/avatars')
                    ->visibility('public'),

                Forms\Components\Select::make('status')
                    ->label(__('الحالة'))
                    ->options(['active' => __('نشط'), 'inactive' => __('غير نشط'), 'banned' => 'محظور'])
                    ->default('active')
                    ->required(),

                Forms\Components\Select::make('type')
                    ->label(__('النوع'))
                    ->options([
                        User::TYPE_ADMIN => __('مدير'),
                        User::TYPE_USER => __('مستخدم'),
                        User::TYPE_CONTENT_CREATOR => __('صانع محتوى'),
                    ])
                    ->default(User::TYPE_ADMIN)
                    ->disabled()
                    ->dehydrated()
                    ->helperText(__('حسابات لوحة التحكم نوعها admin دائماً.')),

                Forms\Components\Select::make('roles')
                    ->label(__('أدوار Filament'))
                    ->relationship(
                        name: 'roles',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->whereIn('name', User::FILAMENT_ROLES),
                    )
                    ->multiple()
                    ->preload()
                    ->required()
                    ->helperText(__('super_admin / admin / moderator فقط — لا يُمنح دور user')),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                MediaUrl::tableImageColumn('avatar', '')->circular()->height(40),
                Tables\Columns\TextColumn::make('name')->label(__('الاسم'))->searchable(),
                Tables\Columns\TextColumn::make('email')->label(__('البريد'))->searchable(),
                Tables\Columns\TextColumn::make('roles.name')->label(__('الأدوار'))->badge(),
                Tables\Columns\BadgeColumn::make('type')->label(__('النوع'))
                    ->colors(['danger' => User::TYPE_ADMIN])
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        User::TYPE_ADMIN => __('مدير'),
                        User::TYPE_CONTENT_CREATOR => __('صانع محتوى'),
                        default => __('مستخدم'),
                    }),
                Tables\Columns\BadgeColumn::make('status')->label(__('الحالة'))
                    ->colors(['success' => 'active', 'gray' => 'inactive', 'danger' => 'banned'])
                    ->formatStateUsing(fn (string $state) => ['active' => __('نشط'), 'inactive' => __('غير نشط'), 'banned' => 'محظور'][$state] ?? $state),
                Tables\Columns\TextColumn::make('created_at')->label(__('انضم في'))->dateTime('Y-m-d')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('الحالة'))
                    ->options(['active' => __('نشط'), 'inactive' => __('غير نشط'), 'banned' => 'محظور']),
                Tables\Filters\SelectFilter::make('roles')
                    ->label(__('الدور'))
                    ->relationship(
                        name: 'roles',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->whereIn('name', User::FILAMENT_ROLES),
                    ),
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
            'index' => Pages\ListAdmins::route('/'),
            'create' => Pages\CreateAdmin::route('/create'),
            'edit' => Pages\EditAdmin::route('/{record}/edit'),
        ];
    }
}
