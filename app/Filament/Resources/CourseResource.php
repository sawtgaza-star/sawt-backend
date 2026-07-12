<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Filament\Resources\CourseResource\RelationManagers;
use App\Models\Course;
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
            ])->columns(2),

            Forms\Components\Section::make('التسعير')->schema([
                Forms\Components\Toggle::make('is_free')
                    ->label('كورس مجاني')->live()->columnSpanFull(),
                Forms\Components\TextInput::make('price')
                    ->label('السعر')->numeric()->default(0)->prefix('$')
                    ->required(fn (Forms\Get $get) => ! $get('is_free'))
                    ->disabled(fn (Forms\Get $get) => (bool) $get('is_free')),
                Forms\Components\TextInput::make('currency')
                    ->label('العملة')->default('USD')->maxLength(3)->required(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')->label('')->circular(),
                Tables\Columns\TextColumn::make('title')->label('العنوان')->searchable()->limit(40),
                Tables\Columns\TextColumn::make('instructor.username')->label('المدرّب')->toggleable(),
                Tables\Columns\TextColumn::make('price')->label('السعر')->money(fn ($record) => $record->currency)->sortable(),
                Tables\Columns\IconColumn::make('is_free')->label('مجاني')->boolean(),
                Tables\Columns\TextColumn::make('students_count')->label('الطلاب')->numeric()->sortable(),
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
        return [
            RelationManagers\SectionsRelationManager::class,
            RelationManagers\LessonsRelationManager::class,
        ];
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
