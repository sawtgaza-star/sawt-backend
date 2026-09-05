<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaServiceResource\Pages;
use App\Models\MediaServiceItem;
use App\Support\LocaleText;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

/**
 * CRUD for Sawt Media services (separate from إعدادات ميديا settings).
 * Public: landing cards + GET /api/v1/pages/media/services/{slug}
 */
class MediaServiceResource extends Resource
{
    protected static ?string $model = MediaServiceItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('Sawt Media');
    }

    public static function getModelLabel(): string
    {
        return __('Media Service');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Media Services');
    }

    public static function getNavigationLabel(): string
    {
        return __('Media Services');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Service')->columnSpanFull()->tabs([
                Forms\Components\Tabs\Tab::make(__('أساسي'))->icon('heroicon-o-information-circle')->schema([
                    Forms\Components\Section::make(__('بطاقة الخدمة (الصفحة الأولى)'))->schema([
                        Forms\Components\TextInput::make('title.ar')
                            ->label(__('العنوان (عربي)'))
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('title.en')
                            ->label(__('Title (EN)'))
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set, ?MediaServiceItem $record) {
                                // Auto-fill slug from English title on create only
                                if ($record?->exists || filled($get('slug'))) {
                                    return;
                                }
                                $set('slug', Str::slug((string) $state));
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->label(__('Slug (URL)'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                            ->helperText(__('مثال: photography → /media/services/photography')),
                        Forms\Components\TextInput::make('number')->label(__('الرقم'))->placeholder('01'),
                        Forms\Components\TextInput::make('sort_order')->label(__('الترتيب'))->numeric()->default(0),
                        Forms\Components\Toggle::make('is_active')->label(__('نشط'))->default(true),
                        // Comma-separated list per locale → API splits into tags.ar / tags.en arrays
                        Forms\Components\TextInput::make('tags.ar')
                            ->label(__('وسوم عربي (مفصولة بفاصلة)'))
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('tags.en')
                            ->label(__('Tags EN (comma-separated)'))
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('image')
                            ->label(__('صورة البطاقة (الصفحة الأولى)'))
                            ->image()->disk('public')->directory('media/services')->imageEditor()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('tagline.ar')->label(__('سطر فرعي (عربي)')),
                        Forms\Components\TextInput::make('tagline.en')->label(__('Tagline (EN)')),
                        Forms\Components\Textarea::make('description.ar')->label(__('الوصف (عربي)'))->rows(3),
                        Forms\Components\Textarea::make('description.en')->label(__('Description (EN)'))->rows(3),
                    ])->columns(2),
                ]),

                Forms\Components\Tabs\Tab::make(__('صفحة التفاصيل'))->icon('heroicon-o-document-text')->schema([
                    // Carousel under the service title (see sawtgaza.com/media/services/video)
                    Forms\Components\Section::make(__('معرض صور الخدمة'))->schema([
                        Forms\Components\FileUpload::make('gallery')
                            ->label(__('صور المعرض'))
                            ->helperText(__('الشريط الأفقي تحت عنوان الخدمة في صفحة التفاصيل.'))
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->disk('public')
                            ->directory('media/services/gallery')
                            ->imageEditor()
                            ->columnSpanFull(),
                    ]),

                    Forms\Components\Section::make(__('ماذا تشمل الخدمة'))->schema([
                        Forms\Components\Textarea::make('includes.ar')
                            ->label(__('نقاط (عربي، سطر لكل نقطة)'))
                            ->rows(4),
                        Forms\Components\Textarea::make('includes.en')
                            ->label(__('Includes (EN, one per line)'))
                            ->rows(4),
                    ])->columns(2),

                    Forms\Components\Section::make(__('نماذج من أعمالنا'))
                        ->description(__('تُدار من صوت ميديا → أعمال ميديا، واربط العمل بهذه الخدمة.'))
                        ->schema([
                            Forms\Components\Placeholder::make('media_service_works_hint')
                                ->label(__('الأعمال المرتبطة'))
                                ->content(__('أنشئ أعمالاً في «أعمال ميديا» واختر هذه الخدمة — تظهر هنا في API ضمن works.items وتفتح /media/works/{slug}.'))
                                ->columnSpanFull(),
                        ]),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')->label(__('Number'))->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('Title'))
                    ->formatStateUsing(fn ($state, MediaServiceItem $record) => LocaleText::translation($record, 'title'))
                    ->searchable(query: function ($query, string $search) {
                        $query->where('title->ar', 'like', "%{$search}%")
                            ->orWhere('title->en', 'like', "%{$search}%");
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')->label(__('Slug'))->searchable()->copyable(),
                Tables\Columns\IconColumn::make('is_active')->label(__('Active'))->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->label(__('Order'))->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->label(__('Last updated'))->dateTime('Y-m-d')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label(__('Active')),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMediaServices::route('/'),
            'create' => Pages\CreateMediaService::route('/create'),
            'edit' => Pages\EditMediaService::route('/{record}/edit'),
        ];
    }
}
