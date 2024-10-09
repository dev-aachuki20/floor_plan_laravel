<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\PasswordResetController;


use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RotaTableController;
use App\Http\Controllers\Api\NotificationController;


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
    | Verify MFA API Routes
    |--------------------------------------------------------------------------
    |
    | Route 		: http://localhost:8000/api/verify-mfa
    | Parameter 	: Multiple
    | Method 		: Post
    |
    */
    Route::post('verify-mfa', [LoginController::class, 'verifyMfa']);

    /*
    |--------------------------------------------------------------------------
    | Reset Google Authenticator API Routes
    |--------------------------------------------------------------------------
    |
    | Route 		: http://localhost:8000/api/reset-google-authenticator
    | Parameter 	: Multiple
    | Method 		: Post
    |
    */
    Route::post('reset-google-authenticator', [LoginController::class, 'resetGoogle2FA']);

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
    // Route::post('register', [RegisterController::class, 'create']);


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
    // Route::get('/email/verify/{uuid}/{hash}', [RegisterController::class, 'verifyEmail']);

});

Route::group(['namespace' => 'App\Http\Controllers\Api', 'middleware' => ['auth:api']], function () {

  
    Route::get('get-specialities/{type?}', [HomeController::class, 'getSpecialities']);

    Route::get('get-sub-specialities/{speciality?}', [HomeController::class, 'getSubSpecialities']);
    
    Route::get('get-hospitals/{trust?}', [HomeController::class, 'getHospitals']);


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

    /*
    |--------------------------------------------------------------------------
    |  Download Users Data API Route
    |--------------------------------------------------------------------------
    |
    | Route         : http://localhost:8000/api/users/export
    | Header        : Content-Type:application/json
    |               : Authorization : Token
    | Parameters    :
    |
    | Method        : GET
    */
    Route::get('users/export', [UserController::class, 'exportUserData']);

    /*
    |--------------------------------------------------------------------------
    | Update Auth User Is Tos API Route
    |--------------------------------------------------------------------------
    |
    | Route         : http://localhost:8000/api/update-tos
    | Header        : Content-Type:application/json
    |               : Authorization : Token
    | Parameters    :
    |
    | Method        : POST
    */
    Route::post('update-tos', [UserController::class, 'updateIstos']);

    /*
    |--------------------------------------------------------------------------
    |  Delete User Record API Route
    |--------------------------------------------------------------------------
    |
    | Route         : http://localhost:8000/api/auth-user/delete
    | Header        : Content-Type:application/json
    |               : Authorization : Token
    | Parameters    :
    |            
    | Method        : POST
    */
    Route::post('auth-user/delete', [UserController::class, 'authUserDestroy']);

    /*
    |--------------------------------------------------------------------------
    |  Get Rota Table Records API Routes
    |--------------------------------------------------------------------------
    |
    | Route         : http://localhost:8000/api/rota-table
    | Header        : Content-Type:application/json
    |               : Authorization : Token
    | Parameters    :
    |                 - filter_by,filter_value (optional): string (e.g., ?filter_by=value&filter_value=value)
    |                 - search (optional): string (e.g., ?search=term)
    |                 - page (optional): integer (e.g., ?page=1)
    |                 - per_page (optional): integer (e.g., ?per_page=10)
    | Method        : POST
    |
    */
    Route::post('rota-table', [RotaTableController::class, 'index']);

    Route::group(['middleware' => ['role:' . implode(',', [config('constant.roles.speciality_lead'), config('constant.roles.anesthetic_lead'), config('constant.roles.staff_coordinator')])]], function () {

        /*
        |--------------------------------------------------------------------------
        |  Update Availability Rota Table Record API Route
        |--------------------------------------------------------------------------
        |
        | Route         : http://localhost:8000/api/rota-table/update-availability
        | Header        : Content-Type:application/json
        |               : Authorization : Token
        | Parameters    :
        |
        | Method        : POST
        */
        Route::post('/rota-table/update-availability', [RotaTableController::class, 'updateAvailability']);
    });

    Route::group(['middleware' => ['role:' . implode(',', [config('constant.roles.system_admin'), config('constant.roles.trust_admin'), config('constant.roles.hospital_admin')])]], function () {

        /*
        |--------------------------------------------------------------------------
        |  Get User Records API Routes
        |--------------------------------------------------------------------------
        |
        | Route         : http://localhost:8000/api/users
        | Header        : Content-Type:application/json
        |               : Authorization : Token
        | Parameters    : - is_deleted : true or false
        |                 - filter_by,filter_value (optional): string (e.g., ?filter_by=value&filter_value=value)
        |                 - search (optional): string (e.g., ?search=term)
        |                 - page (optional): integer (e.g., ?page=1)
        |                 - per_page (optional): integer (e.g., ?per_page=10)
        | Method        : POST
        |
        */
        Route::post('users', [UserController::class, 'index']);


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
        | Method        : GET
        */
        Route::get('user/delete/{uuid}', [UserController::class, 'destroy']);

        /*
        |--------------------------------------------------------------------------
        |  Add Rota Table Record API Route
        |--------------------------------------------------------------------------
        |
        | Route         : http://localhost:8000/api/rota-table/get-details
        | Header        : Content-Type:application/json
        |               : Authorization : Token
        | Parameters    : hospital,
        | Method        : POST
        */
        Route::post('rota-table/get-details', [RotaTableController::class, 'getDetails']);


        /*
        |--------------------------------------------------------------------------
        |  Save Rota Table Record API Route
        |--------------------------------------------------------------------------
        |
        | Route         : http://localhost:8000/api/rota-table/save
        | Header        : Content-Type:application/json
        |               : Authorization : Token
        | Parameters    :
        |                 - uuid: string (e.g., /bbb6d5a6-36eb-4d8e-8397-c09e53cc96c2)
        | Method        : POST
        */
        Route::post('/rota-table/save', [RotaTableController::class, 'saveRota']);

    });

    Route::group(['middleware' => ['role:' . implode(',', [config('constant.roles.system_admin'), config('constant.roles.trust_admin'), config('constant.roles.hospital_admin'),config('constant.roles.chair')])]], function () {

        Route::get('get-trusts', [HomeController::class, 'getTrusts']);

        Route::get('get-roles', [HomeController::class, 'getRoles']);
    
        
        /*
        |--------------------------------------------------------------------------
        |  Get Rota Table Dropdown API Routes
        |--------------------------------------------------------------------------
        |
        | Route         : http://localhost:8000/api/rota-table/dropdowns
        | Header        : Content-Type:application/json
        |               : Authorization : Token
        | Parameters    :
        |
        | Method        : POST
        |
        */
        Route::post('rota-table/dropdowns', [RotaTableController::class, 'rotaTableDropdown']);

        /*
        |--------------------------------------------------------------------------
        |  Get Rota Table Filter Dropdown API Routes
        |--------------------------------------------------------------------------
        |
        | Route         : http://localhost:8000/api/rota-table/filter-dropdowns
        | Header        : Content-Type:application/json
        |               : Authorization : Token
        | Parameters    :
        |
        | Method        : POST
        |
        */
        Route::post('rota-table/filter-dropdowns', [RotaTableController::class, 'rotaTableFitlerDropdown']);

        /*
        |--------------------------------------------------------------------------
        |  Report API Routes
        |--------------------------------------------------------------------------
        |
        | Route         : http://localhost:8000/api/reports
        | Header        : Content-Type:application/json
        |               : Authorization : Token
        | Parameters    :- week_days, hospital
        | Method        : Post
        |
        */
        Route::post('reports', [ReportController::class, 'index']);


        /*
        |--------------------------------------------------------------------------
        |  Report chart API Routes
        |--------------------------------------------------------------------------
        |
        | Route         : http://localhost:8000/api/reports-chart
        | Header        : Content-Type:application/json
        |               : Authorization : Token
        | Parameters    :- year,month, hospital
        | Method        : Post
        |
        */
        Route::post('reports-chart', [ReportController::class, 'reportChart']);
    });

    /*
    |--------------------------------------------------------------------------
    |  Notifications API Routes
    |--------------------------------------------------------------------------
    |
    | Route         : http://localhost:8000/api/notifications
    | Header        : Content-Type:application/json
    |               : Authorization : Token
    | Parameters    :- filter_by = type, filter_value = 'session_confirmed','session_canceled'
    | Method        : Post
    |
    */
    Route::post('notifications', [NotificationController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    |  Mark As Read Notification API Routes
    |--------------------------------------------------------------------------
    |
    | Route         : http://localhost:8000/api/notifications/{uuid}/mark-as-read
    | Header        : Content-Type:application/json
    |               : Authorization : Token
    | Parameters    :- uuid: string (e.g., /bbb6d5a6-36eb-4d8e-8397-c09e53cc96c2)
    | Method        : GET
    |
    */
    Route::get('/notifications/{uuid}/mark-as-read', [NotificationController::class,'makeAsRead']);




});




