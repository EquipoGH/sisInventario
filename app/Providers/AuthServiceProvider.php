<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
// use Illuminate\Support\Facades\Gate;

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
        $this->registerPolicies();

        // Gate genérico: @can('permiso','Bienes') o Gate::authorize('permiso','Bienes')
        // Gate::define('permiso', function ($user, string $permisoNombre) {
        //     return $user->tienePermiso($permisoNombre);
        // });
    }
}
