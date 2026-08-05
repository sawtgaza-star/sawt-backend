<?php

namespace App\Filament\Resources\AboutPageResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\Concerns\Translatable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ValuesRelationManager extends RelationManager
{
    use Translatable;

    protected static string $relationship = 'values';

    protected static ?string $title = 'القيم';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\FileUpload::make('icon')
                ->label('الأيقونة / الصورة')
                ->image()->disk('public')->directory('about/values')
                ->columnSpanFull(),
            Forms\Components\TextInput::make('title')->label('العنوان')->required(),
            Forms\Components\Textarea::make('description')->label('الوصف')->rows(3)->columnSpanFull(),
            Forms\Components\TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->label('مفعّل')->default(true),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\ImageColumn::make('icon')->label('أيقونة')->disk('public')->circular(),
                Tables\Columns\TextColumn::make('title')->label('العنوان')->searchable(),
                Tables\Columns\TextColumn::make('description')->label('الوصف')->limit(40),
                Tables\Columns\IconColumn::make('is_active')->label('مفعّل')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->label('ترتيب')->sortable(),
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
