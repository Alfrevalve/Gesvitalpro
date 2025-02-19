<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Line;
use App\Policies\LinePolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Line::class => LinePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define gates para roles específicos
        Gate::define('admin-access', function ($user) {
            return $user->isAdmin();
        });

        Gate::define('gerente-access', function ($user) {
            return $user->isGerente();
        });

        Gate::define('jefe-linea-access', function ($user) {
            return $user->isJefeLinea();
        });

        Gate::define('instrumentista-access', function ($user) {
            return $user->isInstrumentista();
        });

        Gate::define('vendedor-access', function ($user) {
            return $user->isVendedor();
        });

        // Gates para acciones específicas
        Gate::define('manage-lines', function ($user) {
            return $user->isAdmin() || $user->isGerente();
        });

        Gate::define('view-dashboard', function ($user) {
            return $user->isAdmin() || $user->isGerente() || $user->isJefeLinea();
        });

        Gate::define('manage-equipment', function ($user) {
            return $user->isAdmin() || $user->isGerente() || $user->isJefeLinea();
        });

        Gate::define('manage-surgeries', function ($user) {
            return $user->isAdmin() || $user->isGerente() || $user->isJefeLinea() || $user->isInstrumentista();
        });

        Gate::define('manage-sales', function ($user) {
            return $user->isAdmin() || $user->isGerente() || $user->isVendedor();
        });
    }
}
