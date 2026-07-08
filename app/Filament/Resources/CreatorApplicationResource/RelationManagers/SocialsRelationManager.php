<?php

namespace App\Filament\Resources\CreatorApplicationResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SocialsRelationManager extends RelationManager
{
    protected static string $relationship = 'socials';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Attached Social Accounts');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('platform')->label('المنصة')->required(),
            Forms\Components\TextInput::make('url')->label('الرابط')->url()->required(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('platform')
            ->columns([
                Tables\Columns\TextColumn::make('platform')->label('المنصة'),
                Tables\Columns\TextColumn::make('url')->label('الرابط')->url(fn ($record) => $record->url, true),
            ])
            ->headerActions([])
            ->actions([]);
    }
}
