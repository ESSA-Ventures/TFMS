<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\EmailNotificationSetting;
use App\Models\EmployeeDetails;
use App\Models\ModuleSetting;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SmtpSetting;
use App\Models\User;
use App\Models\UserAuth;
use App\Models\UserPermission;
use App\Scopes\CompanyScope;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ManualTesterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Mail Setup
        $smtp = SmtpSetting::first();
        if (!$smtp) {
            $smtp = new SmtpSetting();
        }
        $smtp->mail_driver = 'smtp';
        $smtp->mail_host = 'smtp.gmail.com';
        $smtp->mail_port = '465'; // Using 465 for SSL as commonly used with Gmail
        $smtp->mail_username = 'tahi.unta55@gmail.com';
        $smtp->mail_password = 'qbni ojow ixqo zhhh';
        $smtp->mail_from_name = 'ESSA Ventures';
        $smtp->mail_from_email = 'tahi.unta55@gmail.com';
        $smtp->mail_encryption = 'ssl';
        $smtp->verified = 1;
        $smtp->save();

        // 2. Roles
        $company = Company::first();
        if (!$company) {
            return;
        }
        $companyId = $company->id;

        $rolesToCreate = [
            'admin' => 'Admin',
            'admin-tfms' => 'Admin TFMS',
            'psm-tfms' => 'PSM TFMS',
            'lecturer-tfms' => 'Lecturer TFMS',
        ];

        foreach ($rolesToCreate as $name => $displayName) {
            Role::updateOrCreate(
                ['name' => $name, 'company_id' => $companyId],
                ['display_name' => $displayName]
            );
        }

        // 3. Users
        $usersToCreate = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'is_superadmin' => true,
                'role' => 'superadmin',
                'company_id' => null
            ],
            [
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'is_superadmin' => false,
                'role' => 'admin',
                'company_id' => $companyId
            ],
            [
                'name' => 'Admin TFMS',
                'email' => 'admin@tfms.edu',
                'is_superadmin' => false,
                'role' => 'admin-tfms',
                'company_id' => $companyId
            ],
        ];

        foreach ($usersToCreate as $u) {
            $userAuth = UserAuth::where('email', $u['email'])->first();
            if (!$userAuth) {
                $userAuth = UserAuth::create([
                    'email' => $u['email'],
                    'password' => Hash::make('123456'), // Default password
                ]);
            } else {
                $userAuth->password = Hash::make('123456');
                $userAuth->save();
            }

            $user = User::withoutGlobalScope(CompanyScope::class)
                ->where('email', $u['email'])
                ->where('company_id', $u['company_id'])
                ->first();
            
            if (!$user) {
                $user = new User();
            }
            
            $user->name = $u['name'];
            $user->email = $u['email'];
            $user->company_id = $u['company_id'];
            $user->is_superadmin = $u['is_superadmin'];
            $user->user_auth_id = $userAuth->id;
            $user->save();

            // Assign Role
            if ($u['is_superadmin']) {
                $role = Role::withoutGlobalScope(CompanyScope::class)
                    ->whereNull('company_id')
                    ->where('name', 'superadmin')
                    ->first();
            } else {
                $role = Role::where('name', $u['role'])
                    ->where('company_id', $companyId)
                    ->first();
            }

            if ($role) {
                $roleIds = [$role->id];
                
                // Add employee role for non-superadmins if it exists
                if (!$u['is_superadmin']) {
                    $employeeRole = Role::where('name', 'employee')->where('company_id', $companyId)->first();
                    if ($employeeRole && $role->id != $employeeRole->id) {
                        $roleIds[] = $employeeRole->id;
                    }
                }

                $user->roles()->sync($roleIds);
                
                if ($u['is_superadmin']) {
                    $this->superadminRolePermissionAttach($user);
                } else {
                    $user->assignUserRolePermission($role->id);
                }
            }

            // Employee Details for non-superadmins
            if (!$u['is_superadmin']) {
                EmployeeDetails::updateOrCreate(
                    ['user_id' => $user->id, 'company_id' => $companyId],
                    [
                        'employee_id' => 'EMP-' . substr(md5($u['email']), 0, 4),
                        'joining_date' => now(),
                    ]
                );
            }
        }

        // 4. Module Settings
        // In user section (employee type) only turn on: message, tasks, users, reports
        $modulesToKeepActive = ['messages', 'tasks', 'employees', 'reports'];
        
        // Deactivate all first for employee type
        ModuleSetting::where('company_id', $companyId)
            ->where('type', 'employee')
            ->update(['status' => 'deactive']);

        // Activate specific ones
        ModuleSetting::where('company_id', $companyId)
            ->where('type', 'employee')
            ->whereIn('module_name', $modulesToKeepActive)
            ->update(['status' => 'active']);

        // 5. Email Notification Settings
        // Enable all email notifications
        EmailNotificationSetting::where('company_id', $companyId)
            ->update(['send_email' => 'yes']);

        $this->command->info('ManualTesterSeeder completed successfully.');
    }

    private function superadminRolePermissionAttach(User $user)
    {
        $permissions = Permission::select('permissions.*')->whereHas('module', function ($query) {
            $query->withoutGlobalScopes()->where('is_superadmin', '1');
        })->get();

        UserPermission::where('user_id', $user->id)->delete();

        $userPermission = [];
        foreach ($permissions as $permission) {
            $userPermission [] = [
                'user_id' => $user->id,
                'permission_id' => $permission->id,
                'permission_type_id' => 4, // 4 is usually 'all'
            ];
        }

        foreach (array_chunk($userPermission, 200) as $userPermissionChunk) {
            UserPermission::insert($userPermissionChunk);
        }
    }
}
