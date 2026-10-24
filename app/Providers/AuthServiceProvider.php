<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Hanya admin yang bisa kelola user & master data
        Gate::define('manage-users', fn ($user) => $user->isAdmin());
        Gate::define('manage-master-data', fn ($user) => $user->isAdmin());

        // Admin & staff bisa buat transaksi
        Gate::define('manage-sales', fn ($user) => true);
        Gate::define('manage-purchases', fn ($user) => true);

        // Hanya admin yang bisa batalkan transaksi & konfirmasi purchase
        Gate::define('cancel-transaction', fn ($user) => $user->isAdmin());

        // Hanya admin yang bisa lihat laporan penuh
        Gate::define('view-reports', fn ($user) => $user->isAdmin());
    }
}
