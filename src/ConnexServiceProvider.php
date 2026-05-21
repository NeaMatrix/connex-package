<?php

namespace Torgodly\Connex;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Torgodly\Connex\Connex\DefaultOtpConfirmationHandler;
use Torgodly\Connex\Contracts\HandlesOtpConfirmation;
use Torgodly\Connex\Http\Controllers\ConnexAuthController;
use Torgodly\Connex\Http\Controllers\LoginPageController;

class ConnexServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/connex.php', 'connex');

        $this->app->singleton(HandlesOtpConfirmation::class, function ($app) {
            $class = config('connex.otp_handler', DefaultOtpConfirmationHandler::class);

            return $app->make($class);
        });
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'connex');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        Blade::component('connex::components.connex-login', 'connex-login');

        $this->publishes([
            __DIR__.'/../config/connex.php' => config_path('connex.php'),
        ], 'connex-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/connex'),
        ], 'connex-views');

        $this->publishes([
            __DIR__.'/../resources/js/connex-login.js' => resource_path('js/vendor/connex-login.js'),
        ], 'connex-assets');

        $this->registerRoutes();
    }

    protected function registerRoutes(): void
    {
        $apiPrefix = trim(config('connex.routes.api_prefix', 'connex/api'), '/');
        $bootstrapPath = trim(config('connex.routes.bootstrap', 'bootstrap'), '/');
        $requestOtpPath = trim(config('connex.routes.request_otp', 'request-otp'), '/');
        $confirmPath = trim(config('connex.routes.confirm_otp', 'confirm-otp'), '/');

        Route::middleware('web')
            ->prefix($apiPrefix)
            ->group(function () use ($bootstrapPath, $requestOtpPath, $confirmPath) {
                Route::post($bootstrapPath, [ConnexAuthController::class, 'bootstrap'])
                    ->name('connex.api.bootstrap');
                Route::post($requestOtpPath, [ConnexAuthController::class, 'requestOtp'])
                    ->name('connex.api.request-otp');
                Route::post($confirmPath, [ConnexAuthController::class, 'confirmOtp'])
                    ->name('connex.api.confirm-otp');
            });

        if (! config('connex.routes.login')) {
            return;
        }

        Route::middleware('web')
            ->group(function () {
                Route::get(config('connex.routes.login'), [LoginPageController::class, 'showLogin'])
                    ->name('connex.login');

                if ($path = config('connex.routes.login_confirm')) {
                    Route::get($path, [LoginPageController::class, 'showLoginConfirm'])
                        ->name('connex.login-confirm');
                }
            });
    }
}
