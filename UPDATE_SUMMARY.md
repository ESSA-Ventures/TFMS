# Source Code Update Summary for Login & Security Features

This document outlines the changes made to the source code to implement the **First Login Password Change** and **User Account Locking** features.

## 1. Database Schema
**File:** `database/migrations/2026_01_16_000000_add_monitor_fields_to_user_auths_table.php`  
**Description:** Created a new migration to add the following columns to the `user_auths` table:
*   `is_first_login` (boolean, default: true): Tracks if the user needs to change their password.
*   `login_attempts` (integer, default: 0): Counts failed login attempts.
*   `is_locked` (boolean, default: false): Indicates if the account is locked.

## 2. Authentication Logic
**File:** `app/Actions/Fortify/AttemptToAuthenticate.php`  
**Description:** 
*   Added pre-check: Throws a validation exception if the account is already locked (`is_locked == true`). **(Updated: Excludes Admins/SuperAdmins)**
*   Login Success: Resets `login_attempts` to 0.
*   Login Failure: Increments `login_attempts`. If it reaches 3, sets `is_locked` to true and throws a specific lock message. **(Updated: Excludes Admins/SuperAdmins)**

**File:** `app/Providers/FortifyServiceProvider.php`  
**Description:** 
*   Modified the `LoginResponse` logic.
*   Checks `is_first_login` immediately after authentication. If true **and the user is NOT an Admin/SuperAdmin**, redirects the user to the "Change Password" route.

## 3. First Login Change Password Feature
**File:** `app/Http/Controllers/FirstLoginController.php` (New)  
**Description:** 
*   `index()`: Returns the view for changing the password.
*   `store()`: Validates the new password, updates the user's password hash, sets `is_first_login` to false, and redirects to the home page.

**File:** `resources/views/auth/first_login_change_password.blade.php` (New)  
**Description:** 
*   Frontend form for the user to input and confirm their new password.

**File:** `routes/web.php`  
**Description:** 
*   Added routes `account/first-login/change-password` (GET & POST) mapped to `FirstLoginController`.

## 4. Admin Unlock Feature
**File:** `app/Http/Controllers/EmployeeController.php`  
**Description:** 
*   Added `unlock($id)` method.
*   Finds the user's auth record, resets `is_locked` to false, resets `login_attempts` to 0, and returns a success response.

**File:** `app/DataTables/EmployeesDataTable.php`  
**Description:** 
*   Eager loaded `userAuth` relationship in the query.
*   Updated the `action` column logic: If a user is locked (`is_locked == true`), an **Unlock** button is added to the actions dropdown menu.

**File:** `resources/views/employees/index.blade.php`  
**Description:** 
*   Added JavaScript event listener for the `.unlock-user` class.
*   Triggers an AJAX POST request to the unlock route when clicked and refreshes the table upon success.

**File:** `routes/web.php`  
**Description:** 
*   Added route `account/employees/unlock/{id}` mapped to `EmployeeController@unlock`.
