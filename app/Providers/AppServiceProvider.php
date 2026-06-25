<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $source = storage_path('api-docs/api-docs.json');
        $targetDir = public_path('docs');
        $target = $targetDir.'/openapi.json';

        if (file_exists($source)) {
            if (! is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            copy($source, $target);
        }
    }
}
