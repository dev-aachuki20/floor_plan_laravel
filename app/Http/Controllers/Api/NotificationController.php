<?php

namespace App\Http\Controllers\Api;


use DB;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\APIController;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends APIController
{
    public function index(){
       
        return $this->respondOk([
            'status'   => true,
            'message'   => trans('messages.record_retrieved_successfully'),
            'data'      => $roles,
        ])->setStatusCode(Response::HTTP_OK);
    }

    
  
   
  
}