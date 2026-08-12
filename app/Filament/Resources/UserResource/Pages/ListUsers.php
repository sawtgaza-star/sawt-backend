<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\AdminResource;
use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        if ($this->activeTab === 'admins') {
            return [
                Actions\Action::make('createAdmin')
                    ->label('إضافة مدير')
                    ->icon('heroicon-o-plus')
                    ->url(AdminResource::getUrl('create')),
            ];
        }

        return [
            Actions\CreateAction::make()
                ->label('إضافة مستخدم'),
        ];
    }

    public function getTabs(): array
    {
        $websiteCount = User::query()
            ->whereIn('type', [User::TYPE_USER, User::TYPE_CONTENT_CREATOR])
            ->whereDoesntHave('roles', fn (Builder $q) => $q->whereIn('name', User::FILAMENT_ROLES))
            ->count();

        $adminsCount = User::query()
            ->where(function (Builder $query) {
                $query->where('type', User::TYPE_ADMIN)
                    ->orWhereHas('roles', fn (Builder $q) => $q->whereIn('name', User::FILAMENT_ROLES));
            })
            ->count();

        return [
            'website' => Tab::make('مستخدمو الموقع')
                ->icon('heroicon-o-users')
                ->badge($websiteCount)
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereIn('type', [User::TYPE_USER, User::TYPE_CONTENT_CREATOR])
                    ->whereDoesntHave('roles', fn (Builder $q) => $q->whereIn('name', User::FILAMENT_ROLES))),
            'admins' => Tab::make('المديرون')
                ->icon('heroicon-o-shield-check')
                ->badge($adminsCount)
                ->modifyQueryUsing(fn (Builder $query) => $query->where(function (Builder $q) {
                    $q->where('type', User::TYPE_ADMIN)
                        ->orWhereHas('roles', fn (Builder $roles) => $roles->whereIn('name', User::FILAMENT_ROLES));
                })),
        ];
    }

    public function getDefaultActiveTab(): string
    {
        return 'website';
    }
}
