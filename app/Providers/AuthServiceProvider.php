<?php

namespace App\Providers;

use App\Domains\Authentication\Models\Permission;
use App\Domains\Content\Models\Content;
use App\Domains\Content\Policies\ContentPolicy;
use App\Domains\Media\Models\Media;
use App\Domains\Media\Policies\MediaPolicy;
use App\Domains\Security\Models\AuditLog;
use App\Domains\Security\Policies\AuditPolicy;
use App\Domains\Settings\Models\Setting;
use App\Domains\Settings\Policies\SettingsPolicy;
use App\Domains\Workflow\Models\Workflow;
use App\Domains\Workflow\Policies\WorkflowPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Throwable;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Content::class => ContentPolicy::class,
        Media::class => MediaPolicy::class,
        Setting::class => SettingsPolicy::class,
        Workflow::class => WorkflowPolicy::class,
        AuditLog::class => AuditPolicy::class,
    ];

    public function boot(): void
    {
        Gate::before(function ($user, string $ability) {
            return $user->hasRole('Admin') ? true : null;
        });

        try {
            Permission::query()->each(function (Permission $permission): void {
                Gate::define($permission->name, fn ($user) => $user->can($permission->name));
            });
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
