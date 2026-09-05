<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VideoResource\Pages;
use App\Filament\Resources\VideoResource\RelationManagers;
use App\Models\Category;
use App\Models\Creator;
use App\Models\Video;
use App\Support\MediaUrl;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class VideoResource extends Resource
{
    use Translatable;

    protected static ?string $model = Video::class;

    protected static ?string $navigationIcon = 'heroicon-o-play-circle';

    protected static ?string $recordRouteKeyName = 'uuid';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('Content');
    }

    public static function getNavigationLabel(): string
    {
        return __('Reels / Videos');
    }

    public static function getModelLabel(): string
    {
        return __('Reel / Video');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Reels / Videos');
    }

    protected static ?string $recordTitleAttribute = 'title';

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'slug', 'uuid', 'creator.username'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return array_filter([
            __('Content Creator') => $record->creator?->username,
            __('Status') => $record->status,
        ]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make()->columnSpan(2)->schema([
                Forms\Components\Section::make(__('المحتوى'))->schema([
                    Forms\Components\TextInput::make('title')
                        ->label(__('العنوان'))
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) =>
                            $operation === 'create' ? $set('slug', Str::slug($state)) : null)
                        ->maxLength(255),

                    Forms\Components\TextInput::make('slug')
                        ->label(__('الرابط (Slug)'))
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    Forms\Components\Textarea::make('description')
                        ->label(__('الوصف'))
                        ->rows(4),

                    Forms\Components\TextInput::make('video_url')->label(__('رابط الفيديو'))->url(),
                    Forms\Components\TextInput::make('audio_url')->label(__('رابط الصوت'))->url(),

                    Forms\Components\FileUpload::make('cover_url')
                        ->label(__('صورة الغلاف'))
                        ->image()
                        ->disk('public')
                        ->directory('videos/covers')
                        ->visibility('public')
                        ->imagePreviewHeight('200'),

                    Forms\Components\TextInput::make('duration_seconds')
                        ->label(__('المدة (ثانية)'))
                        ->numeric(),
                ])->columns(2),
            ]),

            Forms\Components\Group::make()->columnSpan(1)->schema([
                Forms\Components\Section::make(__('النشر'))->schema([
                    Forms\Components\Select::make('creator_id')
                        ->label(__('صانع المحتوى'))
                        ->relationship('creator', 'username')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Select::make('category_id')
                        ->label(__('الفئة'))
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload(),

                    Forms\Components\Select::make('status')
                        ->label(__('الحالة'))
                        ->options([
                            'draft' => __('مسودة'),
                            'published' => __('منشور'),
                            'scheduled' => __('مجدوَل'),
                            'archived' => __('مؤرشف'),
                        ])
                        ->default('draft')
                        ->required(),

                    Forms\Components\DateTimePicker::make('published_at')
                        ->label(__('تاريخ النشر')),

                    Forms\Components\Toggle::make('is_featured')->label(__('مميّز')),
                ]),

                Forms\Components\Section::make(__('الإحصائيات'))->schema([
                    Forms\Components\TextInput::make('play_count')->label(__('المشاهدات'))->numeric()->default(0),
                    Forms\Components\TextInput::make('like_count')->label(__('الإعجابات'))->numeric()->default(0),
                    Forms\Components\TextInput::make('comment_count')->label(__('التعليقات'))->numeric()->default(0),
                ])->collapsed(),
            ]),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                MediaUrl::tableImageColumn('cover_url', '')->height(48)->square(),
                Tables\Columns\TextColumn::make('title')->label(__('العنوان'))->searchable()->limit(40),
                Tables\Columns\TextColumn::make('creator.username')->label(__('صانع المحتوى'))->searchable(),
                Tables\Columns\TextColumn::make('category.name')->label(__('الفئة')),
                Tables\Columns\BadgeColumn::make('status')->label(__('الحالة'))
                    ->colors([
                        'gray' => 'draft',
                        'success' => 'published',
                        'warning' => 'scheduled',
                        'danger' => 'archived',
                    ])
                    ->formatStateUsing(fn (string $state) => [
                        'draft' => __('مسودة'), 'published' => __('منشور'),
                        'scheduled' => __('مجدوَل'), 'archived' => __('مؤرشف'),
                    ][$state] ?? $state),
                Tables\Columns\TextColumn::make('play_count')->label(__('المشاهدات'))->numeric()->sortable(),
                Tables\Columns\IconColumn::make('is_featured')->label(__('مميّز'))->boolean(),
                Tables\Columns\TextColumn::make('published_at')->label(__('النشر'))->dateTime('Y-m-d')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('الحالة'))
                    ->options([
                        'draft' => __('مسودة'), 'published' => __('منشور'),
                        'scheduled' => __('مجدوَل'), 'archived' => __('مؤرشف'),
                    ]),
                Tables\Filters\SelectFilter::make('category_id')
                    ->label(__('الفئة'))
                    ->relationship('category', 'name'),
                Tables\Filters\TernaryFilter::make('is_featured')->label(__('مميّز')),
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
            RelationManagers\CommentsRelationManager::class,
            RelationManagers\TagsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVideos::route('/'),
            'create' => Pages\CreateVideo::route('/create'),
            'edit' => Pages\EditVideo::route('/{record}/edit'),
        ];
    }
}
