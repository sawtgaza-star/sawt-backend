<?php

namespace App\Providers;

use App\Repositories\ContentPageRepository;
use App\Repositories\CreatorPageRepository;
use App\Repositories\AboutRepository;
use App\Repositories\Contracts\ContentPageRepositoryInterface;
use App\Repositories\Contracts\CreatorPageRepositoryInterface;
use App\Repositories\Contracts\AboutRepositoryInterface;
use App\Repositories\Contracts\SettingRepositoryInterface;
use App\Repositories\Contracts\TeamRepositoryInterface;
use App\Repositories\Contracts\SupportRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\SettingRepository;
use App\Repositories\SupportRepository;
use App\Repositories\TeamRepository;
use App\Repositories\UserRepository;
use App\Support\MediaUrl;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Filament\Forms\Components\FileUpload;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CreatorPageRepositoryInterface::class, CreatorPageRepository::class);
        $this->app->bind(ContentPageRepositoryInterface::class, ContentPageRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(TeamRepositoryInterface::class, TeamRepository::class);
        $this->app->bind(SettingRepositoryInterface::class, SettingRepository::class);
        $this->app->bind(AboutRepositoryInterface::class, AboutRepository::class);
        $this->app->bind(SupportRepositoryInterface::class, SupportRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FileUpload::configureUsing(function (FileUpload $component): void {
            $component->getUploadedFileUrlUsing(
                fn (?string $file): ?string => MediaUrl::make($file)
            );
        });

        // Filament Shield: super_admin sees the full sidebar (same as local).
        Gate::before(function ($user, string $ability) {
            if (method_exists($user, 'hasRole') && $user->hasRole('super_admin')) {
                return true;
            }

            return null;
        });
    }
}
