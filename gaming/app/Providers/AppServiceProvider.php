<?php

namespace App\Providers;

use App\Repositories\CardRepository;
use App\Repositories\CardRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CardRepositoryInterface::class, CardRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
