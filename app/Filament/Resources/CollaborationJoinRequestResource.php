<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CollaborationJoinRequestResource\Pages;
use App\Filament\Resources\Concerns\ManagesCollaborationJoinRequestResource;
use App\Models\CollaborationJoinRequest;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class CollaborationJoinRequestResource extends Resource
{
    use ManagesCollaborationJoinRequestResource;

    protected static ?string $model = CollaborationJoinRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $recordRouteKeyName = 'uuid';

    protected static ?string $slug = 'collaboration-join-requests';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('Collaboration');
    }

    public static function getNavigationLabel(): string
    {
        return __('Collaboration Requests');
    }

    public static function getModelLabel(): string
    {
        return __('Collaboration Request');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Collaboration Requests');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = CollaborationJoinRequest::query()->pending()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return static::collaborationJoinRequestForm($form);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return static::collaborationJoinRequestInfolist($infolist);
    }

    public static function table(Table $table): Table
    {
        return static::collaborationJoinRequestTable($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCollaborationJoinRequests::route('/'),
            'view' => Pages\ViewCollaborationJoinRequest::route('/{record}'),
            'edit' => Pages\EditCollaborationJoinRequest::route('/{record}/edit'),
        ];
    }
}
