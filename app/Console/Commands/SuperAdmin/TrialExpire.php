<?php

namespace App\Console\Commands\SuperAdmin;

use App\Models\Company;
use App\Models\SuperAdmin\Package;
use App\Models\SuperAdmin\PackageSetting;
use App\Notifications\SuperAdmin\TrialLicenseExp;
use App\Notifications\SuperAdmin\TrialLicenseExpPre;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TrialExpire extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trial-expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set trial expire status of companies in companies table.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $trialPackage = Package::where('default', 'trial')->first();
        $defaultPackage = Package::where('default', 'yes')->first();
        $setting = PackageSetting::first();

        if ($defaultPackage->annual_status) {
            $expireDate = now()->addYear()->format('Y-m-d');
        }
        else {
            $expireDate = now()->addMonth()->format('Y-m-d');
        }

        $companiesOnTrial = Company::with('package')
            ->where('status', 'active')
            ->whereNotNull('licence_expire_on')
            ->where('licence_expire_on', Carbon::now()->addDays($setting->notification_before)->format('Y-m-d'))
            ->whereHas('package', function ($query) use ($trialPackage) {
                $query->where('default', 'trial')->where('id', $trialPackage->id);
            })->get();

        foreach ($companiesOnTrial as $cmp) {
            $companyUser = Company::firstActiveAdmin($cmp);
            $companyUser->notify(new TrialLicenseExpPre($cmp));
        }

        $companiesNotify = Company::with('package')
            ->where('status', 'active')
            ->whereNotNull('licence_expire_on')
            ->whereDate('licence_expire_on', Carbon::now()->format('Y-m-d'))
            ->whereHas('package', function ($query) use ($trialPackage) {
                $query->where('default', 'trial')->where('id', $trialPackage->id);
            })->get();

        foreach ($companiesNotify as $cmp) {
            $companyUser = Company::firstActiveAdmin($cmp);

            $cmp->package_id = $defaultPackage->id;
            $cmp->licence_expire_on = $expireDate;
            $cmp->status = 'license_expired';
            $cmp->save();

            $companyUser->notify(new TrialLicenseExp($cmp));
        }
    }

}
