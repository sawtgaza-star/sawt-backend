<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogResource\Pages;
use App\Models\Blog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BlogResource extends Resource
{
    use Translatable;

    protected static ?string $model = Blog::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $recordRouteKeyName = 'uuid';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return __('Content');
    }

    public static function getNavigationLabel(): string
    {
        return 'الأخبار / المدونة';
    }

    public static function getModelLabel(): string
    {
        return 'خبر';
    }

    public static function getPluralModelLabel(): string
    {
        return 'الأخبار';
    }

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make()->columnSpan(2)->schema([
                Forms\Components\Section::make('1) بطاقة القائمة (آخر أخبارنا)')->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('العنوان')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) =>
                            $operation === 'create' ? $set('slug', Str::slug($state)) : null)
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('excerpt')
                        ->label('المقتطف (يظهر في البطاقة وتحت العنوان في صفحة التفاصيل)')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\FileUpload::make('cover_image')
                        ->label('صورة البطاقة')
                        ->image()
                        ->disk('public')
                        ->directory('blogs/covers')
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
                        ->directory('blogs/hero')
                        ->visibility('public')
                        ->imageEditor()
                        ->columnSpanFull(),

                    Forms\Components\RichEditor::make('content')
                        ->label('محتوى الخبر')
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
                        ->placeholder('— أحد صانعي المحتوى في منصة صوت')
                        ->columnSpanFull(),

                    Forms\Components\Repeater::make('images')
                        ->label('صور الخبر (معرض)')
                        ->relationship('images')
                        ->schema([
                            Forms\Components\FileUpload::make('image')
                                ->label('الصورة')
                                ->image()
                                ->required()
                                ->disk('public')
                                ->directory('blogs/gallery')
                                ->visibility('public')
                                ->imageEditor()
                                ->columnSpanFull(),
                        ])
                        ->reorderable()
                        ->orderColumn('sort_order')
                        ->collapsible()
                        ->itemLabel(function ($state): ?string {
                            if (! is_array($state)) {
                                return 'صورة جديدة';
                            }

                            return filled($state['image'] ?? null) ? 'صورة' : 'صورة جديدة';
                        })
                        ->addActionLabel('➕ إضافة صورة')
                        ->columnSpanFull(),
                ]),

                Forms\Components\Section::make('3) التصنيفات (داخل الخبر)')->schema([
                    Forms\Components\Repeater::make('categories')
                        ->label('تصنيفات الخبر')
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
                        ->seconds(false)
                        ->minutesStep(60)
                        ->default(fn () => now()->startOfHour())
                        ->helperText('يُقارَن بالساعة (مثلاً 14:00). الخبر يظهر في الـ API عندما تصل الساعة المحددة أو بعدها.'),

                    Forms\Components\Toggle::make('is_featured')
                        ->label('إبراز في الرئيسية')
                        ->helperText('يُفضَّل إبراز 3 أخبار لقسم «آخر أخبارنا»'),

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
                        ->content(fn (?Blog $record): string => number_format((int) ($record?->views_count ?? 0)))
                        ->helperText('يُزاد تلقائياً عند فتح الخبر عبر API (صفحة التفاصيل)')
                        ->visibleOn('edit'),
                ]),
            ]),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('الصورة')
                    ->disk('public'),
                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('categories')
                    ->label('التصنيفات')
                    ->formatStateUsing(function ($state): string {
                        if (is_string($state)) {
                            $state = json_decode($state, true);
                        }

                        if (! is_array($state)) {
                            return '';
                        }

                        return collect($state)
                            ->pluck('name_ar')
                            ->filter()
                            ->implode('، ');
                    })
                    ->limit(30),
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
                    ->dateTime('Y-m-d H:i')
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
            'index' => Pages\ListBlogs::route('/'),
            'create' => Pages\CreateBlog::route('/create'),
            'edit' => Pages\EditBlog::route('/{record}/edit'),
        ];
    }
}
