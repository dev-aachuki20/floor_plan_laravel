<?php

use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\PasswordResetController;
use App\Http\Controllers\Api\Auth\RegisterController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['namespace' => 'Api'], function () {

    /*
    |--------------------------------------------------------------------------
    | Login API Routes
    |--------------------------------------------------------------------------
    |
    | Route 		: http://localhost:8000/api/login
    | Parameter 	: Email, Password
    | Method 		: Post
    |
    */
	Route::post('login',[LoginController::class,'login']);


    /*
    |--------------------------------------------------------------------------
    | Forgot Password API Routes
    |--------------------------------------------------------------------------
    |
    | Route 		: http://localhost:8000/api/forgot-password
    | Parameter 	: Email
    | Method 		: Post
    |
    */
	Route::post('forgot-password',[PasswordResetController::class,'sendResetLinkEmail']);

  
    /*
    |--------------------------------------------------------------------------
    | Reset Password API Routes
    |--------------------------------------------------------------------------
    |
    | Route 		: http://localhost:8000/api/password/reset
    | Parameter 	: token
    | Method 		: Post
    |
    */
	Route::post('password/reset',[PasswordResetController::class,'resetPassword']);



});

Route::group(['namespace' => 'Api','middleware' => ['jwt.auth']], function () {

    /*
    |--------------------------------------------------------------------------
    |  Logout API Routes
    |--------------------------------------------------------------------------
    |
    | Route         : http://localhost:8000/api/logout
    | Header        : Content-Type:application/json
    |               : Authorization : Token
    | Method        : Get
    |
    */
    Route::post('logout', [LoginController::class,'logout']);

    Route::get('user', [LoginController::class, 'getAuthenticatedUser']);

    
});