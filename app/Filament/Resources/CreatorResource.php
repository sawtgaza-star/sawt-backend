<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CreatorResource\Pages;
use App\Models\Creator;
use App\Models\User;
use App\Support\MediaUrl;
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

    /** @return array<string, string> */
    public static function socialPlatformOptions(): array
    {
        return [
            'instagram' => 'Instagram',
            'facebook' => 'Facebook',
            'twitter' => 'X / Twitter',
            'linkedin' => 'LinkedIn',
            'youtube' => 'YouTube',
            'tiktok' => 'TikTok',
            'telegram' => 'Telegram',
            'other' => 'أخرى',
        ];
    }

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
            Forms\Components\Section::make('حساب المنصة')
                ->description('يُنشأ تلقائياً بنوع «صانع محتوى» عند الحفظ')
                ->schema([
                    Forms\Components\TextInput::make('account_name')
                        ->label('الاسم')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('account_email')
                        ->label('البريد الإلكتروني')
                        ->email()
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('account_phone')
                        ->label('الهاتف')
                        ->maxLength(40),
                    Forms\Components\TextInput::make('account_password')
                        ->label('كلمة المرور')
                        ->password()
                        ->revealable()
                        ->helperText('اتركها فارغة لتوليد كلمة مرور تلقائياً'),
                ])->columns(2),

            Forms\Components\Section::make('ملف صانع المحتوى')->schema([
                Forms\Components\TextInput::make('username')
                    ->label('اسم المستخدم (username)')
                    ->helperText('يُستخدم في رابط الملف الشخصي')
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('role')
                    ->label('المسمى / التخصص')
                    ->placeholder('ممثل مسرحي')
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

                Forms\Components\TextInput::make('sort_order')
                    ->label('ترتيب العرض')
                    ->numeric()
                    ->default(0),

                Forms\Components\Toggle::make('is_verified')
                    ->label('موثّق (شارة)')
                    ->default(false),

                Forms\Components\Select::make('status')
                    ->label('الحالة')
                    ->options(['active' => 'نشط', 'inactive' => 'غير نشط'])
                    ->default('active')
                    ->required(),
            ])->columns(2),

            Forms\Components\Section::make('مواقع التواصل')->schema([
                Forms\Components\Repeater::make('socials')
                    ->label('')
                    ->schema([
                        Forms\Components\Select::make('platform')
                            ->label('المنصة')
                            ->options(static::socialPlatformOptions())
                            ->required(),
                        Forms\Components\TextInput::make('url')
                            ->label('الرابط')
                            ->url()
                            ->required()
                            ->maxLength(500),
                        Forms\Components\TextInput::make('followers_count')
                            ->label('عدد المتابعين')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('display_order')
                            ->label('ترتيب العرض')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2)
                    ->reorderable()
                    ->collapsible()
                    ->defaultItems(0)
                    ->addActionLabel('إضافة رابط')
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                MediaUrl::tableImageColumn('avatar', '')->circular()->height(40),
                Tables\Columns\TextColumn::make('user.name')->label('الاسم')->searchable(),
                Tables\Columns\TextColumn::make('username')->label('username')->searchable(),
                Tables\Columns\TextColumn::make('role')->label('التخصص')->limit(30),
                Tables\Columns\TextColumn::make('user.email')->label('البريد')->searchable(),
                Tables\Columns\TextColumn::make('user.type')
                    ->label('نوع الحساب')
                    ->formatStateUsing(fn (?string $state): string => $state === User::TYPE_CONTENT_CREATOR ? 'صانع محتوى' : ($state ?? '—'))
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('followers_count')->label('المتابعون')->numeric()->sortable(),
                Tables\Columns\IconColumn::make('is_verified')->label('موثّق')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->label('الترتيب')->sortable(),
                Tables\Columns\BadgeColumn::make('status')->label('الحالة')
                    ->colors(['success' => 'active', 'danger' => 'inactive'])
                    ->formatStateUsing(fn (string $state) => $state === 'active' ? 'نشط' : 'غير نشط'),
                Tables\Columns\TextColumn::make('updated_at')->label('آخر تحديث')->dateTime('Y-m-d')->sortable(),
            ])
            ->defaultSort('sort_order')
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
        return [];
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
