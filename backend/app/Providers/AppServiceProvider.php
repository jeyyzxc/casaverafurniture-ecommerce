<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Product;
use App\Observers\ProductObserver;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    
    public function register(): void
    {
        
    }

    public function boot(): void
    {
        
        Product::observe(ProductObserver::class);

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
