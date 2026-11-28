<?php

namespace App\Providers;

use App\Models\Institution;
use Illuminate\Support\Facades\View;
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
        View::composer('layouts.admin', function ($view): void {
            $user = auth()->user();
            $view->with('adminInstitutions', $user?->is_super_admin ? Institution::query()->orderBy('name')->get() : collect());
        });
    }
}
