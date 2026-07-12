<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseEnrollmentResource\Pages;
use App\Models\CourseEnrollment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CourseEnrollmentResource extends Resource
{
    protected static ?string $model = CourseEnrollment::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('Courses');
    }

    public static function getModelLabel(): string
    {
        return 'تسجيل';
    }

    public static function getPluralModelLabel(): string
    {
        return 'التسجيلات';
    }

    public static function canCreate(): bool
    {
        return false; // التسجيلات تُنشأ عبر الشراء
    }

    public static function form(Form $form): Form
    {
        // للأدمن: تعديل الحالة يدوياً فقط (مثلاً استرجاع)
        return $form->schema([
            Forms\Components\Select::make('status')
                ->label('الحالة')
                ->options([
                    'pending' => 'قيد الانتظار',
                    'active' => 'مفعّل',
                    'refunded' => 'مُسترجع',
                    'cancelled' => 'ملغى',
                ])->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('course.title')->label('الكورس')->searchable()->limit(30),
                Tables\Columns\TextColumn::make('user.name')->label('الطالب')->searchable(),
                Tables\Columns\TextColumn::make('status')->label('الحالة')->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'active',
                        'gray' => 'refunded',
                        'danger' => 'cancelled',
                    ]),
                Tables\Columns\TextColumn::make('price_paid')->label('المدفوع')->money(fn ($record) => $record->currency)->sortable(),
                Tables\Columns\TextColumn::make('enrolled_at')->label('تاريخ التسجيل')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('الحالة')->options([
                    'pending' => 'قيد الانتظار',
                    'active' => 'مفعّل',
                    'refunded' => 'مُسترجع',
                    'cancelled' => 'ملغى',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('الحالة'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourseEnrollments::route('/'),
            'edit' => Pages\EditCourseEnrollment::route('/{record}/edit'),
        ];
    }
}
