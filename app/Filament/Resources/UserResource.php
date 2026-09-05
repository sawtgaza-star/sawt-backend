<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Support\MediaUrl;
use App\Support\ContentCreatorPermissions;
use App\Support\WebsiteUserPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $recordRouteKeyName = 'uuid';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('Administration');
    }

    public static function getNavigationLabel(): string
    {
        return __('Website Users');
    }

    public static function getModelLabel(): string
    {
        return __('Website User');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Website Users');
    }

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'phone', 'uuid'];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('type', [User::TYPE_USER, User::TYPE_CONTENT_CREATOR])
            ->whereDoesntHave('roles', fn (Builder $q) => $q->whereIn('name', User::FILAMENT_ROLES));
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('مستخدم الموقع / API'))->schema([
                Forms\Components\TextInput::make('name')->label(__('الاسم'))->required(),
                Forms\Components\TextInput::make('email')->label(__('البريد الإلكتروني'))->email()->required()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('phone')->label(__('الهاتف')),
                Forms\Components\TextInput::make('country_code')->label(__('مفتاح الدولة'))->default('+970'),

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
                        User::TYPE_USER => __('مستخدم'),
                        User::TYPE_CONTENT_CREATOR => __('صانع محتوى'),
                    ])
                    ->default(User::TYPE_USER)
                    ->disabled()
                    ->dehydrated()
                    ->helperText(__('يُحدَّث تلقائياً عند قبول طلب الانضمام كصانع محتوى.')),

                Forms\Components\Placeholder::make('role_hint')
                    ->label(__('الدور'))
                    ->content(__('يُعيَّن تلقائياً دور user أو content_creator حسب النوع — بدون دخول Filament.'))
                    ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('phone')->label(__('الهاتف'))->toggleable(),
                Tables\Columns\TextColumn::make('roles.name')->label(__('الأدوار'))->badge()->default(User::ROLE_USER),
                Tables\Columns\BadgeColumn::make('type')->label(__('النوع'))
                    ->colors([
                        'gray' => User::TYPE_USER,
                        'success' => User::TYPE_CONTENT_CREATOR,
                    ])
                    ->formatStateUsing(fn (?string $state) => match ($state) {
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
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('النوع'))
                    ->options([
                        User::TYPE_USER => __('مستخدم'),
                        User::TYPE_CONTENT_CREATOR => __('صانع محتوى'),
                    ]),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function ensureWebsiteUserRole(User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        if ($user->type === User::TYPE_CONTENT_CREATOR || $user->hasRole(User::ROLE_CONTENT_CREATOR)) {
            foreach (ContentCreatorPermissions::all() as $name) {
                Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            }

            $role = Role::firstOrCreate(['name' => User::ROLE_CONTENT_CREATOR, 'guard_name' => 'web']);
            $role->syncPermissions(ContentCreatorPermissions::all());
            $user->syncRoles([User::ROLE_CONTENT_CREATOR]);

            return;
        }

        foreach (WebsiteUserPermissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(['name' => User::ROLE_USER, 'guard_name' => 'web']);
        $role->syncPermissions(WebsiteUserPermissions::all());

        $user->syncRoles([User::ROLE_USER]);
    }
}
