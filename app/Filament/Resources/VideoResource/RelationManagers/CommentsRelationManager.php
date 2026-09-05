<?php

namespace App\Filament\Resources\VideoResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Comments');
    }

    protected static function getModelLabel(): ?string
    {
        return __('Comment');
    }

    // للمراقبة فقط — ما بنسمح بإضافة تعليقات من لوحة الأدمن
    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Textarea::make('comment')
                ->label(__('التعليق'))
                ->required()
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('comment')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label(__('المستخدم')),
                Tables\Columns\TextColumn::make('comment')->label(__('التعليق'))->limit(60)->wrap(),
                Tables\Columns\TextColumn::make('parent.comment')->label(__('رد على'))->limit(30)->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')->label(__('التاريخ'))->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([])
            ->actions([
                Tables\Actions\EditAction::make()->label(__('تعديل النص')),
                Tables\Actions\DeleteAction::make()->label(__('حذف (مراقبة)')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
