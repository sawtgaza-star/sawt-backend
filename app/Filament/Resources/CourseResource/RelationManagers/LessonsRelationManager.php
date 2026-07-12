<?php

namespace App\Filament\Resources\CourseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class LessonsRelationManager extends RelationManager
{
    protected static string $relationship = 'lessons';

    protected static ?string $title = 'الدروس';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->label('عنوان الدرس')->required()->columnSpanFull(),
            Forms\Components\Select::make('course_section_id')
                ->label('القسم')
                ->relationship('section', 'title')
                ->searchable()->preload(),
            Forms\Components\Textarea::make('description')->label('وصف الدرس')->rows(2)->columnSpanFull(),
            Forms\Components\Select::make('video_provider')
                ->label('مصدر الفيديو')
                ->options(['url' => 'رابط مباشر', 'youtube' => 'يوتيوب', 'vimeo' => 'Vimeo', 'upload' => 'رفع'])
                ->default('url')->required(),
            Forms\Components\TextInput::make('video_url')->label('رابط الفيديو')->url()->columnSpanFull(),
            Forms\Components\TextInput::make('duration_seconds')->label('المدة (ثانية)')->numeric()->default(0),
            Forms\Components\Toggle::make('is_preview')->label('معاينة مجانية'),
            Forms\Components\TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('الدرس')->searchable(),
                Tables\Columns\TextColumn::make('section.title')->label('القسم')->toggleable(),
                Tables\Columns\IconColumn::make('is_preview')->label('معاينة')->boolean(),
                Tables\Columns\TextColumn::make('duration_seconds')->label('المدة')
                    ->formatStateUsing(fn ($state) => gmdate($state >= 3600 ? 'H:i:s' : 'i:s', (int) $state)),
                Tables\Columns\TextColumn::make('sort_order')->label('الترتيب')->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('إضافة درس'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
