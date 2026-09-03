<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseTrainerResource\Pages;
use App\Models\CourseTrainer;
use App\Support\MediaUrl;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Filament CRUD for course trainers (مدربو الدورات) — translatable via LocaleSwitcher.
 */
class CourseTrainerResource extends Resource
{
    use Translatable;

    protected static ?string $model = CourseTrainer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $recordRouteKeyName = 'uuid';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('Courses');
    }

    public static function getNavigationLabel(): string
    {
        return 'مدربو الدورات';
    }

    public static function getModelLabel(): string
    {
        return 'مدرب';
    }

    public static function getPluralModelLabel(): string
    {
        return 'مدربو الدورات';
    }

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('بيانات المدرب')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('الاسم')->required()->maxLength(255),
                Forms\Components\FileUpload::make('avatar')
                    ->label('الصورة')
                    ->image()->disk('public')->directory('courses/trainers')->imageEditor()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('title')
                    ->label('المسمى / التخصص')
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('bio')
                    ->label('نبذة')->rows(4)
                    ->columnSpanFull(),
                // Badge on incubator experts cards (e.g. «7 سنوات» / «7 years»)
                Forms\Components\TextInput::make('experience')
                    ->label('شارة الخبرة')
                    ->placeholder('7 سنوات')
                    ->maxLength(100)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('phone')->label('هاتف')->tel(),
                Forms\Components\TextInput::make('email')->label('بريد')->email(),
                Forms\Components\Toggle::make('is_active')->label('نشط')->default(true),
                Forms\Components\TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),
            ])->columns(2),

            Forms\Components\Section::make('وسائل التواصل')->schema([
                Forms\Components\Repeater::make('socials')
                    ->label('الحسابات')
                    ->schema([
                        Forms\Components\Select::make('platform')
                            ->label('المنصة')
                            ->options(CreatorResource::socialPlatformOptions())
                            ->required(),
                        Forms\Components\TextInput::make('url')
                            ->label('الرابط')->url()->required(),
                    ])
                    ->columns(2)
                    ->reorderable()
                    ->addActionLabel('➕ إضافة حساب')
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                MediaUrl::tableImageColumn('avatar', '')->circular(),
                Tables\Columns\TextColumn::make('name')->label('الاسم')->searchable(),
                Tables\Columns\TextColumn::make('email')->label('البريد')->toggleable(),
                Tables\Columns\TextColumn::make('courses_count')->counts('courses')->label('الكورسات'),
                Tables\Columns\IconColumn::make('is_active')->label('نشط')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->label('الترتيب')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListCourseTrainers::route('/'),
            'create' => Pages\CreateCourseTrainer::route('/create'),
            'edit' => Pages\EditCourseTrainer::route('/{record}/edit'),
        ];
    }
}
