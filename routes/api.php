<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\PasswordResetController;


use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\UserController;
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
    Route::post('login', [LoginController::class, 'login']);


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
    Route::post('forgot-password', [PasswordResetController::class, 'sendResetLinkEmail']);


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
    Route::post('password/reset', [PasswordResetController::class, 'resetPassword']);


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
    Route::post('register', [RegisterController::class, 'create']);


    /*
    |--------------------------------------------------------------------------
    | Verify Email API Routes
    |--------------------------------------------------------------------------
    |
    | Route 		: http://localhost:8000/api/email/verify/{id}/{hash}
    | Parameter 	: Multiple
    | Method 		: Post
    |
    */
    Route::get('/email/verify/{uuid}/{hash}', [RegisterController::class, 'verifyEmail']);


    /*
    |--------------------------------------------------------------------------
    | Open API Routes
    |--------------------------------------------------------------------------
    | Method        : Get
    |
    */

    Route::get('get-roles', [HomeController::class, 'getRoles']);

    Route::get('get-trusts', [HomeController::class, 'getTrusts']);

    Route::get('get-hospitals/{trust}', [HomeController::class, 'getHospitals']);

    Route::get('get-specialities/{hospital}', [HomeController::class, 'getSpecialities']);

    Route::get('get-sub-specialities/{speciality}', [HomeController::class, 'getSubSpecialities']);
});

Route::group(['namespace' => 'App\Http\Controllers\Api', 'middleware' => ['auth:api']], function () {

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
    Route::post('logout', [LoginController::class, 'logout']);

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

    /*
    |--------------------------------------------------------------------------
    |  Update Auth User Profile API Routes
    |--------------------------------------------------------------------------
    |
    | Route         : http://localhost:8000/api/update-profile
    | Header        : Content-Type:application/json
    |               : Authorization : Token
    | Parameter 	: Multiple
    | Method        : POST
    |
    */
    Route::post('update-profile', [HomeController::class, 'updateProfile']);



    Route::group(['middleware' => ['role:' . implode(',', [config('constant.roles.system_admin'), config('constant.roles.trust_admin'), config('constant.roles.hospital_admin')])]], function () {

        /*
        |--------------------------------------------------------------------------
        |  Get User Records API Routes
        |--------------------------------------------------------------------------
        |
        | Route         : http://localhost:8000/api/users
        | Header        : Content-Type:application/json
        |               : Authorization : Token
        | Parameters    : 
        |                 - filter_by,filter_value (optional): string (e.g., ?filter_by=value&filter_value=value)
        |                 - search (optional): string (e.g., ?search=term)
        |                 - page (optional): integer (e.g., ?page=1)
        |                 - per_page (optional): integer (e.g., ?per_page=10)
        | Method        : GET
        |
        */
        Route::get('users', [UserController::class, 'index']);


        /*
        |--------------------------------------------------------------------------
        |  Get Single User Record API Route
        |--------------------------------------------------------------------------
        |
        | Route         : http://localhost:8000/api/user/show/{uuid}
        | Header        : Content-Type:application/json
        |               : Authorization : Token
        | Method        : GET        
        */
        Route::get('user/show/{uuid}', [UserController::class, 'show']);

        /*
        |--------------------------------------------------------------------------
        |  Add User Record API Route
        |--------------------------------------------------------------------------
        |
        | Route         : http://localhost:8000/api/user/create
        | Header        : Content-Type:application/json
        |               : Authorization : Token
        | Method        : POST        
        */
        Route::post('user/create', [UserController::class, 'store']);

        /*
        |--------------------------------------------------------------------------
        |  Update User Record API Route
        |--------------------------------------------------------------------------
        |
        | Route         : http://localhost:8000/api/user/update/{uuid}
        | Header        : Content-Type:application/json
        |               : Authorization : Token
        | Parameters    : 
        |                 - uuid: string (e.g., /bbb6d5a6-36eb-4d8e-8397-c09e53cc96c2)
        | Method        : PUT        
        */
        Route::put('user/update/{uuid}', [UserController::class, 'update']);

        /*
        |--------------------------------------------------------------------------
        |  Delete User Record API Route
        |--------------------------------------------------------------------------
        |
        | Route         : http://localhost:8000/api/user/delete/uuid
        | Header        : Content-Type:application/json
        |               : Authorization : Token
        | Parameters    : 
        |                 - uuid: string (e.g., /bbb6d5a6-36eb-4d8e-8397-c09e53cc96c2)
        | Method        : DELETE
        */
        Route::delete('user/delete/{uuid}', [UserController::class, 'destroy']);



        // Route::apiResource('users',UserController::class)->parameters(['users' => 'user']);

    });
});
