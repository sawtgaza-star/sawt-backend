<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StoryResource\Pages;
use App\Models\Story;
use App\Support\MediaUrl;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class StoryResource extends Resource
{
    use Translatable;

    protected static ?string $model = Story::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $recordRouteKeyName = 'uuid';

    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): ?string
    {
        return __('Content');
    }

    public static function getNavigationLabel(): string
    {
        return 'القصص';
    }

    public static function getModelLabel(): string
    {
        return 'قصة';
    }

    public static function getPluralModelLabel(): string
    {
        return 'القصص';
    }

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make()->columnSpan(2)->schema([
                Forms\Components\Section::make('1) بطاقة القائمة / الرئيسية')->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('عنوان صفحة التفاصيل')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) =>
                            $operation === 'create' ? $set('slug', Str::slug($state)) : null)
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('card_headline')
                        ->label('العنوان على البطاقة')
                        ->helperText('إن تُرك فارغاً يُستخدم عنوان صفحة التفاصيل')
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('excerpt')
                        ->label('المقتطف (على البطاقة وتحت العنوان في التفاصيل)')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('card_footer_title')
                        ->label('اسم/عنوان أسفل البطاقة')
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('card_footer_subtitle')
                        ->label('سطر إضافي أسفل البطاقة')
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('badge')
                        ->label('شارة البطاقة')
                        ->helperText('مثال: قصة نجاح — تظهر على البطاقة في الرئيسية وقائمة القصص')
                        ->placeholder('قصة نجاح')
                        ->maxLength(80)
                        ->columnSpanFull(),

                    Forms\Components\FileUpload::make('cover_image')
                        ->label('صورة البطاقة')
                        ->image()
                        ->disk('public')
                        ->directory('stories/covers')
                        ->visibility('public')
                        ->imageEditor()
                        ->imagePreviewHeight('200')
                        ->columnSpanFull(),
                ]),

                Forms\Components\Section::make('2) صفحة التفاصيل — الهيرو والمحتوى')->schema([
                    Forms\Components\FileUpload::make('hero_image')
                        ->label('صورة الهيرو (اختياري)')
                        ->helperText('إن تُركت فارغة تُستخدم صورة البطاقة')
                        ->image()
                        ->disk('public')
                        ->directory('stories/hero')
                        ->visibility('public')
                        ->imageEditor()
                        ->columnSpanFull(),

                    Forms\Components\RichEditor::make('content')
                        ->label('محتوى القصة')
                        ->toolbarButtons([
                            'bold', 'italic', 'underline', 'strike',
                            'h2', 'h3',
                            'bulletList', 'orderedList',
                            'link', 'blockquote',
                            'undo', 'redo',
                        ])
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('quote_text')
                        ->label('نص الاقتباس المميز')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('quote_author')
                        ->label('مصدر الاقتباس')
                        ->columnSpanFull(),

                    Forms\Components\Repeater::make('images')
                        ->label('صور القصة (معرض)')
                        ->relationship('images')
                        ->schema([
                            Forms\Components\FileUpload::make('image')
                                ->label('الصورة')
                                ->image()
                                ->required()
                                ->disk('public')
                                ->directory('stories/gallery')
                                ->visibility('public')
                                ->imageEditor()
                                ->columnSpanFull(),
                        ])
                        ->reorderable()
                        ->orderColumn('sort_order')
                        ->collapsible()
                        ->itemLabel(fn ($state): ?string => filled(is_array($state) ? ($state['image'] ?? null) : null) ? 'صورة' : 'صورة جديدة')
                        ->addActionLabel('➕ إضافة صورة')
                        ->columnSpanFull(),
                ]),

                Forms\Components\Section::make('3) التصنيفات')->schema([
                    Forms\Components\Placeholder::make('categories_badge_hint')
                        ->label('شارة البطاقة')
                        ->content('تُعدَّل من حقل «شارة البطاقة» في القسم 1. إن تُركت فارغة تُستخدم أول تصنيف أدناه.')
                        ->columnSpanFull(),
                    Forms\Components\Repeater::make('categories')
                        ->label('تصنيفات القصة')
                        ->schema([
                            Forms\Components\TextInput::make('name_ar')
                                ->label('الاسم (عربي)')
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                    if (blank($get('slug'))) {
                                        $set('slug', Str::slug((string) $state));
                                    }
                                }),
                            Forms\Components\TextInput::make('name_en')->label('Name (EN)'),
                            Forms\Components\TextInput::make('slug')
                                ->label('Slug')
                                ->maxLength(80),
                        ])
                        ->columns(3)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(function ($state): ?string {
                            if (is_string($state)) {
                                $state = json_decode($state, true);
                            }

                            if (! is_array($state)) {
                                return 'تصنيف';
                            }

                            return $state['name_ar'] ?? $state['name_en'] ?? 'تصنيف';
                        })
                        ->addActionLabel('➕ إضافة تصنيف')
                        ->columnSpanFull(),
                ]),
            ]),

            Forms\Components\Group::make()->columnSpan(1)->schema([
                Forms\Components\Section::make('النشر')->schema([
                    Forms\Components\TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    Forms\Components\Select::make('status')
                        ->label('الحالة')
                        ->options([
                            'draft' => 'مسودة',
                            'published' => 'منشور',
                        ])
                        ->default('draft')
                        ->required(),

                    Forms\Components\DateTimePicker::make('published_at')
                        ->label('تاريخ النشر')
                        ->seconds(false),

                    Forms\Components\Toggle::make('is_featured')
                        ->label('إبراز في الرئيسية')
                        ->helperText('يُفضَّل إبراز القصص لقسم «القصص» في الصفحة الرئيسية'),

                    Forms\Components\TextInput::make('sort_order')
                        ->label('الترتيب')
                        ->numeric()
                        ->default(0),
                ]),

                Forms\Components\Section::make('البيانات الوصفية')->schema([
                    Forms\Components\TextInput::make('author_name')
                        ->label('الكاتب')
                        ->placeholder('فريق منصة صوت'),

                    Forms\Components\TextInput::make('read_time_minutes')
                        ->label('وقت القراءة (دقائق)')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(120)
                        ->helperText('اختياري — يُحسب تلقائياً من المحتوى إذا تُرك فارغاً'),

                    Forms\Components\Placeholder::make('views_count_info')
                        ->label('عدد المشاهدات')
                        ->content(fn (?Story $record): string => number_format((int) ($record?->views_count ?? 0)))
                        ->helperText('يُزاد تلقائياً عند فتح القصة عبر API')
                        ->visibleOn('edit'),
                ]),
            ]),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                MediaUrl::tableImageColumn('cover_image', 'الصورة'),
                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('badge')
                    ->label('شارة البطاقة')
                    ->getStateUsing(fn (Story $record): ?string => $record->primaryBadge()['ar'] ?? null)
                    ->badge()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('category_labels')
                    ->label('التصنيفات')
                    ->getStateUsing(fn (Story $record): array => $record->categoryLabels('ar'))
                    ->badge()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('images_count')
                    ->label('الصور')
                    ->counts('images'),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('مميز')
                    ->boolean(),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('تاريخ النشر')
                    ->dateTime('Y-m-d')
                    ->sortable(),
                Tables\Columns\TextColumn::make('views_count')
                    ->label('المشاهدات')
                    ->sortable(),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'draft' => 'مسودة',
                        'published' => 'منشور',
                    ]),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('مميز في الرئيسية'),
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
            'index' => Pages\ListStories::route('/'),
            'create' => Pages\CreateStory::route('/create'),
            'edit' => Pages\EditStory::route('/{record}/edit'),
        ];
    }
}
