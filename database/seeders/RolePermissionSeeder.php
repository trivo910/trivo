<?php

namespace Database\Seeders;

use App\Support\AdminAccess;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permissions = AdminAccess::permissions();
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        foreach (AdminAccess::ROLES as $name) {
            $allowed = match ($name) {
                'Super Admin' => $permissions,
                'Admin' => array_values(array_diff($permissions, ['users.manage'])),
                'Editor' => array_values(array_filter($permissions, fn (string $permission) => $permission === 'dashboard.view' || $permission === 'media.manage'
                    || preg_match('/^(pages|posts|services|portfolio|testimonials|faqs|clients|menus)\./', $permission))),
                'SEO Manager' => array_values(array_filter($permissions, fn (string $permission) => in_array($permission, ['dashboard.view', 'settings.seo', 'redirects.manage'], true)
                    || str_ends_with($permission, '.view') && ! in_array($permission, ['leads.view', 'activity.view'], true)
                    || str_ends_with($permission, '.seo'))),
            };
            Role::findOrCreate($name, 'web')->syncPermissions($allowed);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
