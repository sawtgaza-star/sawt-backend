<?php

namespace App\Filament\Resources\CreatorResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\Concerns\Translatable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CollaborationsRelationManager extends RelationManager
{
    use Translatable;

    protected static string $relationship = 'collaborations';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Collaborations');
    }

    protected static function getModelLabel(): ?string
    {
        return __('Collaboration');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('company_name')
                ->label('اسم الجهة/الشركة')
                ->required()
                ->maxLength(255),

            Forms\Components\FileUpload::make('company_logo')
                ->label('شعار الجهة')
                ->image()
                ->disk('public')
                ->directory('creators/collaboration-logos')
                ->visibility('public')
                ->imagePreviewHeight('150'),

            Forms\Components\Textarea::make('description')
                ->label('وصف التعاون')
                ->rows(3)
                ->columnSpanFull(),

            Forms\Components\TextInput::make('reviewer_name')->label('اسم صاحب التقييم'),
            Forms\Components\TextInput::make('reviewer_role')->label('منصب صاحب التقييم'),

            Forms\Components\Select::make('rating')
                ->label('التقييم')
                ->options([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5']),

            Forms\Components\TextInput::make('featured_video_url')->label('رابط فيديو مميّز')->url(),
            Forms\Components\TextInput::make('featured_video_views')->label('مشاهدات الفيديو المميّز')->numeric()->default(0),

            Forms\Components\Toggle::make('is_featured')->label('تعاون مميّز'),
            Forms\Components\TextInput::make('sort_order')->label('ترتيب العرض')->numeric()->default(0),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('company_name')
            ->columns([
                Tables\Columns\ImageColumn::make('company_logo')
                    ->label('')
                    ->disk('public')
                    ->height(40)
                    ->square(),
                Tables\Columns\TextColumn::make('company_name')->label('الجهة'),
                Tables\Columns\TextColumn::make('reviewer_name')->label('صاحب التقييم'),
                Tables\Columns\TextColumn::make('rating')->label('التقييم')->badge(),
                Tables\Columns\IconColumn::make('is_featured')->label('مميّز')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->headerActions([
                Tables\Actions\LocaleSwitcher::make(),
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
