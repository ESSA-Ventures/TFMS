<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserAuth;

// 1. Cleanup: Unlock admin if it was locked by mistake
$adminAuth = UserAuth::where('email', 'admin@example.com')->first();
if ($adminAuth) {
    $adminAuth->is_locked = false;
    $adminAuth->login_attempts = 0;
    $adminAuth->save();
}

// 2. Find a PURE Employee to lock (Not an admin, not superadmin)
$employeeAuth = UserAuth::whereHas('user', function($q) {
    $q->whereHas('roles', function($r) {
        $r->where('name', 'employee');
    });
    $q->whereDoesntHave('roles', function($r) {
        $r->where('name', 'admin');
    });
    $q->where('is_superadmin', 0);
})->first();

if ($employeeAuth) {
    $employeeAuth->login_attempts = 3;
    $employeeAuth->is_locked = true;
    $employeeAuth->save();
    echo "\n[SUCCESS] Locked Account:\n";
    echo " - Email: " . $employeeAuth->email . "\n";
    echo " - Role: Employee (Non-Admin)\n";
    echo " - Status: LOCKED (Set to 3 attempts)\n";

    // 3. Find an Admin for the user to login with
    $adminAuthForDisplay = UserAuth::whereHas('user', function($q) {
        $q->whereHas('roles', function($r) {
            $r->where('name', 'admin');
        });
    })->first();

    if ($adminAuthForDisplay) {
        echo "\n[INFO] Admin Account (Use this to login and unlock):\n";
        echo " - Email: " . $adminAuthForDisplay->email . "\n";
    }

} else {
    echo "\n[ERROR] No non-admin employee account found to lock.\n";
    echo "Please create a new employee in the system first, then run this again.\n";
}

echo "\n";

