<?php

namespace Database\Seeders;

use App\Models\EmployeeDetails;
use App\Models\Role;
use App\Models\User;
use App\Models\UserAuth;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TFMSUserSeeder extends Seeder
{
    public function run()
    {
        $company = \App\Models\Company::first();
        if (!$company) {
            return;
        }
        $companyId = $company->id;

        // 1. CLEANUP PHASE
        // =====================================================================
        
        // Remove all tasks and their associations
        DB::table('task_users')->truncate();
        DB::table('task_history')->truncate();
        DB::table('tasks')->delete();
        
        // Remove all projects
        DB::table('project_members')->truncate();
        DB::table('projects')->delete();

        // Identify roles to purge users from
        $purgeRoles = ['employee', 'client'];
        $purgeUserIds = DB::table('role_user')
            ->join('roles', 'role_user.role_id', '=', 'roles.id')
            ->whereIn('roles.name', $purgeRoles)
            ->pluck('user_id')
            ->toArray();

        // Also purge any existing TFMS users (and admin@example.com if we're recreating it)
        $tfmsUserIds = User::where('email', 'like', '%@tfms.edu')
            ->orWhere('email', 'admin@example.com')
            ->pluck('id')
            ->toArray();
        $allIdsToPurge = array_unique(array_merge($purgeUserIds, $tfmsUserIds));

        // Delete users (cascading normally handles details, but we'll be thorough)
        if (!empty($allIdsToPurge)) {
            EmployeeDetails::whereIn('user_id', $allIdsToPurge)->delete();
            DB::table('role_user')->whereIn('user_id', $allIdsToPurge)->delete();
            User::whereIn('id', $allIdsToPurge)->delete();
        }

        // 2. SEEDING PHASE
        // =====================================================================

        // Role retrieval
        $adminMainRole = Role::where('name', 'admin')->where('company_id', $companyId)->first();
        $adminTfmsRole = Role::where('name', 'admin-tfms')->where('company_id', $companyId)->first();
        $psmRole = Role::where('name', 'psm-tfms')->where('company_id', $companyId)->first();
        $lecturerRole = Role::where('name', 'lecturer-tfms')->where('company_id', $companyId)->first();
        // We still use 'employee' role as a base for many system permissions in Worksuite
        $employeeRole = Role::where('name', 'employee')->where('company_id', $companyId)->first();

        // a. Main Admin
        $this->createUser('Admin Example', 'admin@example.com', $companyId, $adminMainRole, null);

        // b. TFMS Admin 
        $admin = $this->createUser('Admin TFMS', 'admin@tfms.edu', $companyId, $adminTfmsRole, $employeeRole);

        // c. PSM
        $psm = $this->createUser('PSM Coordinator', 'psm@tfms.edu', $companyId, $psmRole, $employeeRole);

        // c. Lecturers
        $overload = $this->createUser('Dr. Overload', 'overload@tfms.edu', $companyId, $lecturerRole, $employeeRole);
        $balanced = $this->createUser('Dr. Balanced', 'balanced@tfms.edu', $companyId, $lecturerRole, $employeeRole);
        $underload = $this->createUser('Dr. Underload', 'underload@tfms.edu', $companyId, $lecturerRole, $employeeRole);
        $pending = $this->createUser('Dr. Pending', 'pending@tfms.edu', $companyId, $lecturerRole, $employeeRole);
        $mixed = $this->createUser('Dr. Mixed', 'mixed@tfms.edu', $companyId, $lecturerRole, $employeeRole);

        // d. Shared Project
        $project = new Project();
        $project->project_name = 'TFMS Main Research Project';
        $project->company_id = $companyId;
        $project->start_date = now()->subMonth();
        $project->deadline = now()->addMonths(6);
        $project->status = 'in progress';
        $project->save();

        // Add all to project
        $userIds = [$overload->id, $balanced->id, $underload->id, $pending->id, $mixed->id];
        foreach ($userIds as $uid) {
            DB::table('project_members')->insert(['user_id' => $uid, 'project_id' => $project->id]);
        }

        // 3. SCENARIO TASKS (Scale 1-10)
        // =====================================================================

        // SCENARIO 1: Overload (60 points total)
        for ($i = 1; $i <= 6; $i++) {
            $this->createTask($project, "Overload Task $i", 10, 'approved', [$overload->id]);
        }

        // SCENARIO 2: Balanced (30 points total)
        for ($i = 1; $i <= 3; $i++) {
            $this->createTask($project, "Balanced Task $i", 10, 'approved', [$balanced->id]);
        }

        // SCENARIO 3: Underload (8 points total)
        for ($i = 1; $i <= 2; $i++) {
            $this->createTask($project, "Underload Task $i", 4, 'approved', [$underload->id]);
        }

        // SCENARIO 4: Pending (No approved points)
        $this->createTask($project, "Future Research Plan", 10, 'pending', [$pending->id]);

        // SCENARIO 5: Mixed (Some approved, some pending)
        $this->createTask($project, "Mixed Approved Task", 5, 'approved', [$mixed->id]);
        $this->createTask($project, "Mixed Pending Task", 5, 'pending', [$mixed->id]);
    }

    private function createTask($project, $heading, $weightage, $status, $userIds)
    {
        $task = new Task();
        $task->company_id = $project->company_id;
        $task->project_id = $project->id;
        $task->heading = $heading;
        $task->description = 'TFMS Task Description';
        $task->start_date = now();
        $task->due_date = now()->addDays(14);
        $task->status = 'incomplete';
        $task->weightage = $weightage;
        $task->approval_status = $status;
        $task->final_weightage = count($userIds) > 0 ? ($weightage / count($userIds)) : 0;
        $task->save();

        foreach ($userIds as $userId) {
            TaskUser::create([
                'user_id' => $userId,
                'task_id' => $task->id
            ]);
        }
    }

    private function createUser($name, $email, $companyId, $customRole, $employeeRole)
    {
        $userAuth = UserAuth::where('email', $email)->first();
        if (!$userAuth) {
            $userAuth = UserAuth::create([
                'email' => $email,
                'password' => bcrypt('pass1234')
            ]);
        }

        $user = new User();
        $user->company_id = $companyId;
        $user->name = $name;
        $user->email = $email;
        $user->user_auth_id = $userAuth->id;
        $user->save();

        // Attach Roles
        $roles = [$customRole->id];
        if ($employeeRole) {
            $roles[] = $employeeRole->id;
        }
        $user->roles()->sync($roles);

        // Sync permissions
        $user->assignUserRolePermission($customRole->id);

        // Employee Details
        $employeeDetail = new EmployeeDetails();
        $employeeDetail->user_id = $user->id;
        $employeeDetail->company_id = $companyId;
        $employeeDetail->employee_id = 'TFMS-' . rand(1000, 9999);
        $employeeDetail->joining_date = now();
        $employeeDetail->save();

        return $user;
    }
}
