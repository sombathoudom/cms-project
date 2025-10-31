<?php

namespace Database\Seeders;

use App\Domains\Authentication\Models\Permission;
use App\Domains\Authentication\Models\Role;
use App\Domains\Content\Models\KnowledgeBaseArticle;
use App\Domains\Settings\Models\Setting;
use App\Domains\Workflow\Models\Contact;
use App\Domains\Workflow\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            // DO NOT USE IN PRODUCTION - Sample seed data only.
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            $permissions = [
                'content.view',
                'content.create',
                'content.update',
                'content.delete',
                'media.view',
                'media.create',
                'media.update',
                'media.delete',
                'taxonomy.manage',
                'settings.view',
                'settings.update',
                'workflow.view',
                'workflow.update',
                'audit.view',
            ];

            $permissionRecords = collect($permissions)
                ->mapWithKeys(function (string $permission) {
                    $record = Permission::query()->firstOrCreate(
                        ['name' => $permission, 'guard_name' => 'web'],
                        ['id' => (string) Str::uuid()]
                    );

                    return [$permission => $record];
                });

            $roles = [
                'Admin' => $permissions,
                'Agent' => ['content.view', 'content.update', 'media.view', 'workflow.view'],
                'Viewer' => ['content.view'],
            ];

            $roleRecords = collect();

            foreach ($roles as $roleName => $rolePermissions) {
                $role = Role::query()->firstOrCreate(
                    ['name' => $roleName, 'guard_name' => 'web'],
                    ['id' => (string) Str::uuid()]
                );
                $role->syncPermissions($permissionRecords->only($rolePermissions)->values());
                $roleRecords->put($roleName, $role);
            }

            $admin = User::factory()->create([
                'name' => 'Demo Admin',
                'email' => 'admin@example.com',
            ]);
            $admin->assignRole($roleRecords->get('Admin'));

            $agent = User::factory()->create([
                'name' => 'Demo Agent',
                'email' => 'agent@example.com',
            ]);
            $agent->assignRole($roleRecords->get('Agent'));

            $viewer = User::factory()->create([
                'name' => 'Demo Viewer',
                'email' => 'viewer@example.com',
            ]);
            $viewer->assignRole($roleRecords->get('Viewer'));

            Setting::factory()->create([
                'key' => 'site.name',
                'value' => ['value' => 'CMS Demo'],
            ]);

            Ticket::factory(3)->create([
                'assigned_to' => $agent->id,
            ]);

            Contact::factory(3)->create();

            KnowledgeBaseArticle::factory(2)->create([
                'author_id' => $admin->id,
            ]);
        });
    }
}
