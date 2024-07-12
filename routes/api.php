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


   /*
    |--------------------------------------------------------------------------
    | Register API Routes
    |--------------------------------------------------------------------------
    |
    | Route 		: http://localhost:8000/api/register
    | Parameter 	: Multiple
    | Method 		: Post
    |
    */
	Route::post('register',[RegisterController::class,'create']);


    /*
    |--------------------------------------------------------------------------
    | Open API Routes
    |--------------------------------------------------------------------------
    | Method        : Get
    |
    */

    Route::get('get-trusts', [HomeController::class,'getTrusts']);

    Route::get('get-hospitals/{trust}', [HomeController::class,'getHospitals']);



});

Route::group(['namespace' => 'Api','middleware' => ['auth:api']], function () {

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

    /*
    |--------------------------------------------------------------------------
    |  User Details API Routes
    |--------------------------------------------------------------------------
    |
    | Route         : http://localhost:8000/api/me
    | Header        : Content-Type:application/json
    |               : Authorization : Token
    | Method        : Get
    |
    */
    Route::get('me', [LoginController::class, 'getAuthenticatedUser']);




    
});