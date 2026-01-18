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
    public function run($companyId)
    {
        $faker = \Faker\Factory::create();

        // Roles
        $adminRole = Role::where('name', 'admin-tfms')->where('company_id', $companyId)->first();
        $psmRole = Role::where('name', 'psm-tfms')->where('company_id', $companyId)->first();
        $lecturerRole = Role::where('name', 'lecturer-tfms')->where('company_id', $companyId)->first();
        $employeeRole = Role::where('name', 'employee')->where('company_id', $companyId)->first();

        // 1. Admin
        $this->createUser('Admin TFMS', 'admin@tfms.edu', $companyId, $adminRole, $employeeRole);

        // 2. PSM
        $this->createUser('PSM Coordinator', 'psm@tfms.edu', $companyId, $psmRole, $employeeRole);

        // 3. Lecturers
        $lecturers = [];
        $lecturerEmails = ['lecturer@tfms.edu', 'lecturer2@tfms.edu', 'lecturer3@tfms.edu', 'lecturer4@tfms.edu', 'lecturer5@tfms.edu'];
        
        foreach ($lecturerEmails as $email) {
            $name = $faker->name;
            $lecturers[] = $this->createUser($name, $email, $companyId, $lecturerRole, $employeeRole);
        }

        // Add Projects and Tasks assigned to lecturers
        foreach ($lecturers as $index => $lecturer) {
            // Create a Project
            $project = new Project();
            $project->project_name = 'TFMS Research Project ' . ($index + 1);
            $project->company_id = $companyId;
            $project->client_id = null;
            $project->start_date = now()->subMonth();
            $project->deadline = now()->addMonths(3);
            $project->status = 'in progress';
            $project->save();

            // Assign lecturer to project
            DB::table('project_members')->insert([
                'user_id' => $lecturer->id,
                'project_id' => $project->id,
            ]);

            // Create Tasks for the project
            for ($i = 1; $i <= 3; $i++) {
                $task = new Task();
                $task->company_id = $companyId;
                $task->project_id = $project->id;
                $task->heading = 'Lecturer Task ' . $i . ' for ' . $project->project_name;
                $task->description = 'Detailed description for task ' . $i;
                $task->start_date = now();
                $task->due_date = now()->addDays(10);
                $task->status = 'incomplete';
                $task->save();

                // Assign task to lecturer
                TaskUser::create([
                    'user_id' => $lecturer->id,
                    'task_id' => $task->id
                ]);
            }
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

        $user = User::where('email', $email)->where('company_id', $companyId)->first();
        if (!$user) {
            $user = new User();
            $user->company_id = $companyId;
            $user->name = $name;
            $user->email = $email;
            $user->user_auth_id = $userAuth->id;
            $user->save();
        }

        // Attach Roles
        $user->roles()->syncWithoutDetaching([$customRole->id, $employeeRole->id]);

        // Sync Granular Permissions
        $user->assignUserRolePermission($customRole->id);

        // Employee Details
        $employeeDetail = EmployeeDetails::where('user_id', $user->id)->first();
        if (!$employeeDetail) {
            $employeeDetail = new EmployeeDetails();
            $employeeDetail->user_id = $user->id;
            $employeeDetail->company_id = $companyId;
            $employeeDetail->employee_id = 'EMP-' . rand(1000, 9999);
            $employeeDetail->joining_date = now();
            $employeeDetail->save();
        }

        return $user;
    }
}
