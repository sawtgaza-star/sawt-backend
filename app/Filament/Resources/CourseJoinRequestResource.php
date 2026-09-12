<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Concerns\ManagesCourseJoinRequestResource;
use App\Filament\Resources\CourseJoinRequestResource\Pages;
use App\Models\CourseJoinRequest;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Table;

/**
 * Admin list of course enroll / waitlist requests submitted by website users.
 */
class CourseJoinRequestResource extends Resource
{
    use ManagesCourseJoinRequestResource;

    protected static ?string $model = CourseJoinRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $recordRouteKeyName = 'uuid';

    protected static ?string $slug = 'course-join-requests';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('Courses');
    }

    public static function getNavigationLabel(): string
    {
        return __('طلبات الانضمام');
    }

    public static function getModelLabel(): string
    {
        return __('طلب انضمام كورس');
    }

    public static function getPluralModelLabel(): string
    {
        return __('طلبات الانضمام');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = CourseJoinRequest::query()->pending()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return static::joinRequestForm($form);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return static::joinRequestInfolist($infolist);
    }

    public static function table(Table $table): Table
    {
        return static::joinRequestTable($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['course', 'user', 'reviewer']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourseJoinRequests::route('/'),
            'view' => Pages\ViewCourseJoinRequest::route('/{record}'),
            'edit' => Pages\EditCourseJoinRequest::route('/{record}/edit'),
        ];
    }
}
