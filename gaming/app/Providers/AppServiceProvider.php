<?php

namespace App\Providers;

use App\Domains\Card\Repositories\CardRepository;
use App\Domains\Card\Repositories\CardRepositoryInterface;
use App\Domains\Score\Repositories\ScoreRepositoryInterface;
use App\Domains\Score\Repositories\ScoreRepositry;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CardRepositoryInterface::class, CardRepository::class);
        $this->app->bind(ScoreRepositoryInterface::class, ScoreRepositry::class);
    }

    public function boot(): void
    {
        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }
}
