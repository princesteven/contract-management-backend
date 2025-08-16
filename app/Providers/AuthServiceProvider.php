<?php

namespace App\Providers;

use App\Models\User;
use App\Models\AuditLog;
use App\Models\Contract;
use App\Models\ContractCounterParty;
use App\Models\BusinessUnit;
use App\Policies\UserPolicy;
use App\Policies\RolePolicy;
use App\Policies\PermissionPolicy;
use App\Policies\AuditLogPolicy;
use App\Policies\ContractPolicy;
use App\Policies\ContractCounterPartyPolicy;
use App\Policies\BusinessUnitPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Role::class => RolePolicy::class,
        Permission::class => PermissionPolicy::class,
        AuditLog::class => AuditLogPolicy::class,
        Contract::class => ContractPolicy::class,
        ContractCounterParty::class => ContractCounterPartyPolicy::class,
        BusinessUnit::class => BusinessUnitPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Additional gates can be defined here if needed
    }
}
