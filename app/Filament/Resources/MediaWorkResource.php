<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaWorkResource\Pages;
use App\Models\MediaServiceItem;
use App\Models\MediaWork;
use App\Support\LocaleText;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

/**
 * CRUD for Sawt Media portfolio works (/media/works/{slug}).
 * Linked to a service for «نماذج من أعمالنا» on the service detail page.
 */
class MediaWorkResource extends Resource
{
    protected static ?string $model = MediaWork::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('Sawt Media');
    }

    public static function getModelLabel(): string
    {
        return __('Media Work');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Media Works');
    }

    public static function getNavigationLabel(): string
    {
        return __('Media Works');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Work')->columnSpanFull()->tabs([
                Forms\Components\Tabs\Tab::make(__('أساسي'))->icon('heroicon-o-information-circle')->schema([
                    Forms\Components\Section::make(__('بطاقة العمل'))->schema([
                        Forms\Components\Select::make('media_service_id')
                            ->label(__('الخدمة المرتبطة'))
                            ->helperText(__('يظهر ضمن «نماذج من أعمالنا» في صفحة تلك الخدمة.'))
                            ->options(fn () => MediaServiceItem::query()
                                ->orderBy('sort_order')
                                ->get()
                                ->mapWithKeys(fn (MediaServiceItem $s) => [
                                    $s->id => LocaleText::translation($s, 'title') ?: $s->slug,
                                ]))
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('title.ar')
                            ->label(__('العنوان (عربي)'))
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('title.en')
                            ->label('Title (EN)')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set, ?MediaWork $record) {
                                if ($record?->exists || filled($get('slug'))) {
                                    return;
                                }
                                $set('slug', Str::slug((string) $state));
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug (URL)')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                            ->helperText(__('مثال: film → /media/works/film')),
                        Forms\Components\TextInput::make('sort_order')->label(__('الترتيب'))->numeric()->default(0),
                        Forms\Components\Toggle::make('is_active')->label(__('نشط'))->default(true),
                        Forms\Components\Toggle::make('show_on_landing')->label(__('يظهر في الصفحة الأولى'))->default(true),
                        Forms\Components\FileUpload::make('cover_image')
                            ->label(__('صورة الغلاف / البطاقة'))
                            ->image()->disk('public')->directory('media/works')->imageEditor()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('category.ar')->label(__('التصنيف (عربي)')),
                        Forms\Components\TextInput::make('category.en')->label('Category (EN)'),
                        Forms\Components\TextInput::make('tag.ar')->label(__('وسم (عربي)')),
                        Forms\Components\TextInput::make('tag.en')->label('Tag (EN)'),
                        Forms\Components\TextInput::make('date.ar')->label(__('التاريخ (عربي)')),
                        Forms\Components\TextInput::make('date.en')->label('Date (EN)'),
                        Forms\Components\Textarea::make('summary.ar')->label(__('الملخص (عربي)'))->rows(3),
                        Forms\Components\Textarea::make('summary.en')->label('Summary (EN)')->rows(3),
                    ])->columns(2),

                    Forms\Components\Section::make(__('إحصائيات بارزة (أعلى الصفحة)'))->schema([
                        Forms\Components\Repeater::make('highlights')
                            ->label(__('أرقام'))
                            ->schema([
                                Forms\Components\TextInput::make('value')->label(__('القيمة'))->required()->placeholder('+60%'),
                                Forms\Components\TextInput::make('label_ar')->label(__('التسمية (عربي)')),
                                Forms\Components\TextInput::make('label_en')->label('Label (EN)'),
                            ])
                            ->columns(3)
                            ->reorderable()
                            ->addActionLabel(__('➕ رقم'))
                            ->columnSpanFull(),
                    ]),
                ]),

                Forms\Components\Tabs\Tab::make(__('عن المشروع'))->icon('heroicon-o-document-text')->schema([
                    Forms\Components\Section::make(__('المحتوى'))->schema([
                        Forms\Components\Textarea::make('about.ar')->label(__('عن المشروع (عربي)'))->rows(4),
                        Forms\Components\Textarea::make('about.en')->label('About (EN)')->rows(4),
                        Forms\Components\Textarea::make('challenges.ar')
                            ->label(__('التحديات (عربي، سطر لكل نقطة)'))->rows(3),
                        Forms\Components\Textarea::make('challenges.en')
                            ->label('Challenges (EN)')->rows(3),
                        Forms\Components\Textarea::make('solutions.ar')
                            ->label(__('الحلول (عربي، سطر لكل نقطة)'))->rows(3),
                        Forms\Components\Textarea::make('solutions.en')
                            ->label('Solutions (EN)')->rows(3),
                    ])->columns(2),
                ]),

                Forms\Components\Tabs\Tab::make(__('المراحل'))->icon('heroicon-o-queue-list')->schema([
                    Forms\Components\Repeater::make('stages')
                        ->label(__('مراحل المشروع'))
                        ->schema([
                            Forms\Components\TextInput::make('title_ar')->label(__('العنوان (عربي)'))->required(),
                            Forms\Components\TextInput::make('title_en')->label('Title (EN)'),
                            Forms\Components\Textarea::make('body_ar')->label(__('الوصف (عربي)'))->rows(2),
                            Forms\Components\Textarea::make('body_en')->label('Body (EN)')->rows(2),
                        ])
                        ->columns(2)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => LocaleText::pick($state, 'title', 'مرحلة') ?: null)
                        ->addActionLabel(__('➕ مرحلة'))
                        ->columnSpanFull(),
                ]),

                Forms\Components\Tabs\Tab::make(__('رأي العميل'))->icon('heroicon-o-chat-bubble-bottom-center-text')->schema([
                    Forms\Components\Section::make(__('الشهادة'))->schema([
                        Forms\Components\FileUpload::make('client_avatar')
                            ->label(__('صورة العميل'))
                            ->image()->disk('public')->directory('media/works/clients')->imageEditor()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('client_name')->label(__('اسم العميل')),
                        Forms\Components\TextInput::make('client_role.ar')->label(__('المسمى (عربي)')),
                        Forms\Components\TextInput::make('client_role.en')->label('Role (EN)'),
                        Forms\Components\Textarea::make('client_quote.ar')->label(__('الرأي (عربي)'))->rows(3),
                        Forms\Components\Textarea::make('client_quote.en')->label('Quote (EN)')->rows(3),
                    ])->columns(2),
                ]),

                Forms\Components\Tabs\Tab::make(__('النتائج والصور'))->icon('heroicon-o-chart-bar')->schema([
                    Forms\Components\Section::make(__('النتائج'))->schema([
                        Forms\Components\Repeater::make('results')
                            ->label(__('أرقام النتائج'))
                            ->schema([
                                Forms\Components\TextInput::make('value')->label(__('القيمة'))->required()->placeholder('+45'),
                                Forms\Components\TextInput::make('label_ar')->label(__('التسمية (عربي)')),
                                Forms\Components\TextInput::make('label_en')->label('Label (EN)'),
                            ])
                            ->columns(3)
                            ->reorderable()
                            ->addActionLabel(__('➕ نتيجة'))
                            ->columnSpanFull(),
                    ]),
                    Forms\Components\Section::make(__('صور من المشروع'))->schema([
                        Forms\Components\FileUpload::make('gallery')
                            ->label(__('المعرض'))
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->disk('public')
                            ->directory('media/works/gallery')
                            ->imageEditor()
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
                Tables\Columns\TextColumn::make('title')
                    ->label(__('Title'))
                    ->formatStateUsing(fn ($state, MediaWork $record) => LocaleText::translation($record, 'title'))
                    ->searchable(query: function ($query, string $search) {
                        $query->where('title->ar', 'like', "%{$search}%")
                            ->orWhere('title->en', 'like', "%{$search}%");
                    }),
                Tables\Columns\TextColumn::make('slug')->label(__('Slug'))->searchable()->copyable(),
                Tables\Columns\TextColumn::make('service.title')
                    ->label(__('Service'))
                    ->formatStateUsing(fn ($state, MediaWork $record) => $record->service
                        ? (LocaleText::translation($record->service, 'title') ?: $record->service->slug)
                        : '—'),
                Tables\Columns\IconColumn::make('show_on_landing')->label(__('Landing page'))->boolean(),
                Tables\Columns\IconColumn::make('is_active')->label(__('Active'))->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->label(__('Order'))->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('media_service_id')
                    ->label(__('Service'))
                    ->options(fn () => MediaServiceItem::query()
                        ->orderBy('sort_order')
                        ->get()
                        ->mapWithKeys(fn (MediaServiceItem $s) => [
                            $s->id => LocaleText::translation($s, 'title') ?: $s->slug,
                        ])),
                Tables\Filters\TernaryFilter::make('is_active')->label(__('Active')),
                Tables\Filters\TernaryFilter::make('show_on_landing')->label(__('Landing page')),
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
            'index' => Pages\ListMediaWorks::route('/'),
            'create' => Pages\CreateMediaWork::route('/create'),
            'edit' => Pages\EditMediaWork::route('/{record}/edit'),
        ];
    }
}
