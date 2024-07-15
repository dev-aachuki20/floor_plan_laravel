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
            'user_email'    => ['required','email','regex:/^(?!.*[\/]).+@(?!.*[\/]).+\.(?!.*[\/]).+$/i','exists:users,user_email,deleted_at,NULL'],
            'password' => 'required|min:8',
        ],[
            'user_email.exists' => trans('validation.invalid'),
        ],[
            'user_email' => 'email',
        ]);


        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return $this->setStatusCode(400)->respondWithError(trans('auth.failed'));
            }
        } catch (Exception $e) {
            // dd('Error in LoginController::login (' . $e->getCode() . '): ' . $e->getMessage() . ' at line ' . $e->getLine());
          
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));

        }catch (JWTException $e) {
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
                'hospital'              => $user->hospital ? $user->hospital->hospital_name : null,
                
            ]
        ];

        return $this->setStatusCode(200)->respondOk($reponseData);
    }

    public function getAuthenticatedUser()
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
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

        if($user){

            $user_details['uuid']          = $user->uuid;
            $user_details['full_name']     = ucwords($user->full_name);
            $user_details['primary_role']  = $user->primary_role;
            $user_details['role_name']     = $user->role->role_name;
            $user_details['user_email']    = $user->user_email;
            $user_details['phone']         = $user->phone;

            $hospital = $user->hospital()->first();

            $user_details['trust']         = $hospital ? $hospital->trust : null;
            $user_details['trust_name']    = $hospital ? $hospital->trustDetails->trust_name : null;

            $hospital = $user->hospital()->first();

            $user_details['hospital']      = $hospital ? $hospital->id : null;
            $user_details['hospital_name'] = $hospital ? $hospital->hospital_name : null;
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