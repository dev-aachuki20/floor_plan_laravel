<?php

namespace App\Http\Controllers\Backend;

use App\Models\User;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $staffCount = User::whereHas('roles',function($query){
            $query->where('id',config('constant.roles.staff'));
        })->count();

        $companyCount = User::whereHas('roles',function($query){
            $query->where('id',config('constant.roles.company'));
        })->count();
        
        return view('backend.dashboard',compact('staffCount','companyCount'));
    }


}
