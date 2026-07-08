<?php

namespace App\Filament\Resources\VideoResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TagsRelationManager extends RelationManager
{
    protected static string $relationship = 'tags';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Tags');
    }

    public function form(Form $form): Form
    {
        // ما في حقول pivot إضافية بجدول video_tags — الإضافة والإزالة فقط عبر AttachAction/DetachAction
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('الوسم'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('إضافة وسم')
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()->label('إزالة'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
