<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->bind(
            \App\Repositories\UserRepositoryInterface::class,
            \App\Repositories\UserRepository::class
        );
        $this->app->bind(
            \App\Repositories\PesananRepositoryInterface::class,
            \App\Repositories\PesananRepository::class
        );
        $this->app->bind(
            \App\Repositories\KeranjangRepositoryInterface::class,
            \App\Repositories\KeranjangRepository::class
        );
        $this->app->bind(
            \App\Repositories\AlamatPenggunaRepositoryInterface::class,
            \App\Repositories\AlamatPenggunaRepository::class
        );
        $this->app->bind(
            \App\Repositories\NotifikasiRepositoryInterface::class,
            \App\Repositories\NotifikasiRepository::class
        );
        $this->app->bind(
            \App\Repositories\ItemPesananRepositoryInterface::class,
            \App\Repositories\ItemPesananRepository::class
        );
        $this->app->bind(
            \App\Repositories\ItemPesananRepositoryInterface::class,
            \App\Repositories\ItemPesananRepository::class
        );
        $this->app->bind(
            \App\Repositories\ItemPesananRepositoryInterface::class,
            \App\Repositories\ItemPesananRepository::class
        );
        $this->app->bind(
            \App\Repositories\ItemPesananRepositoryInterface::class,
            \App\Repositories\ItemPesananRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        Paginator::useBootstrapFive();

    }
}
