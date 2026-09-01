<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Filament\Resources\CourseResource\RelationManagers;
use App\Models\Course;
use App\Support\MediaUrl;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

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
            Forms\Components\Section::make('معلومات الكورس')->schema([
                Forms\Components\TextInput::make('title')
                    ->label('العنوان')->required()->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state)))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('slug')
                    ->label('الرابط (slug)')->required()->maxLength(255)->unique(ignoreRecord: true),
                Forms\Components\Textarea::make('description')
                    ->label('الوصف')->rows(4)->columnSpanFull(),
                Forms\Components\FileUpload::make('image')
                    ->label('صورة الغلاف')->image()->directory('courses')->imageEditor(),
            ])->columns(2),

            Forms\Components\Section::make('التصنيف والمدرّب')->schema([
                Forms\Components\Select::make('instructor_id')
                    ->label('المدرّب')->relationship('instructor', 'username')->searchable()->preload(),
                Forms\Components\Select::make('category_id')
                    ->label('التصنيف')->relationship('category', 'name')->searchable()->preload(),
                Forms\Components\Select::make('level')
                    ->label('المستوى')
                    ->options(['beginner' => 'مبتدئ', 'intermediate' => 'متوسط', 'advanced' => 'متقدم'])
                    ->default('beginner')->required(),
                Forms\Components\Select::make('status')
                    ->label('الحالة')
                    ->options(['draft' => 'مسودة', 'published' => 'منشور'])
                    ->default('draft')->required(),
                Forms\Components\Hidden::make('delivery_mode')->default('offline'),
            ])->columns(2),

            Forms\Components\Section::make('تفاصيل الحضور')->schema([
                Forms\Components\TextInput::make('location')
                    ->label('المكان')->maxLength(255)->required(),
                Forms\Components\TextInput::make('location_details')
                    ->label('تفاصيل المكان')->maxLength(255),
                Forms\Components\DateTimePicker::make('starts_at')
                    ->label('تاريخ/وقت البدء'),
                Forms\Components\DateTimePicker::make('ends_at')
                    ->label('تاريخ/وقت الانتهاء'),
                Forms\Components\TextInput::make('max_seats')
                    ->label('الحد الأقصى للمقاعد')
                    ->numeric()
                    ->minValue(1)
                    ->helperText('اتركه فارغاً إذا لم يكن هناك حد'),
                Forms\Components\TagsInput::make('requirements')
                    ->label('المتطلبات')
                    ->placeholder('أضف متطلباً ثم Enter')
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                MediaUrl::tableImageColumn('image', '')->circular(),
                Tables\Columns\TextColumn::make('title')->label('العنوان')->searchable()->limit(40),
                Tables\Columns\TextColumn::make('location')->label('المكان')->toggleable()->limit(25),
                Tables\Columns\TextColumn::make('starts_at')->label('البدء')->dateTime('Y-m-d')->toggleable(),
                Tables\Columns\TextColumn::make('instructor.username')->label('المدرّب')->toggleable(),
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
