<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignResource\Pages;
use App\Filament\Resources\CampaignResource\RelationManagers;
use App\Models\Campaign;
use App\Support\MediaUrl;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CampaignResource extends Resource
{
    use Translatable;

    protected static ?string $model = Campaign::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $recordRouteKeyName = 'uuid';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('Finance');
    }

    public static function getNavigationLabel(): string
    {
        return __('Campaigns');
    }

    public static function getModelLabel(): string
    {
        return __('Campaign');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Campaigns');
    }

    protected static ?string $recordTitleAttribute = 'title';

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'slug', 'uuid'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            __('Status') => $record->status,
            __('Slug') => $record->slug,
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('title')
                    ->label('العنوان')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) =>
                        $operation === 'create' ? $set('slug', Str::slug($state)) : null)
                    ->maxLength(255),

                Forms\Components\TextInput::make('slug')
                    ->label('الرابط (Slug)')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Forms\Components\Textarea::make('description')
                    ->label('الوصف')
                    ->rows(4)
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('image')
                    ->label('صورة الحملة')
                    ->image()
                    ->disk('public')
                    ->directory('campaigns')
                    ->visibility('public')
                    ->imagePreviewHeight('200')
                    ->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('التمويل')->schema([
                Forms\Components\TextInput::make('target_amount')
                    ->label('المبلغ المستهدف')
                    ->numeric()
                    ->prefix('$')
                    ->required(),

                Forms\Components\TextInput::make('current_amount')
                    ->label('المبلغ المُحصَّل حالياً')
                    ->numeric()
                    ->prefix('$')
                    ->default(0)
                    ->helperText('يُحدَّث تلقائياً غالباً عبر التبرعات — التعديل اليدوي للتصحيح فقط'),

                Forms\Components\DatePicker::make('start_date')->label('تاريخ البداية'),
                Forms\Components\DatePicker::make('end_date')->label('تاريخ النهاية'),

                Forms\Components\Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'draft' => 'مسودة',
                        'active' => 'نشطة',
                        'completed' => 'مكتملة',
                        'cancelled' => 'ملغاة',
                    ])
                    ->default('draft')
                    ->required(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                MediaUrl::tableImageColumn('image', '')->height(48)->square(),
                Tables\Columns\TextColumn::make('title')->label('العنوان')->searchable()->limit(35),
                Tables\Columns\TextColumn::make('target_amount')->label('المستهدف')->money('USD'),
                Tables\Columns\TextColumn::make('current_amount')->label('المُحصَّل')->money('USD'),
                Tables\Columns\TextColumn::make('progress_percent')
                    ->label('نسبة الإنجاز')
                    ->state(fn (Campaign $record) => $record->progress_percent . '%'),
                Tables\Columns\BadgeColumn::make('status')->label('الحالة')
                    ->colors([
                        'gray' => 'draft',
                        'success' => 'active',
                        'primary' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state) => [
                        'draft' => 'مسودة', 'active' => 'نشطة',
                        'completed' => 'مكتملة', 'cancelled' => 'ملغاة',
                    ][$state] ?? $state),
                Tables\Columns\TextColumn::make('end_date')->label('تنتهي في')->date('Y-m-d')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'draft' => 'مسودة', 'active' => 'نشطة',
                        'completed' => 'مكتملة', 'cancelled' => 'ملغاة',
                    ]),
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\DonationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCampaigns::route('/'),
            'create' => Pages\CreateCampaign::route('/create'),
            'edit' => Pages\EditCampaign::route('/{record}/edit'),
        ];
    }
}
