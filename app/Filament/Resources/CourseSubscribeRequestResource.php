<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\ManagesCourseSubscribeRequestResource;
use App\Filament\Resources\CourseSubscribeRequestResource\Pages;
use App\Models\CourseSubscribeRequest;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Table;

/**
 * Admin inbox for guest course "Subscribe now" applications (separate from join/waitlist).
 */
class CourseSubscribeRequestResource extends Resource
{
    use ManagesCourseSubscribeRequestResource;

    protected static ?string $model = CourseSubscribeRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $recordRouteKeyName = 'uuid';

    protected static ?string $slug = 'course-subscribe-requests';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('Courses');
    }

    public static function getNavigationLabel(): string
    {
        return __('طلبات الاشتراك');
    }

    public static function getModelLabel(): string
    {
        return __('طلب اشتراك كورس');
    }

    public static function getPluralModelLabel(): string
    {
        return __('طلبات الاشتراك');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = CourseSubscribeRequest::query()->pending()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return static::subscribeRequestForm($form);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return static::subscribeRequestInfolist($infolist);
    }

    public static function table(Table $table): Table
    {
        return static::subscribeRequestTable($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['course', 'reviewer']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourseSubscribeRequests::route('/'),
            'view' => Pages\ViewCourseSubscribeRequest::route('/{record}'),
            'edit' => Pages\EditCourseSubscribeRequest::route('/{record}/edit'),
        ];
    }
}
