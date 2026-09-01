<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeamMemberResource\Pages;
use App\Models\TeamMember;
use App\Support\MediaUrl;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TeamMemberResource extends Resource
{
    use Translatable;

    protected static ?string $model = TeamMember::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $recordRouteKeyName = 'uuid';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('Team');
    }

    public static function getNavigationLabel(): string
    {
        return __('Team Members');
    }

    public static function getModelLabel(): string
    {
        return __('Team Member');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Team Members');
    }

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'role', 'uuid'];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Member')->columnSpanFull()->tabs([
                Forms\Components\Tabs\Tab::make('البطاقة')->schema([
                    Forms\Components\Select::make('major_id')
                        ->label('القسم (Major)')
                        ->relationship('major', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->helperText('اختر القسم من قائمة الـ Majors'),

                    Forms\Components\TextInput::make('name')
                        ->label('الاسم')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('role')
                        ->label('المسمى الوظيفي')
                        ->required()
                        ->placeholder('UI/UX Designer')
                        ->maxLength(255),

                    Forms\Components\FileUpload::make('photo')
                        ->label('الصورة (البطاقة وصفحة التفاصيل)')
                        ->image()
                        ->disk('public')
                        ->directory('team/members')
                        ->visibility('public')
                        ->imagePreviewHeight('180')
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('sort_order')
                        ->label('ترتيب العرض')
                        ->numeric()
                        ->default(0),

                    Forms\Components\Toggle::make('is_active')
                        ->label('مفعّل')
                        ->default(true),
                ])->columns(2),

                Forms\Components\Tabs\Tab::make('صفحة التفاصيل')->schema([
                    Forms\Components\TextInput::make('years_of_experience')
                        ->label('سنوات الخبرة')
                        ->numeric()
                        ->minValue(0)
                        ->placeholder('5')
                        ->helperText('تظهر كـ «5 سنوات من الخبرة»'),

                    Forms\Components\Textarea::make('bio')
                        ->label('النبذة')
                        ->rows(6)
                        ->columnSpanFull()
                        ->helperText('نص «نبذة عنه» في صفحة العضو'),

                    Forms\Components\Section::make('روابط التواصل (العضو)')->schema([
                        Forms\Components\TextInput::make('facebook_url')->label('Facebook')->url()->placeholder('https://…'),
                        Forms\Components\TextInput::make('linkedin_url')->label('LinkedIn')->url()->placeholder('https://…'),
                        Forms\Components\TextInput::make('twitter_url')->label('X / Twitter')->url()->placeholder('https://…'),
                        Forms\Components\TextInput::make('instagram_url')->label('Instagram')->url()->placeholder('https://…'),
                    ])->columns(2),
                ])->columns(2),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                MediaUrl::tableImageColumn('photo', 'الصورة')->circular(),
                Tables\Columns\TextColumn::make('name')->label('الاسم')->searchable(),
                Tables\Columns\TextColumn::make('role')->label('المسمى')->searchable(),
                Tables\Columns\TextColumn::make('years_of_experience')->label('الخبرة')->suffix(' سنة')->placeholder('—'),
                Tables\Columns\TextColumn::make('major.name')->label('القسم')->badge()->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('مفعّل')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->label('الترتيب')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('major_id')
                    ->label('القسم')
                    ->relationship('major', 'name'),
                Tables\Filters\TernaryFilter::make('is_active')->label('الحالة'),
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
            'index' => Pages\ListTeamMembers::route('/'),
            'create' => Pages\CreateTeamMember::route('/create'),
            'edit' => Pages\EditTeamMember::route('/{record}/edit'),
        ];
    }
}
