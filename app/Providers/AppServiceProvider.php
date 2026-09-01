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
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\UnableToCheckFileExistence;

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
            $component->deleteUploadedFileUsing(function (FileUpload $component, string $file): void {
                $component->getDisk()->delete($file);
            });

            $component->getUploadedFileUsing(function (FileUpload $component, string $file, string | array | null $storedFileNames): ?array {
                $storage = $component->getDisk();
                $shouldFetchFileInformation = $component->shouldFetchFileInformation();

                if ($shouldFetchFileInformation) {
                    try {
                        if (! $storage->exists($file)) {
                            return null;
                        }
                    } catch (UnableToCheckFileExistence) {
                        return null;
                    }
                }

                return [
                    'name' => ($component->isMultiple() ? ($storedFileNames[$file] ?? null) : $storedFileNames) ?? basename($file),
                    'size' => $shouldFetchFileInformation ? $storage->size($file) : 0,
                    'type' => $shouldFetchFileInformation ? $storage->mimeType($file) : null,
                    'url' => MediaUrl::make($file),
                ];
            });
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
