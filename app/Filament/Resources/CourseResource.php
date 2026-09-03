<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Models\Course;
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

    public static function getModelLabel(): string
    {
        return 'كورس';
    }

    public static function getPluralModelLabel(): string
    {
        return 'الكورسات';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Course')->columnSpanFull()->tabs([
                Forms\Components\Tabs\Tab::make('أساسي')->icon('heroicon-o-information-circle')->schema([
                    Forms\Components\Section::make('معلومات الكورس')->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('العنوان')->required()->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state)))
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('slug')
                            ->label('الرابط (slug)')->required()->maxLength(255)->unique(ignoreRecord: true)
                            ->helperText('مثال: graphic-design → /courses/graphic-design'),
                        Forms\Components\Textarea::make('description')
                            ->label('الوصف')->rows(4)->columnSpanFull(),
                        Forms\Components\FileUpload::make('image')
                            ->label('صورة بطاقة الحاضنة')
                            ->helperText('تظهر فقط في بطاقات «دوراتنا الأكثر شهرة» — ليست لصورة صفحة التفاصيل')
                            ->image()->directory('courses')->imageEditor()
                            ->columnSpanFull(),
                    ])->columns(2),

                    Forms\Components\Section::make('تصنيف الدورة والمدرب')->schema([
                        Forms\Components\Select::make('trainer_id')
                            ->label('مدرب الدورة')
                            ->relationship(
                                name: 'trainer',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->getTranslation('name', 'ar') ?: $record->getTranslation('name', 'en') ?: (string) $record->uuid)
                            ->searchable()
                            ->preload()
                            ->helperText('من قائمة مدربي الدورات — وليس صنّاع المحتوى')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->label('الاسم')->required(),
                                Forms\Components\TextInput::make('title')->label('المسمى'),
                                Forms\Components\Toggle::make('is_active')->label('نشط')->default(true),
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
                            ->label('تصنيف الدورة')
                            ->relationship(
                                name: 'courseCategory',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->getTranslation('name', 'ar') ?: $record->slug)
                            ->searchable()
                            ->preload()
                            ->helperText('تصنيفات الدورات فقط — منفصلة عن فئات المحتوى')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->label('الاسم')->required(),
                                Forms\Components\TextInput::make('slug')->label('Slug')->required(),
                                Forms\Components\Toggle::make('is_active')->label('مفعّل')->default(true),
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
                            ->label('المستوى')
                            ->options(['beginner' => 'مبتدئ', 'intermediate' => 'متوسط', 'advanced' => 'متقدم'])
                            ->default('beginner')->required(),
                        Forms\Components\Select::make('status')
                            ->label('الحالة')
                            ->options(['draft' => 'مسودة', 'published' => 'منشور'])
                            ->default('draft')->required(),
                        Forms\Components\Hidden::make('delivery_mode')->default('offline'),
                        Forms\Components\Placeholder::make('delivery_hint')
                            ->label('نوع الحضور')
                            ->content('جميع الكورسات حضورية (أوفلاين) فقط.')
                            ->columnSpanFull(),
                    ])->columns(2),
                ]),

                Forms\Components\Tabs\Tab::make('الجدول والمقاعد')->icon('heroicon-o-calendar')->schema([
                    Forms\Components\Section::make('الحضور والمواعيد')->schema([
                        Forms\Components\TextInput::make('location')
                            ->label('المكان')->maxLength(255),
                        Forms\Components\TextInput::make('location_details')
                            ->label('تفاصيل المكان')->maxLength(255),
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('تاريخ البدء'),
                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label('تاريخ الانتهاء'),
                        Forms\Components\DateTimePicker::make('registration_ends_at')
                            ->label('ينتهي التسجيل في')
                            ->helperText('يُستخدم للعدّ التنازلي في صفحة التفاصيل'),
                        Forms\Components\TextInput::make('duration_weeks')
                            ->label('المدة (أسابيع)')
                            ->numeric()->minValue(1),
                        Forms\Components\TextInput::make('max_seats')
                            ->label('عدد المقاعد')
                            ->numeric()->minValue(1)
                            ->helperText('اتركه فارغاً إذا لم يكن هناك حد'),
                    ])->columns(2),

                    Forms\Components\Section::make('بطاقة القائمة')->schema([
                        Forms\Components\TextInput::make('duration_hours')
                            ->label('ساعات البرنامج')
                            ->placeholder('15 ساعة'),
                        Forms\Components\TextInput::make('sessions_hours')
                            ->label('ساعات الجلسات')
                            ->placeholder('4 ساعات'),
                        Forms\Components\TextInput::make('rating')
                            ->label('التقييم (من 5)')
                            ->numeric()->minValue(0)->maxValue(5)->step(0.1),
                        Forms\Components\Toggle::make('is_coming_soon')
                            ->label('قريباً (قائمة انتظار)')
                            ->helperText('يظهر شارة «قريباً» وزر قائمة الانتظار بدل تفاصيل الكورس')
                            ->columnSpanFull(),
                    ])->columns(2),
                ]),

                Forms\Components\Tabs\Tab::make('أهداف البرنامج')->icon('heroicon-o-flag')->schema([
                    Forms\Components\Repeater::make('objectives')
                        ->label('الأهداف')
                        ->schema([
                            Forms\Components\FileUpload::make('icon')
                                ->label('الأيقونة')
                                ->image()->disk('public')->directory('courses/icons')->imageEditor()
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('title_ar')->label('العنوان (عربي)')->required(),
                            Forms\Components\TextInput::make('title_en')->label('Title (EN)'),
                            Forms\Components\Textarea::make('desc_ar')->label('الوصف (عربي)')->rows(2),
                            Forms\Components\Textarea::make('desc_en')->label('Description (EN)')->rows(2),
                        ])
                        ->columns(2)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['title_ar'] ?? 'هدف')
                        ->addActionLabel('➕ إضافة هدف')
                        ->columnSpanFull(),
                ]),

                Forms\Components\Tabs\Tab::make('محاور البرنامج')->icon('heroicon-o-list-bullet')->schema([
                    Forms\Components\Repeater::make('modules')
                        ->label('المحاور')
                        ->schema([
                            Forms\Components\TextInput::make('title_ar')->label('عنوان المحور (عربي)')->required(),
                            Forms\Components\TextInput::make('title_en')->label('Module title (EN)'),
                            Forms\Components\Repeater::make('lessons')
                                ->label('الدروس (اختياري)')
                                ->schema([
                                    Forms\Components\TextInput::make('title_ar')->label('الدرس (عربي)')->required(),
                                    Forms\Components\TextInput::make('title_en')->label('Lesson (EN)'),
                                    Forms\Components\TextInput::make('duration')->label('المدة')->placeholder('15 دقيقة'),
                                ])
                                ->columns(3)
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => $state['title_ar'] ?? 'درس')
                                ->addActionLabel('➕ درس')
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['title_ar'] ?? 'محور')
                        ->addActionLabel('➕ إضافة محور')
                        ->columnSpanFull(),
                ]),

                Forms\Components\Tabs\Tab::make('المخرجات والمزايا')->icon('heroicon-o-sparkles')->schema([
                    Forms\Components\Section::make('قبل البرنامج')->schema([
                        Forms\Components\Repeater::make('outcomes_before')
                            ->label('النقاط')
                            ->schema([
                                Forms\Components\TextInput::make('ar')->label('عربي')->required(),
                                Forms\Components\TextInput::make('en')->label('English'),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->addActionLabel('➕ نقطة')
                            ->columnSpanFull(),
                    ]),
                    Forms\Components\Section::make('بعد البرنامج')->schema([
                        Forms\Components\Repeater::make('outcomes_after')
                            ->label('النقاط')
                            ->schema([
                                Forms\Components\TextInput::make('ar')->label('عربي')->required(),
                                Forms\Components\TextInput::make('en')->label('English'),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->addActionLabel('➕ نقطة')
                            ->columnSpanFull(),
                    ]),
                    Forms\Components\Section::make('ماذا ستحصل عند انضمامك')->schema([
                        Forms\Components\Repeater::make('benefits')
                            ->label('المزايا')
                            ->schema([
                                Forms\Components\FileUpload::make('icon')
                                    ->label('الأيقونة')
                                    ->image()->disk('public')->directory('courses/icons')->imageEditor()
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('ar')->label('عربي')->required(),
                                Forms\Components\TextInput::make('en')->label('English'),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['ar'] ?? 'ميزة')
                            ->addActionLabel('➕ ميزة')
                            ->columnSpanFull(),
                    ]),
                ]),

                Forms\Components\Tabs\Tab::make('التسجيل والقبول')->icon('heroicon-o-clipboard-document-check')->schema([
                    Forms\Components\Section::make('شروط التسجيل')->schema([
                        Forms\Components\Repeater::make('requirements')
                            ->label('الشروط')
                            ->schema([
                                Forms\Components\TextInput::make('ar')->label('عربي')->required(),
                                Forms\Components\TextInput::make('en')->label('English'),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->addActionLabel('➕ شرط')
                            ->columnSpanFull()
                            ->helperText('إن كانت البيانات القديمة نصاً بسيطاً، أعد حفظ الشروط بهذا الشكل'),
                    ]),
                    Forms\Components\Section::make('آلية اختيار المشاركين')->schema([
                        Forms\Components\Repeater::make('selection_steps')
                            ->label('الخطوات')
                            ->schema([
                                Forms\Components\FileUpload::make('icon')
                                    ->label('الأيقونة')
                                    ->image()->disk('public')->directory('courses/icons')->imageEditor()
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('title_ar')->label('العنوان (عربي)')->required(),
                                Forms\Components\TextInput::make('title_en')->label('Title (EN)'),
                                Forms\Components\Textarea::make('desc_ar')->label('الوصف (عربي)')->rows(2),
                                Forms\Components\Textarea::make('desc_en')->label('Description (EN)')->rows(2),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['title_ar'] ?? 'خطوة')
                            ->addActionLabel('➕ خطوة')
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
                Tables\Columns\TextColumn::make('title')->label('العنوان')->searchable()->limit(40),
                Tables\Columns\TextColumn::make('slug')->label('Slug')->toggleable()->limit(20),
                Tables\Columns\TextColumn::make('location')->label('المكان')->toggleable()->limit(25),
                Tables\Columns\TextColumn::make('starts_at')->label('البدء')->dateTime('Y-m-d')->toggleable(),
                Tables\Columns\IconColumn::make('is_coming_soon')->label('قريباً')->boolean()->toggleable(),
                Tables\Columns\TextColumn::make('trainer.name')->label('المدرب')->toggleable(),
                Tables\Columns\TextColumn::make('courseCategory.name')->label('التصنيف')->toggleable(),
                Tables\Columns\TextColumn::make('students_count')->label('المقبولون')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('join_requests_count')
                    ->counts('joinRequests')
                    ->label('الطلبات')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')->label('الحالة')->badge()
                    ->colors(['gray' => 'draft', 'success' => 'published'])
                    ->formatStateUsing(fn ($state) => $state === 'published' ? 'منشور' : 'مسودة'),
                Tables\Columns\TextColumn::make('created_at')->label('أُنشئ')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('الحالة')
                    ->options(['draft' => 'مسودة', 'published' => 'منشور']),
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
