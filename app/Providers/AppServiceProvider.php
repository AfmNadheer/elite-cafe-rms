<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Page numbers (1, 2, 3 ...) styled with Bootstrap 5
        Paginator::useBootstrapFive();
    }
}
