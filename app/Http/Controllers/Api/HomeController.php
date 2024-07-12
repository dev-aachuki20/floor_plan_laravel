<?php

namespace App\Http\Controllers\Api;

use DB;
use App\Models\User;

use App\Models\Trust;
use App\Models\Hospital;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\APIController;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends APIController
{

    public function getTrusts(){

        $trusts = Trust::pluck('trust_name','id');

        return $this->respondOk([
            'status'   => true,
            'message'   => trans('messages.record_retrieved_successfully'),
            'data'      => $trusts,
        ])->setStatusCode(Response::HTTP_OK);
    }

    public function getHospitals($trust){

        $hospitals = Hospital::where('trust',$trust)->pluck('hospital_name','id');
        
        return $this->respondOk([
            'status'   => true,
            'message'   => trans('messages.record_retrieved_successfully'),
            'data'      => $hospitals,
        ])->setStatusCode(Response::HTTP_OK);
    }


}