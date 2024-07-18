<?php

namespace App\Http\Controllers\Api\Auth;

use DB;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Controllers\Api\APIController;
use Symfony\Component\HttpFoundation\Response;


class LoginController extends APIController
{

    /**
     * Log the user in.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'user_email'    => ['required', 'email', 'regex:/^(?!.*[\/]).+@(?!.*[\/]).+\.(?!.*[\/]).+$/i', 'exists:users,user_email,deleted_at,NULL'],
            'password' => 'required|min:8',
        ], [
            'user_email.exists' => trans('validation.invalid'),
        ], [
            'user_email' => 'email',
        ]);


        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return $this->setStatusCode(400)->respondWithError(trans('auth.failed'));
            }
        } catch (Exception $e) {
            // dd('Error in LoginController::login (' . $e->getCode() . '): ' . $e->getMessage() . ' at line ' . $e->getLine());

            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
        } catch (JWTException $e) {
            // dd('Error in LoginController::login (' . $e->getCode() . '): ' . $e->getMessage() . ' at line ' . $e->getLine());
            return $this->setStatusCode(500)->respondWithError(trans('auth.messages.could_not_create_token'));
        }

        $user = JWTAuth::user();

        $reponseData = [
            'status'        => true,
            'message'       => trans('messages.login_success'),
            'token_type'    => 'Bearer',
            'access_token'  => $token,
            'data'          => [
                // 'id'                    => $user->id,
                'uuid'                  => $user->uuid,
                'full_name'             => $user->full_name,
                'user_email'            => $user->user_email,
                'role'                  => $user->role->role_name,
                'hospital'              => $user->getHospitals()->pluck('hospital_name', 'id')->toArray(),
                'speciality'            => $user->specialityDetail()->value('speciality_name'),
                'sub_speciality'        => $user->subSpecialityDetail()->value('sub_speciality_name'),
            ]
        ];

        return $this->setStatusCode(200)->respondOk($reponseData);
    }

    public function getAuthenticatedUser()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }

        $user_details = [];

        if ($user) {

            $user_details['uuid']          = $user->uuid;
            $user_details['full_name']     = ucwords($user->full_name);
            $user_details['primary_role']  = $user->primary_role;
            $user_details['role_name']     = $user->role->role_name;
            $user_details['user_email']    = $user->user_email;
            $user_details['phone']         = $user->phone;


            $user_details['trust'] =   $user->trusts ? $user->trusts()->value('id') : null;
            $user_details['trust_name'] = $user->trusts ? $user->trusts()->value('trust_name') :  null;

            $user_details['hospital'] = $user->getHospitals()->pluck('hospital_name', 'id')->toArray();
            
            $user_details['speciality'] = $user->specialityDetail()->value('id');
            $user_details['speciality_name'] = $user->specialityDetail()->value('speciality_name');

            $user_details['sub_speciality'] = $user->subSpecialityDetail()->value('id');
            $user_details['sub_speciality_name'] = $user->subSpecialityDetail()->value('sub_speciality_name');

            $user_details['created_by']    = $user->createdBy ? $user->createdBy->full_name : null;
            
        }

        return response()->json(compact('user_details'));
    }

    public function logout(Request $request)
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return $this->respondOk([
            'success'   => true,
            'message'   => trans('auth.messages.logout.success'),
        ]);
    }
}
