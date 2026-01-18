<?php

namespace Database\Seeders;

use App\Models\Designation;
use App\Models\LeaveType;
use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($companyId)
    {

        $departments = [
            ['team_name' => 'Faculty of Computing', 'company_id' => $companyId],
            ['team_name' => 'Faculty of Engineering', 'company_id' => $companyId],
            ['team_name' => 'Faculty of Science', 'company_id' => $companyId],
            ['team_name' => 'Faculty of Management', 'company_id' => $companyId],
            ['team_name' => 'Centre for Academic Studies', 'company_id' => $companyId],
        ];

        $designations = [
            ['name' => 'Professor', 'company_id' => $companyId],
            ['name' => 'Associate Professor', 'company_id' => $companyId],
            ['name' => 'Senior Lecturer', 'company_id' => $companyId],
            ['name' => 'Lecturer', 'company_id' => $companyId],
            ['name' => 'Tutor', 'company_id' => $companyId],
        ];

        Team::insert($departments);
        Designation::insert($designations);

        $teams = Team::where('company_id', $companyId)->pluck('id')->toArray();
        $designations = Designation::where('company_id', $companyId)->pluck('id')->toArray();

        LeaveType::where('company_id', $companyId)->update([
            'department' => json_encode($teams),
            'designation' => json_encode($designations),
        ]);

    }

}
