<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Models\Course;
use App\Support\LocaleText;
use App\Support\MediaUrl;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

/**
 * Filament CRUD for incubator courses (full detail tabs + offline-only delivery).
 * Cover upload label = incubator card image; LocaleSwitcher on create/edit pages.
 */
class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('Courses');
    }

    public static function getNavigationLabel(): string
    {
        return __('Courses');
    }

    public static function getModelLabel(): string
    {
        return __('Course');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Courses');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Course')->columnSpanFull()->tabs([
                Forms\Components\Tabs\Tab::make(__('أساسي'))->icon('heroicon-o-information-circle')->schema([
                    Forms\Components\Section::make(__('معلومات الكورس'))->schema([
                        Forms\Components\TextInput::make('title')
                            ->label(__('العنوان'))->required()->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state)))
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('slug')
                            ->label(__('الرابط (slug)'))->required()->maxLength(255)->unique(ignoreRecord: true)
                            ->helperText(__('مثال: graphic-design → /courses/graphic-design')),
                        Forms\Components\Textarea::make('description')
                            ->label(__('الوصف'))->rows(4)->columnSpanFull(),
                        Forms\Components\FileUpload::make('image')
                            ->label(__('صورة بطاقة الحاضنة'))
                            ->helperText(__('تظهر فقط في بطاقات «دوراتنا الأكثر شهرة» — ليست لصورة صفحة التفاصيل'))
                            ->image()->directory('courses')->imageEditor()
                            ->columnSpanFull(),
                    ])->columns(2),

                    Forms\Components\Section::make(__('تصنيف الدورة والمدرب'))->schema([
                        Forms\Components\Select::make('trainer_id')
                            ->label(__('مدرب الدورة'))
                            ->relationship(
                                name: 'trainer',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => LocaleText::translation($record, 'name') ?: (string) $record->uuid)
                            ->searchable()
                            ->preload()
                            ->helperText(__('من قائمة مدربي الدورات — وليس صنّاع المحتوى'))
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->label(__('الاسم'))->required(),
                                Forms\Components\TextInput::make('title')->label(__('المسمى')),
                                Forms\Components\Toggle::make('is_active')->label(__('نشط'))->default(true),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                $name = (string) ($data['name'] ?? '');
                                $title = (string) ($data['title'] ?? '');
                                $trainer = \App\Models\CourseTrainer::query()->create([
                                    'name' => ['ar' => $name, 'en' => $name],
                                    'title' => ['ar' => $title, 'en' => $title],
                                    'is_active' => $data['is_active'] ?? true,
                                ]);

                                return $trainer->id;
                            }),
                        Forms\Components\Select::make('course_category_id')
                            ->label(__('تصنيف الدورة'))
                            ->relationship(
                                name: 'courseCategory',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => LocaleText::translation($record, 'name') ?: $record->slug)
                            ->searchable()
                            ->preload()
                            ->helperText(__('تصنيفات الدورات فقط — منفصلة عن فئات المحتوى'))
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->label(__('الاسم'))->required(),
                                Forms\Components\TextInput::make('slug')->label('Slug')->required(),
                                Forms\Components\Toggle::make('is_active')->label(__('مفعّل'))->default(true),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                $category = \App\Models\CourseCategory::query()->create([
                                    'name' => [
                                        'ar' => $data['name'],
                                        'en' => $data['name'],
                                    ],
                                    'slug' => $data['slug'],
                                    'is_active' => $data['is_active'] ?? true,
                                ]);

                                return $category->id;
                            }),
                        Forms\Components\Select::make('level')
                            ->label(__('المستوى'))
                            ->options(['beginner' => __('مبتدئ'), 'intermediate' => __('متوسط'), 'advanced' => 'متقدم'])
                            ->default('beginner')->required(),
                        Forms\Components\Select::make('status')
                            ->label(__('الحالة'))
                            ->options(['draft' => __('مسودة'), 'published' => 'منشور'])
                            ->default('draft')->required(),
                        Forms\Components\Hidden::make('delivery_mode')->default('offline'),
                        Forms\Components\Placeholder::make('delivery_hint')
                            ->label(__('نوع الحضور'))
                            ->content(__('جميع الكورسات حضورية (أوفلاين) فقط.'))
                            ->columnSpanFull(),
                    ])->columns(2),
                ]),

                Forms\Components\Tabs\Tab::make(__('الجدول والمقاعد'))->icon('heroicon-o-calendar')->schema([
                    Forms\Components\Section::make(__('الحضور والمواعيد'))->schema([
                        Forms\Components\TextInput::make('location')
                            ->label(__('المكان'))->maxLength(255),
                        Forms\Components\TextInput::make('location_details')
                            ->label(__('تفاصيل المكان'))->maxLength(255),
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label(__('تاريخ البدء')),
                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label(__('تاريخ الانتهاء')),
                        Forms\Components\DateTimePicker::make('registration_ends_at')
                            ->label(__('ينتهي التسجيل في'))
                            ->helperText(__('يُستخدم للعدّ التنازلي في صفحة التفاصيل')),
                        Forms\Components\TextInput::make('duration_weeks')
                            ->label(__('المدة (أسابيع)'))
                            ->numeric()->minValue(1),
                        Forms\Components\TextInput::make('max_seats')
                            ->label(__('عدد المقاعد'))
                            ->numeric()->minValue(1)
                            ->helperText(__('اتركه فارغاً إذا لم يكن هناك حد')),
                    ])->columns(2),

                    Forms\Components\Section::make(__('بطاقة القائمة'))->schema([
                        Forms\Components\TextInput::make('duration_hours')
                            ->label(__('ساعات البرنامج'))
                            ->placeholder(__('15 ساعة')),
                        Forms\Components\TextInput::make('sessions_hours')
                            ->label(__('ساعات الجلسات'))
                            ->placeholder(__('4 ساعات')),
                        Forms\Components\TextInput::make('rating')
                            ->label(__('التقييم (من 5)'))
                            ->numeric()->minValue(0)->maxValue(5)->step(0.1),
                        Forms\Components\Toggle::make('is_coming_soon')
                            ->label(__('قريباً (قائمة انتظار)'))
                            ->helperText(__('يظهر شارة «قريباً» وزر قائمة الانتظار بدل تفاصيل الكورس'))
                            ->columnSpanFull(),
                    ])->columns(2),
                ]),

                Forms\Components\Tabs\Tab::make(__('أهداف البرنامج'))->icon('heroicon-o-flag')->schema([
                    Forms\Components\Repeater::make('objectives')
                        ->label(__('الأهداف'))
                        ->schema([
                            Forms\Components\FileUpload::make('icon')
                                ->label(__('الأيقونة'))
                                ->image()->disk('public')->directory('courses/icons')->imageEditor()
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('title_ar')->label(__('العنوان (عربي)'))->required(),
                            Forms\Components\TextInput::make('title_en')->label('Title (EN)'),
                            Forms\Components\Textarea::make('desc_ar')->label(__('الوصف (عربي)'))->rows(2),
                            Forms\Components\Textarea::make('desc_en')->label('Description (EN)')->rows(2),
                        ])
                        ->columns(2)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => LocaleText::pick($state, 'title', 'هدف') ?: null)
                        ->addActionLabel(__('➕ إضافة هدف'))
                        ->columnSpanFull(),
                ]),

                Forms\Components\Tabs\Tab::make(__('محاور البرنامج'))->icon('heroicon-o-list-bullet')->schema([
                    Forms\Components\Repeater::make('modules')
                        ->label(__('المحاور'))
                        ->schema([
                            Forms\Components\TextInput::make('title_ar')->label(__('عنوان المحور (عربي)'))->required(),
                            Forms\Components\TextInput::make('title_en')->label('Module title (EN)'),
                            Forms\Components\Repeater::make('lessons')
                                ->label(__('الدروس (اختياري)'))
                                ->schema([
                                    Forms\Components\TextInput::make('title_ar')->label(__('الدرس (عربي)'))->required(),
                                    Forms\Components\TextInput::make('title_en')->label('Lesson (EN)'),
                                    Forms\Components\TextInput::make('duration')->label(__('المدة'))->placeholder(__('15 دقيقة')),
                                ])
                                ->columns(3)
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => LocaleText::pick($state, 'title', 'درس') ?: null)
                                ->addActionLabel(__('➕ درس'))
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => LocaleText::pick($state, 'title', 'محور') ?: null)
                        ->addActionLabel(__('➕ إضافة محور'))
                        ->columnSpanFull(),
                ]),

                Forms\Components\Tabs\Tab::make(__('المخرجات والمزايا'))->icon('heroicon-o-sparkles')->schema([
                    Forms\Components\Section::make(__('قبل البرنامج'))->schema([
                        Forms\Components\Repeater::make('outcomes_before')
                            ->label(__('النقاط'))
                            ->schema([
                                Forms\Components\TextInput::make('ar')->label(__('عربي'))->required(),
                                Forms\Components\TextInput::make('en')->label('English'),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->addActionLabel(__('➕ نقطة'))
                            ->columnSpanFull(),
                    ]),
                    Forms\Components\Section::make(__('بعد البرنامج'))->schema([
                        Forms\Components\Repeater::make('outcomes_after')
                            ->label(__('النقاط'))
                            ->schema([
                                Forms\Components\TextInput::make('ar')->label(__('عربي'))->required(),
                                Forms\Components\TextInput::make('en')->label('English'),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->addActionLabel(__('➕ نقطة'))
                            ->columnSpanFull(),
                    ]),
                    Forms\Components\Section::make(__('ماذا ستحصل عند انضمامك'))->schema([
                        Forms\Components\Repeater::make('benefits')
                            ->label(__('المزايا'))
                            ->schema([
                                Forms\Components\FileUpload::make('icon')
                                    ->label(__('الأيقونة'))
                                    ->image()->disk('public')->directory('courses/icons')->imageEditor()
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('ar')->label(__('عربي'))->required(),
                                Forms\Components\TextInput::make('en')->label('English'),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => (app()->getLocale() === 'en' ? ($state['en'] ?? $state['ar'] ?? null) : ($state['ar'] ?? $state['en'] ?? null)) ?: __('ميزة'))
                            ->addActionLabel(__('➕ ميزة'))
                            ->columnSpanFull(),
                    ]),
                ]),

                Forms\Components\Tabs\Tab::make(__('التسجيل والقبول'))->icon('heroicon-o-clipboard-document-check')->schema([
                    Forms\Components\Section::make(__('شروط التسجيل'))->schema([
                        Forms\Components\Repeater::make('requirements')
                            ->label(__('الشروط'))
                            ->schema([
                                Forms\Components\TextInput::make('ar')->label(__('عربي'))->required(),
                                Forms\Components\TextInput::make('en')->label('English'),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->addActionLabel(__('➕ شرط'))
                            ->columnSpanFull()
                            ->helperText(__('إن كانت البيانات القديمة نصاً بسيطاً، أعد حفظ الشروط بهذا الشكل')),
                    ]),
                    Forms\Components\Section::make(__('آلية اختيار المشاركين'))->schema([
                        Forms\Components\Repeater::make('selection_steps')
                            ->label(__('الخطوات'))
                            ->schema([
                                Forms\Components\FileUpload::make('icon')
                                    ->label(__('الأيقونة'))
                                    ->image()->disk('public')->directory('courses/icons')->imageEditor()
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('title_ar')->label(__('العنوان (عربي)'))->required(),
                                Forms\Components\TextInput::make('title_en')->label('Title (EN)'),
                                Forms\Components\Textarea::make('desc_ar')->label(__('الوصف (عربي)'))->rows(2),
                                Forms\Components\Textarea::make('desc_en')->label('Description (EN)')->rows(2),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => LocaleText::pick($state, 'title', 'خطوة') ?: null)
                            ->addActionLabel(__('➕ خطوة'))
                            ->columnSpanFull(),
                    ]),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                MediaUrl::tableImageColumn('image', '')->circular(),
                Tables\Columns\TextColumn::make('title')->label(__('العنوان'))->searchable()->limit(40),
                Tables\Columns\TextColumn::make('slug')->label('Slug')->toggleable()->limit(20),
                Tables\Columns\TextColumn::make('location')->label(__('المكان'))->toggleable()->limit(25),
                Tables\Columns\TextColumn::make('starts_at')->label(__('البدء'))->dateTime('Y-m-d')->toggleable(),
                Tables\Columns\IconColumn::make('is_coming_soon')->label(__('قريباً'))->boolean()->toggleable(),
                Tables\Columns\TextColumn::make('trainer.name')->label(__('المدرب'))->toggleable(),
                Tables\Columns\TextColumn::make('courseCategory.name')->label(__('التصنيف'))->toggleable(),
                Tables\Columns\TextColumn::make('students_count')->label(__('المقبولون'))->numeric()->sortable(),
                Tables\Columns\TextColumn::make('join_requests_count')
                    ->counts('joinRequests')
                    ->label(__('الطلبات'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')->label(__('الحالة'))->badge()
                    ->colors(['gray' => 'draft', 'success' => 'published'])
                    ->formatStateUsing(fn ($state) => $state === 'published' ? 'منشور' : 'مسودة'),
                Tables\Columns\TextColumn::make('created_at')->label(__('أُنشئ'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label(__('الحالة'))
                    ->options(['draft' => __('مسودة'), 'published' => 'منشور']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
