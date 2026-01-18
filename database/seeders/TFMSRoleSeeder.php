<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TFMSRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($companyId)
    {
        $roles = [
            'admin-tfms' => 'Admin TFMS',
            'psm-tfms' => 'PSM TFMS',
            'lecturer-tfms' => 'Lecturer TFMS',
        ];

        $allPermissionType = DB::table('permission_types')->where('name', 'all')->first();
        $nonePermissionType = DB::table('permission_types')->where('name', 'none')->first();

        // Admin-TFMS Permissions
        $adminPermissions = [
            'add_employees' => $allPermissionType->id,
            'view_employees' => $allPermissionType->id,
            'edit_employees' => $allPermissionType->id,
            'delete_employees' => $allPermissionType->id,
            'add_projects' => $allPermissionType->id,
            'view_projects' => $allPermissionType->id,
            'edit_projects' => $allPermissionType->id,
            'delete_projects' => $allPermissionType->id,
            'add_tasks' => $allPermissionType->id,
            'view_tasks' => $allPermissionType->id,
            'edit_tasks' => $allPermissionType->id,
            'delete_tasks' => $allPermissionType->id,
            // Basic dashboard and settings access might be needed for sidebar visibility
            'view_overview_dashboard' => $allPermissionType->id,
            'view_project_dashboard' => $allPermissionType->id,
        ];

        // PSM-TFMS Permissions
        $psmPermissions = [
            'view_projects' => $allPermissionType->id,
            'edit_projects' => $allPermissionType->id,
            'view_tasks' => $allPermissionType->id,
            'edit_tasks' => $allPermissionType->id,
            'view_employees' => $allPermissionType->id,
            'view_overview_dashboard' => $allPermissionType->id,
            'view_project_dashboard' => $allPermissionType->id,
        ];

        // Lecturer-TFMS Permissions
        $lecturerPermissions = [
            'view_projects' => $allPermissionType->id,
            'view_tasks' => $allPermissionType->id,
            'view_overview_dashboard' => $allPermissionType->id,
            'view_project_dashboard' => $allPermissionType->id,
        ];

        $rolePermissionMap = [
            'admin-tfms' => $adminPermissions,
            'psm-tfms' => $psmPermissions,
            'lecturer-tfms' => $lecturerPermissions,
        ];

        foreach ($roles as $name => $displayName) {
            $role = Role::where('name', $name)
                ->where('company_id', $companyId)
                ->first();

            if (!$role) {
                $role = new Role();
                $role->name = $name;
                $role->company_id = $companyId;
            }

            $role->display_name = $displayName;
            $role->save();

            // Clear existing permissions for this role
            \App\Models\PermissionRole::where('role_id', $role->id)->delete();
            
            $data = [];
            $mappedPermissions = $rolePermissionMap[$name] ?? [];
            
            $dbPermissions = Permission::whereIn('name', array_keys($mappedPermissions))->get();

            foreach ($dbPermissions as $permission) {
                $data[] = [
                    'permission_id' => $permission->id,
                    'role_id' => $role->id,
                    'permission_type_id' => $mappedPermissions[$permission->name],
                ];
            }

            foreach (array_chunk($data, 100) as $item) {
                \App\Models\PermissionRole::insert($item);
            }
        }
    }
}
