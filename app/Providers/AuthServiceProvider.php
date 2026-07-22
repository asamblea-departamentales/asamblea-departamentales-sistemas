<?php

namespace App\Providers;

use App\Models\Blog\Category as BlogPostCategory;
use App\Models\Blog\Post as BlogPost;
use App\Policies\ActividadPolicy;
use App\Policies\Blog\CategoryPolicy as BlogPostCategoryPolicy;
use App\Policies\Blog\PostPolicy as BlogPostPolicy;
use App\Policies\CierreMensualPolicy;
use App\Policies\ContratoPolicy;
use App\Policies\ExceptionPolicy;
use App\Policies\RequisicionPolicy;
use App\Policies\TicketPolicy;
use App\Policies\UserPolicy;
use BezhanSalleh\FilamentExceptions\Models\Exception;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\Models\Activity;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Activity::class => ActivityPolicy::class,
        BlogPostCategory::class => BlogPostCategoryPolicy::class,
        BlogPost::class => BlogPostPolicy::class,
        Exception::class => ExceptionPolicy::class,
        'Spatie\Permission\Models\Role' => 'App\Policies\RolePolicy',
        \App\Models\Actividad::class => ActividadPolicy::class,
        \App\Models\Ticket::class => TicketPolicy::class,
        \App\Models\Requisicion::class => RequisicionPolicy::class,
        \App\Models\Contrato::class => ContratoPolicy::class,
        \App\Models\CierreMensual::class => CierreMensualPolicy::class,
        \App\Models\User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                return true;
            }
        });
    }
}
