<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AboutPageResource\Pages;
use App\Filament\Resources\AboutPageResource\RelationManagers;
use App\Models\AboutPage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AboutPageResource extends Resource
{
    use Translatable;

    protected static ?string $model = AboutPage::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-information-circle';

    protected static ?string $recordRouteKeyName = 'uuid';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('Content');
    }

    public static function getNavigationLabel(): string
    {
        return __('About Page');
    }

    public static function getModelLabel(): string
    {
        return __('About Page');
    }

    public static function getPluralModelLabel(): string
    {
        return __('About Page');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('About')->columnSpanFull()->tabs([
                Forms\Components\Tabs\Tab::make(__('الهيرو'))->schema([
                    Forms\Components\FileUpload::make('hero_image')
                        ->label(__('صورة الخلفية'))
                        ->image()->disk('public')->directory('about/hero')->imageEditor()
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('hero_title')->label(__('العنوان'))->required()->maxLength(255),
                    Forms\Components\Textarea::make('hero_description')->label(__('الوصف'))->rows(3)->columnSpanFull(),
                ])->columns(2),

                Forms\Components\Tabs\Tab::make(__('من نحن'))->schema([
                    Forms\Components\FileUpload::make('intro_image')
                        ->label(__('الصورة'))
                        ->image()->disk('public')->directory('about/intro')->imageEditor()
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('intro_title')->label(__('العنوان'))->required(),
                    Forms\Components\Textarea::make('intro_body')->label(__('النص'))->rows(6)->columnSpanFull(),
                ])->columns(2),

                Forms\Components\Tabs\Tab::make(__('القيم'))->schema([
                    Forms\Components\TextInput::make('values_title')->label(__('عنوان القسم')),
                    Forms\Components\Textarea::make('values_subtitle')->label(__('الوصف الفرعي'))->rows(2)->columnSpanFull(),
                    Forms\Components\Placeholder::make('values_hint')
                        ->content(__('أضف بطاقات القيم من تبويب «القيم» أسفل الصفحة بعد الحفظ.'))
                        ->columnSpanFull(),
                ])->columns(2),

                Forms\Components\Tabs\Tab::make(__('المنصة'))->schema([
                    Forms\Components\FileUpload::make('platform_image')
                        ->label(__('الصورة'))
                        ->image()->disk('public')->directory('about/platform')->imageEditor()
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('platform_title')->label(__('العنوان')),
                    Forms\Components\Textarea::make('platform_description')->label(__('الوصف'))->rows(5)->columnSpanFull(),
                ])->columns(2),

                Forms\Components\Tabs\Tab::make(__('قصة صوت'))->schema([
                    Forms\Components\TextInput::make('story_title')->label(__('عنوان القسم')),
                    Forms\Components\Textarea::make('story_subtitle')->label(__('الوصف الفرعي'))->rows(2)->columnSpanFull(),
                    Forms\Components\Placeholder::make('story_hint')
                        ->content(__('أضف بطاقات القصة من تبويب «بطاقات القصة» أسفل الصفحة بعد الحفظ.'))
                        ->columnSpanFull(),
                ])->columns(2),

                Forms\Components\Tabs\Tab::make(__('بانر الانضمام'))->schema([
                    Forms\Components\FileUpload::make('join_image')
                        ->label(__('صورة الخلفية'))
                        ->image()->disk('public')->directory('about/join')->imageEditor()
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('join_title')->label(__('العنوان')),
                    Forms\Components\Textarea::make('join_description')->label(__('الوصف'))->rows(3)->columnSpanFull(),
                    Forms\Components\TextInput::make('join_button_text')->label(__('نص الزر')),
                    Forms\Components\TextInput::make('join_button_url')->label(__('رابط الزر'))->placeholder('/donate'),
                    Forms\Components\Toggle::make('is_active')->label(__('مفعّلة'))->default(true),
                ])->columns(2),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('hero_title')->label(__('عنوان الهيرو'))->limit(40),
                Tables\Columns\IconColumn::make('is_active')->label(__('مفعّلة'))->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->label(__('آخر تحديث'))->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ValuesRelationManager::class,
            RelationManagers\StoryCardsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAboutPages::route('/'),
            'edit' => Pages\EditAboutPage::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return AboutPage::query()->count() === 0;
    }
}
