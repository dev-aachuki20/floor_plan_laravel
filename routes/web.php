<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Middleware\CheckRole;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

//Clear Cache facade value:
Route::get('/cache-clear', function () {
    Artisan::call('optimize:clear');
    return '<h1>All Cache cleared</h1>';
});

/*

// Authentication Routes
Route::group(['middleware' => ['guest','PreventBackHistory']], function () {
    Route::controller(LoginController::class)->group(function(){

        //Frontend 
        Route::get('/', function () {
            return redirect()->route('login');
        });

        Route::get('/login', 'index')->name('login');
        Route::post('/login','login')->name('authenticate');
        // Route::get('logout','logout')->name('logout');

        //Backend
        Route::get('admin/login', 'showAdminLogin')->name('admin.login');
        Route::post('admin/login','login')->name('admin.authenticate');
        
    });

    Route::controller(ForgotPasswordController::class)->group(function(){
        Route::get('/forgot-password', 'index')->name('forgot.password');
        Route::post('/forgot-pass-mail','sendResetLinkEmail')->name('password_mail_link');

        Route::get('admin/forgot-password', 'showAdminForgetPassword')->name('admin.forgot.password');
        Route::post('admin/forgot-pass-mail','sendResetLinkEmail')->name('admin.password_mail_link');
    });

    Route::controller(ResetPasswordController::class)->group(function(){
        Route::get('reset-password/{token}', 'showform')->name('resetPassword');
        Route::post('/reset-password','reset')->name('reset-new-password');

        Route::get('admin/reset-password/{token}', 'showAdminResetPassword')->name('admin.resetPassword');
        Route::post('admin/reset-password','reset')->name('admin.reset-new-password');
    });

});


Route::middleware(['auth','PreventBackHistory', 'userinactive','role:' . implode(',', [config('constant.role.system_admin')])])->group(function () {

    Route::group(['as' => 'admin.', 'prefix' => 'admin','namespace' => 'App\Http\Controllers\Backend'], function () {

        Route::get('admin/logout',[LoginController::class,'logout'])->name('logout');

        Route::get('dashboard', 'DashboardController@index')->name('dashboard');

        Route::get('profile', 'ProfileController@showProfile')->name('show.profile');
        Route::post('profile', 'ProfileController@updateProfile')->name('update.profile');

        // Route::get('change-password', 'ProfileController@showChangePassword')->name('show.change.password');
        Route::post('change-password', 'ProfileController@updateChangePassword')->name('update.change.password');
        
    });


});
*/