<?php

namespace App\Filament\Resources\CreatorResource\RelationManagers;

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
        return __('Social Accounts');
    }

    protected static function getModelLabel(): ?string
    {
        return __('Social Account');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('platform')
                ->label('المنصة')
                ->options([
                    'instagram' => 'Instagram',
                    'facebook' => 'Facebook',
                    'twitter' => 'X / Twitter',
                    'linkedin' => 'LinkedIn',
                    'youtube' => 'YouTube',
                    'tiktok' => 'TikTok',
                    'telegram' => 'Telegram',
                    'other' => 'أخرى',
                ])
                ->required(),

            Forms\Components\TextInput::make('url')
                ->label('الرابط')
                ->url()
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('followers_count')
                ->label('عدد المتابعين')
                ->numeric()
                ->default(0),

            Forms\Components\TextInput::make('display_order')
                ->label('ترتيب العرض')
                ->numeric()
                ->default(0),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('platform')
            ->columns([
                Tables\Columns\TextColumn::make('platform')->label('المنصة'),
                Tables\Columns\TextColumn::make('url')->label('الرابط')->limit(40),
                Tables\Columns\TextColumn::make('followers_count')->label('المتابعون')->numeric(),
                Tables\Columns\TextColumn::make('display_order')->label('الترتيب')->sortable(),
            ])
            ->defaultSort('display_order')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
