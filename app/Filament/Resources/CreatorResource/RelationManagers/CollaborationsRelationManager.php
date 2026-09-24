<?php

namespace App\Filament\Resources\CreatorResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Attach partner companies to a creator with a per-company caption (quote / rating / author).
 * Section video is a shared latest Instagram reel — not edited here.
 */
class CollaborationsRelationManager extends RelationManager
{
    protected static string $relationship = 'partnerCompanies';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('أبرز التعاونات');
    }

    protected static function getModelLabel(): ?string
    {
        return __('تعاون');
    }

    /**
     * Pivot fields shared by attach + edit actions.
     *
     * @return array<int, Forms\Components\Component>
     */
    protected function collaborationPivotForm(): array
    {
        return [
            Forms\Components\TextInput::make('sort_order')
                ->label(__('ترتيب العرض'))
                ->numeric()
                ->default(0),
            Forms\Components\TextInput::make('rating')
                ->label(__('التقييم (نجوم)'))
                ->numeric()
                ->minValue(1)
                ->maxValue(5)
                ->default(5),
            Forms\Components\Textarea::make('quote_ar')
                ->label(__('الاقتباس / الكابشن (عربي)'))
                ->rows(3)
                ->columnSpanFull(),
            Forms\Components\Textarea::make('quote_en')
                ->label('Quote / caption (EN)')
                ->rows(3)
                ->columnSpanFull(),
            Forms\Components\TextInput::make('author_name')
                ->label(__('اسم صاحب الاقتباس'))
                ->maxLength(255),
            Forms\Components\TextInput::make('author_role_ar')
                ->label(__('المسمى (عربي)'))
                ->maxLength(255),
            Forms\Components\TextInput::make('author_role_en')
                ->label('Role (EN)')
                ->maxLength(255),
            Forms\Components\FileUpload::make('author_photo')
                ->label(__('صورة صاحب الاقتباس'))
                ->image()
                ->disk('public')
                ->directory('creators/collab-authors')
                ->visibility('public')
                ->imagePreviewHeight('80')
                ->columnSpanFull(),
        ];
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->collaborationPivotForm())->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(__('الشركة'))->searchable(),
                Tables\Columns\TextColumn::make('category')->label(__('التصنيف'))->limit(20),
                Tables\Columns\TextColumn::make('pivot.rating')->label(__('التقييم')),
                Tables\Columns\TextColumn::make('pivot.author_name')->label(__('صاحب الاقتباس')),
                Tables\Columns\TextColumn::make('pivot.sort_order')->label(__('الترتيب')),
            ])
            // Do not defaultSort/reorderable('sort_order') — ambiguous with company.sort_order vs pivot
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->label(__('الشركة'))
                            ->searchable()
                            ->preload(),
                        ...$this->collaborationPivotForm(),
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('editCaption')
                    ->label(__('تعديل الاقتباس'))
                    ->icon('heroicon-o-pencil-square')
                    ->fillForm(fn (Model $record): array => [
                        'sort_order' => (int) ($record->pivot->sort_order ?? 0),
                        'rating' => (int) ($record->pivot->rating ?? 5),
                        'quote_ar' => $record->pivot->quote_ar,
                        'quote_en' => $record->pivot->quote_en,
                        'author_name' => $record->pivot->author_name,
                        'author_role_ar' => $record->pivot->author_role_ar,
                        'author_role_en' => $record->pivot->author_role_en,
                        'author_photo' => $record->pivot->author_photo,
                    ])
                    ->form($this->collaborationPivotForm())
                    ->action(function (Model $record, array $data): void {
                        // Update pivot caption only — company name/logo stay on Partner Companies
                        $this->getOwnerRecord()->partnerCompanies()->updateExistingPivot($record->getKey(), [
                            'sort_order' => (int) ($data['sort_order'] ?? 0),
                            'rating' => (int) ($data['rating'] ?? 5),
                            'quote_ar' => $data['quote_ar'] ?? null,
                            'quote_en' => $data['quote_en'] ?? null,
                            'author_name' => $data['author_name'] ?? null,
                            'author_role_ar' => $data['author_role_ar'] ?? null,
                            'author_role_en' => $data['author_role_en'] ?? null,
                            'author_photo' => $data['author_photo'] ?? null,
                        ]);
                    }),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make(),
            ]);
    }
}
