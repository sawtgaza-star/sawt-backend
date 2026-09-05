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
                ->label(__('المنصة'))
                ->options([
                    'instagram' => 'Instagram',
                    'facebook' => 'Facebook',
                    'twitter' => 'X / Twitter',
                    'linkedin' => 'LinkedIn',
                    'youtube' => 'YouTube',
                    'tiktok' => 'TikTok',
                    'telegram' => 'Telegram',
                    'other' => __('أخرى'),
                ])
                ->required(),

            Forms\Components\TextInput::make('url')
                ->label(__('الرابط'))
                ->url()
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('followers_count')
                ->label(__('عدد المتابعين'))
                ->numeric()
                ->default(0),

            Forms\Components\TextInput::make('display_order')
                ->label(__('ترتيب العرض'))
                ->numeric()
                ->default(0),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('platform')
            ->columns([
                Tables\Columns\TextColumn::make('platform')->label(__('المنصة')),
                Tables\Columns\TextColumn::make('url')->label(__('الرابط'))->limit(40),
                Tables\Columns\TextColumn::make('followers_count')->label(__('المتابعون'))->numeric(),
                Tables\Columns\TextColumn::make('display_order')->label(__('الترتيب'))->sortable(),
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
