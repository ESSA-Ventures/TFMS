<?php

namespace App\Providers;

use Str;
use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Company;
use App\Models\UserAuth;
use App\Scopes\ActiveScope;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use App\Models\GlobalSetting;
use Laravel\Fortify\Features;
use App\Models\LanguageSetting;
use App\Models\SocialAuthSetting;
use Froiden\Envato\Traits\AppBoot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Models\SuperAdmin\FrontMenu;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\SuperAdmin\FooterMenu;
use App\Actions\Fortify\CreateNewUser;
use App\Models\SuperAdmin\FrontDetail;
use App\Models\SuperAdmin\FrontWidget;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\ServiceProvider;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use Laravel\Fortify\Contracts\LoginResponse;
use App\Actions\Fortify\AttemptToAuthenticate;
use Illuminate\Validation\ValidationException;
use App\Actions\Fortify\RedirectIfTwoFactorConfirmed;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse;
use Modules\Subdomain\Http\Middleware\SubdomainCheck;
use Laravel\Fortify\Actions\EnsureLoginIsNotThrottled;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;

class FortifyServiceProvider extends ServiceProvider
{

    use AppBoot;

    public function isLegal()
    {
        return true;
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    // WORKSUITESAAS
    public function register()
    {

        $this->app->instance(LoginResponse::class, new class implements LoginResponse {

            public function toResponse($request)
            {
                $userAuth = auth()->user();
                $user = $userAuth->user;

                if ($userAuth && $userAuth->is_first_login) {
                     if (!$user->is_superadmin && !$user->hasRole('admin')) {
                         return redirect()->route('first-login.index');
                     }
                }

                session(['user' => User::find(user()->id)]);

                if (auth()->user() && auth()->user()->user->is_superadmin) {
                    return redirect(RouteServiceProvider::SUPER_ADMIN_HOME);
                }

                $emailCountInCompanies = DB::table('users')->where('email', user()->email)->count();
                session()->forget('user_company_count');

                if ($emailCountInCompanies > 1) {
                    if (module_enabled('Subdomain')) {
                        UserAuth::multipleUserLoginSubdomain();
                    }
                    else {
                        session(['user_company_count' => $emailCountInCompanies]);

                        return redirect(route('superadmin.superadmin.workspaces'));
                    }

                }

                return redirect(session()->has('url.intended') ? session()->get('url.intended') : RouteServiceProvider::HOME);
            }

        });

        $this->app->instance(TwoFactorLoginResponse::class, new class implements TwoFactorLoginResponse {

            public function toResponse($request)
            {
                session(['user' => User::find(user()->id)]);

                if (auth()->user() && auth()->user()->user->is_superadmin) {
                    return redirect(RouteServiceProvider::SUPER_ADMIN_HOME);
                }

                $emailCountInCompanies = DB::table('users')->where('email', user()->email)->count();
                session(['user_company_count' => $emailCountInCompanies]);

                if ($emailCountInCompanies > 1) {
                    return redirect(route('superadmin.superadmin.workspaces'));
                }

                return redirect(session()->has('url.intended') ? session()->get('url.intended') : RouteServiceProvider::HOME);
            }

        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Fortify::authenticateThrough(function (Request $request) {

            return array_filter([
                config('fortify.limiters.login') ? null : EnsureLoginIsNotThrottled::class,
                Features::enabled(Features::twoFactorAuthentication()) ? RedirectIfTwoFactorConfirmed::class : null,
                AttemptToAuthenticate::class,
                PrepareAuthenticatedSession::class,
            ]);
        });
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // Fortify::authenticateThrough();
        Fortify::authenticateUsing(function (Request $request) {
            $rules = [
                'email' => 'required|email:rfc|regex:/(.+)@(.+)\.(.+)/i'
            ];

            $request->validate($rules);

            $userAuth = UserAuth::where('email', $request->email)->first();

            if ($userAuth) {
                $isAdmin = DB::table('role_user')
                    ->join('users', 'users.id', '=', 'role_user.user_id')
                    ->where('users.user_auth_id', $userAuth->id)
                    ->where('role_user.role_id', 1)
                    ->exists();

                if (!$isAdmin && $userAuth->is_locked) {
                    throw ValidationException::withMessages([
                        Fortify::username() => __('Your account has been locked due to multiple failed login attempts. Please contact the administrator.'),
                    ]);
                }

                if (Hash::check($request->password, $userAuth->password)) {
                    $userAuth->login_attempts = 0;
                    $userAuth->save();

                    // Added for validation of account login in company
                    UserAuth::validateLoginActiveDisabled($userAuth);

                    return $userAuth;
                } else {
                    if (!$isAdmin && preg_match('/^(?=.*[0-9])(?=.*[a-zA-Z])[a-zA-Z0-9]{8,16}$/', $request->password)) {
                        $userAuth->login_attempts = $userAuth->login_attempts + 1;
                        if ($userAuth->login_attempts >= 3) {
                            $userAuth->is_locked = true;
                        }
                        $userAuth->save();
                    }
                }
            }
        });


        Fortify::requestPasswordResetLinkView(function () {
            $globalSetting = GlobalSetting::first();
            if ($globalSetting) {
                App::setLocale($globalSetting->locale);
                Carbon::setLocale($globalSetting->locale);
                setlocale(LC_TIME, $globalSetting->locale . '_' . mb_strtoupper($globalSetting->locale));
            }
            $frontWidgets = FrontWidget::all();

            return view('auth.passwords.forget', [
                'globalSetting' => $globalSetting,
                'frontWidgets' => $frontWidgets,
            ]);
        });

        Fortify::loginView(function () {

            $this->showInstall();

            $this->checkMigrateStatus();
            $globalSetting = GlobalSetting::first();
            // Is worksuite
            $company = Company::first();


            if (!$this->isLegal()) {

                if (!module_enabled('Subdomain')){
                    return redirect('verify-purchase');
                }

                // We will only show verify page for super-admin-login
                // We will check it's opened on main or not
                if (Str::contains(request()->url(), 'super-admin-login')) {
                    return redirect('verify-purchase');
                }
            }

            if ($globalSetting) {
                App::setLocale($globalSetting->locale);
                Carbon::setLocale($globalSetting->locale);
                setlocale(LC_TIME, $globalSetting->locale . '_' . mb_strtoupper($globalSetting->locale));
            }

            $userTotal = User::count();

            if ($userTotal == 0) {
                $accountSetupBlade = 'auth.account_setup';
                // WORKSUITESAAS
                if (isWorksuiteSaas()){
                    $accountSetupBlade = 'super-admin.account_setup';
                }

                return view($accountSetupBlade, ['global' => $globalSetting, 'setting' => $globalSetting]);
            }

            $socialAuthSettings = SocialAuthSetting::first();

            $languages = language_setting();
            $frontWidgets = FrontWidget::all();

            if ($globalSetting && $globalSetting->front_design == 1 && $globalSetting->login_ui == 1 && !module_enabled('Subdomain')) {
                $localeLanguage = LanguageSetting::where('language_code', App::getLocale())->first();

                $frontMenuCount = FrontMenu::select('id', 'language_setting_id')->where('language_setting_id', $localeLanguage?->id)->count();
                $frontDetail = FrontDetail::first();
                $frontMenu = FrontMenu::where('language_setting_id', $frontMenuCount > 0 ? ($localeLanguage?->id) : null)->first();
                $footerMenuCount = FooterMenu::select('id', 'language_setting_id')->where('language_setting_id', $localeLanguage?->id)->count();
                $footerSettings = FooterMenu::whereNotNull('slug')->where('language_setting_id', $footerMenuCount > 0 ? ($localeLanguage?->id) : null)->get();

                if (session()->has('language')) {
                    $locale = session('language');
                }
                else {
                    $locale = $frontDetail->locale;
                }



                return view('super-admin.saas.login',
                    [
                        'setting' => $globalSetting,
                        'socialAuthSettings' => $socialAuthSettings,
                        'company' => $company,
                        'global' => $globalSetting,
                        'frontMenu' => $frontMenu,
                        'footerSettings' => $footerSettings,
                        'locale' => $locale,
                        'frontDetail' => $frontDetail,
                        'languages' => $languages,
                        'frontWidgets' => $frontWidgets,
                    ]
                );
            }

            return view('auth.login', [
                'globalSetting' => $globalSetting,
                'socialAuthSettings' => $socialAuthSettings,
                'company' => $company,
                'languages' => $languages,
                'frontWidgets' => $frontWidgets,
            ]);

        });

        Fortify::resetPasswordView(function ($request) {
            $globalSetting = GlobalSetting::first();
            if ($globalSetting) {
                App::setLocale($globalSetting->locale);
                Carbon::setLocale($globalSetting->locale);
                setlocale(LC_TIME, $globalSetting->locale . '_' . mb_strtoupper($globalSetting->locale));
            }
            $frontWidgets = FrontWidget::all();
            return view('auth.passwords.reset-password', [
                'request' => $request,
                'globalSetting' => $globalSetting,
                'frontWidgets' => $frontWidgets,
            ]);
        });

        Fortify::confirmPasswordView(function ($request) {
            $globalSetting = GlobalSetting::first();
            App::setLocale($globalSetting->locale);
            Carbon::setLocale($globalSetting->locale);
            setlocale(LC_TIME, $globalSetting->locale . '_' . mb_strtoupper($globalSetting->locale));

            return view('auth.password-confirm', ['request' => $request, 'globalSetting' => $globalSetting]);
        });

        Fortify::twoFactorChallengeView(function () {
            $globalSetting = GlobalSetting::first();
            App::setLocale($globalSetting->locale);
            Carbon::setLocale($globalSetting->locale);
            setlocale(LC_TIME, $globalSetting->locale . '_' . mb_strtoupper($globalSetting->locale));
            $frontWidgets = FrontWidget::all();

            return view('auth.two-factor-challenge', [
                'globalSetting' => $globalSetting,
                'frontWidgets' => $frontWidgets,
            ]);
        });

        Fortify::registerView(function () {

            // ISWORKSUITE
            $company = Company::first();
            $globalSetting = GlobalSetting::first();

            if (!$company->allow_client_signup) {
                return redirect(route('login'));
            }

            App::setLocale($globalSetting->locale);
            Carbon::setLocale($globalSetting->locale);
            setlocale(LC_TIME, $globalSetting->locale . '_' . mb_strtoupper($globalSetting->locale));
            $frontWidgets = FrontWidget::all();

            return view('auth.register', [
                'globalSetting' => $globalSetting,
                'frontWidgets' => $frontWidgets,
            ]);

        });

        Fortify::verifyEmailView(function () {
            $userAuth = UserAuth::find(user()->user_auth_id);

            if ((!is_null($userAuth->email_code_expires_at) && $userAuth->email_code_expires_at->isPast()) || is_null($userAuth->email_code_expires_at)) {
                $userAuth->sendEmailVerificationNotification();
            }

            $frontWidgets = FrontWidget::all();

            return view('auth.verify-email', [
                'frontWidgets' => $frontWidgets,
            ]);
        });


    }

    public function checkMigrateStatus()
    {
        return check_migrate_status();
    }

}
