<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CreatorResource\Pages;
use App\Filament\Resources\CreatorResource\RelationManagers;
use App\Models\Creator;
use App\Models\User;
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
            Forms\Components\Tabs::make('Creator')->columnSpanFull()->tabs([

                Forms\Components\Tabs\Tab::make('البيانات الأساسية')->schema([
                    Forms\Components\Section::make()->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('حساب المستخدم')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('username')
                            ->label('اسم المستخدم (username)')
                            ->helperText('يُستخدم في رابط /creators/{username}')
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('content_type')
                            ->label('نوع المحتوى')
                            ->placeholder('ممثل مسرحي / صحفي / يوتيوبر...')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('bio')
                            ->label('نبذة تعريفية')
                            ->rows(4)
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('avatar')
                            ->label('الصورة الشخصية')
                            ->image()
                            ->disk('public')
                            ->directory('creators/avatars')
                            ->visibility('public')
                            ->imagePreviewHeight('150'),

                        Forms\Components\FileUpload::make('cover')
                            ->label('صورة الغلاف')
                            ->image()
                            ->disk('public')
                            ->directory('creators/covers')
                            ->visibility('public')
                            ->imagePreviewHeight('150'),
                    ])->columns(2),
                ]),

                Forms\Components\Tabs\Tab::make('الحالة والإحصائيات')->schema([
                    Forms\Components\Section::make()->schema([
                        Forms\Components\Select::make('status')
                            ->label('الحالة')
                            ->options(['active' => 'نشط', 'inactive' => 'غير نشط'])
                            ->default('active')
                            ->required(),

                        Forms\Components\Toggle::make('is_verified')->label('موثّق'),
                        Forms\Components\Toggle::make('is_featured')->label('مميّز'),

                        Forms\Components\TextInput::make('followers_count')
                            ->label('عدد المتابعين')->numeric()->default(0),

                        Forms\Components\TextInput::make('views_count')
                            ->label('عدد المشاهدات')->numeric()->default(0),

                        Forms\Components\TextInput::make('total_videos')
                            ->label('عدد الفيديوهات')->numeric()->default(0),

                        Forms\Components\TextInput::make('monthly_goal_amount')
                            ->label('الهدف الشهري للدعم')
                            ->numeric()
                            ->prefix('$'),
                    ])->columns(2),
                ]),

                Forms\Components\Tabs\Tab::make('بيانات استلام الدعم')->schema([
                    Forms\Components\Section::make()->schema([
                        Forms\Components\TextInput::make('bank_name')->label('اسم البنك'),
                        Forms\Components\TextInput::make('bank_account_owner')->label('اسم صاحب الحساب'),
                        Forms\Components\TextInput::make('bank_account_number')->label('رقم الحساب'),
                        Forms\Components\TextInput::make('bank_iban')->label('IBAN'),
                        Forms\Components\TextInput::make('paypal_email')->label('بريد PayPal')->email(),
                    ])->columns(2),
                ]),
            ]),
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
                Tables\Columns\TextColumn::make('content_type')->label('نوع المحتوى'),
                Tables\Columns\TextColumn::make('followers_count')->label('المتابعون')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('total_videos')->label('الفيديوهات')->numeric()->sortable(),
                Tables\Columns\IconColumn::make('is_verified')->label('موثّق')->boolean(),
                Tables\Columns\IconColumn::make('is_featured')->label('مميّز')->boolean(),
                Tables\Columns\BadgeColumn::make('status')->label('الحالة')
                    ->colors(['success' => 'active', 'danger' => 'inactive'])
                    ->formatStateUsing(fn (string $state) => $state === 'active' ? 'نشط' : 'غير نشط'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(['active' => 'نشط', 'inactive' => 'غير نشط']),
                Tables\Filters\TernaryFilter::make('is_verified')->label('موثّق'),
                Tables\Filters\TernaryFilter::make('is_featured')->label('مميّز'),
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
            RelationManagers\CollaborationsRelationManager::class,
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
