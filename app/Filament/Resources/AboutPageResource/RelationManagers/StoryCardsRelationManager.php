<?php

namespace App\Filament\Resources\AboutPageResource\RelationManagers;

use App\Support\MediaUrl;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\Concerns\Translatable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StoryCardsRelationManager extends RelationManager
{
    use Translatable;

    protected static string $relationship = 'storyCards';

    protected static ?string $title = 'بطاقات القصة';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\FileUpload::make('icon')
                ->label(__('الأيقونة / الصورة'))
                ->image()->disk('public')->directory('about/story')
                ->columnSpanFull(),
            Forms\Components\TextInput::make('title')->label(__('العنوان'))->required(),
            Forms\Components\Textarea::make('description')->label(__('الوصف'))->rows(4)->columnSpanFull(),
            Forms\Components\TextInput::make('sort_order')->label(__('الترتيب'))->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label(__('مفعّل'))->default(true),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                MediaUrl::tableImageColumn('icon', 'أيقونة')->circular(),
                Tables\Columns\TextColumn::make('title')->label(__('العنوان'))->searchable(),
                Tables\Columns\TextColumn::make('description')->label(__('الوصف'))->limit(40),
                Tables\Columns\IconColumn::make('is_active')->label(__('مفعّل'))->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->label(__('ترتيب'))->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
