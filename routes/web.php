<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Middleware\CheckRole;


//Clear Cache facade value:
Route::get('/cache-clear', function () {
    Artisan::call('optimize:clear');
    return '<h1>All Cache cleared</h1>';
});

Route::get('/', function () {
    return redirect(config('app.site_url').'/login');
});

Route::get('/api', function () {
    return redirect(config('app.site_url').'/login');
});


Route::get('/phpinfo', function(){
    ob_start();
    phpinfo();
    $phpInfo = ob_get_clean();

    echo $phpInfo;
});