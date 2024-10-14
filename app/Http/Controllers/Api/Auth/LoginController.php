<?php

namespace App\Http\Controllers\Api\Auth;

use DB;
use Carbon\Carbon;
use App\Models\User;
use App\Mail\MfaTokenMail;
use App\Mail\MfaGoogleMail;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Controllers\Api\APIController;
use Symfony\Component\HttpFoundation\Response;
use PragmaRX\Google2FAQRCode\Google2FA; 



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
            'user_email' => ['required', 'email', 'regex:/^(?!.*[\/]).+@(?!.*[\/]).+\.(?!.*[\/]).+$/i', 'exists:users,user_email'],
            'password'   => 'required|min:8',
            'is_set'     => 'required|boolean',
        ], [
            'user_email.exists' => trans('messages.wrong_credentials'),
        ], [
            'user_email' => 'email',
            'is_set'     => 'set',
        ]);

        try {
            DB::beginTransaction();
            $deletedUser = User::where('user_email', $credentials['user_email'])->onlyTrashed()->first();

            if ($deletedUser && $deletedUser->deleted_at) {
                return $this->setStatusCode(403)->respondWithError(trans('auth.account_suspended'));
            }

           
            $credentials = collect($credentials)->except('is_set')->toArray();
            if (!$token = JWTAuth::attempt($credentials)) {
                return $this->setStatusCode(400)->respondWithError(trans('auth.failed'));
            }

            $mfaMethod          = getSetting('mfa_method');
            $otpTokenExpireTime = getSetting('otp_expire_time') ? (int)getSetting('otp_expire_time') : 10;

            $auth_user = User::where('user_email', $credentials['user_email'])->first();

            if (($mfaMethod === 'email') && $request->is_set == false) {
                
                $otp = generateToken(8);
                $expiry = now()->addMinutes($otpTokenExpireTime); 

                $auth_user->otp = $otp;
                $auth_user->otp_expires_at = $expiry;
                $auth_user->save();

                Mail::to($auth_user->user_email)->queue(new MfaTokenMail($auth_user->full_name, $otp, $otpTokenExpireTime));
                DB::commit();
                return $this->respondOk([
                    'status'     => true,
                    'message'    => trans('auth.otp_sent'),
                    'mfa_method' => $mfaMethod
                ])->setStatusCode(Response::HTTP_OK);

            } elseif (($mfaMethod === 'google') && ($request->is_set == false)) {

               /* if (!$auth_user->google2fa_secret) {
                    $qrcodeUrl = $this->generateGoogle2faSecret($auth_user);

                    DB::commit();
                    return $this->respondOk([
                        'status'     => true,
                        'message'    => trans('auth.google_authenticator_not_setup'),
                        'mfa_method' => $mfaMethod,
                        'qrcodeUrl'  => $qrcodeUrl

                    ])->setStatusCode(Response::HTTP_OK);
                }*/
    
                return $this->respondOk([
                    'status'     => true,
                    'message'    => trans('auth.mfa_required'),
                    'mfa_method' => $mfaMethod,
                ])->setStatusCode(Response::HTTP_OK);

            }

        } catch (Exception $e) {
            DB::rollBack();
            
            // dd('Error in LoginController::login (' . $e->getCode() . '): ' . $e->getMessage() . ' at line ' . $e->getLine());

            \Log::info('Error in LoginController::login (' . $e->getCode() . '): ' . $e->getMessage() . ' at line ' . $e->getLine());
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
        } catch (JWTException $e) {
            \Log::info('Error in LoginController::login (' . $e->getCode() . '): ' . $e->getMessage() . ' at line ' . $e->getLine());
            return $this->setStatusCode(500)->respondWithError(trans('auth.messages.could_not_create_token'));
        }

        $user = JWTAuth::user();

        $user->update(['last_login_at'=>now()]);

        //User Activity
        if (!auth()->user()->is_system_admin) {
            $hospitals = $user->getHospitals()->get();
            if($hospitals->count() > 0){
                $login_date  = date('Y-m-d');
                foreach($hospitals as $hospital){
                    $userActivityRecord = ['user_id' => $user->id, 'hospital_id' => $hospital->id, 'login_date' => $login_date];
                    UserActivity::firstOrCreate($userActivityRecord);
                }
            }
        }


        $data = [
            'uuid'                  => $user->uuid,
            'full_name'             => $user->full_name,
            'user_email'            => $user->user_email,
            'primary_role'          => $user->primary_role,
            'role'                  => $user->role->role_name,
            'trust'                 => $user->trusts ? $user->trusts()->value('id') : null,
            'trust_name'            => $user->trusts ? $user->trusts()->value('trust_name') : null,
            'hospital'              => $user->getHospitals()->pluck('hospital_name', 'id')->toArray(),
            'is_tos'                => $user->is_tos,
        ];

        if($user->primary_role != config('constant.roles.booker')){
            $data['speciality']     = $user->specialityDetail()->value('speciality_name');
            $data['sub_speciality'] = $user->subSpecialityDetail()->value('sub_speciality_name');
        }

        $reponseData = [
            'status'        => true,
            'message'       => trans('messages.login_success'),
            'token_type'    => 'Bearer',
            'access_token'  => $token,
            'data'          => $data
        ];

        return $this->setStatusCode(200)->respondOk($reponseData);
    }
    
    
    public function generateGoogle2faSecret($user)
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        $user->google2fa_secret = $secret;
        $user->save();

        $appName = config('app.name');
        $appName = str_replace(' ', '', $appName);
        
        return $google2fa->getQRCodeInline($appName, $user->user_email, $secret);
    }

    public function verifyMfa(Request $request)
    {
        $method = getSetting('mfa_method');

        $request->validate([
            'user_email' => ['required', 'email', 'regex:/^(?!.*[\/]).+@(?!.*[\/]).+\.(?!.*[\/]).+$/i', 'exists:users,user_email'],
            'otp'  => 'required',
        ],[],[
            'user_email' => 'email',
        ]);

        try {
            DB::beginTransaction();
            $deletedUser = User::where('user_email', $request->user_email)->onlyTrashed()->first();
            if ($deletedUser && $deletedUser->deleted_at) {
                return $this->setStatusCode(403)->respondWithError(trans('auth.account_suspended'));
            }

            $user = User::where('user_email', $request->user_email)->first();

            if ($method === 'email') {
               
                if ($user->otp !== $request->otp || now()->gt($user->otp_expires_at)) {
                    return $this->setStatusCode(400)->respondWithError(trans('auth.invalid_otp'));
                }

                $user->otp = null;
                $user->otp_expires_at = null;

            }elseif ($method === 'google') {

                $google2fa = new Google2FA();
               
                if($user->google2fa_secret){
                    $secretKey = $user->google2fa_secret;
                    $isValid = $google2fa->verifyKey($secretKey, $request->otp);
    
                    if (!$isValid) {
                        return $this->setStatusCode(400)->respondWithError(trans('auth.invalid_otp'));
                    }
                }else{
                    return $this->setStatusCode(400)->respondWithError(trans('auth.google_authenticator_not_setup'));
                }
                
            }

            $user->last_login_at = now();
            $user->save();

            $mfaTimeDuration    = getSetting('mfa_time_duration') ? 60 * (int)getSetting('mfa_time_duration') : 10;

            $rememberMe = $request->has('remember_me') && $request->remember_me == true;
            if ($rememberMe) {
                // Extend the token's lifetime to 30 days (43200 minutes)
                JWTAuth::factory()->setTTL($mfaTimeDuration);
            } else {
                // Use the default JWT TTL (from jwt.php config)
                JWTAuth::factory()->setTTL(config('jwt.ttl'));
            }

            $token = JWTAuth::fromUser($user);

            //User Activity
            if (!$user->is_system_admin) {
                $hospitals = $user->getHospitals()->get();
                if($hospitals->count() > 0){
                    $login_date  = date('Y-m-d');
                    foreach($hospitals as $hospital){
                        $userActivityRecord = ['user_id' => $user->id, 'hospital_id' => $hospital->id, 'login_date' => $login_date];
                        UserActivity::firstOrCreate($userActivityRecord);
                    }
                }
            }
    
            $data = [
                'uuid'                  => $user->uuid,
                'full_name'             => $user->full_name,
                'user_email'            => $user->user_email,
                'primary_role'          => $user->primary_role,
                'role'                  => $user->role->role_name,
                'trust'                 => $user->trusts ? $user->trusts()->value('id') : null,
                'trust_name'            => $user->trusts ? $user->trusts()->value('trust_name') : null,
                'hospital'              => $user->getHospitals()->pluck('hospital_name', 'id')->toArray(),
                'is_tos'                => $user->is_tos,
            ];
    
            if($user->primary_role != config('constant.roles.booker')){
                $data['speciality']     = $user->specialityDetail()->value('speciality_name');
                $data['sub_speciality'] = $user->subSpecialityDetail()->value('sub_speciality_name');
            }
    
            DB::commit();
            $reponseData = [
                'status'        => true,
                'message'       => trans('messages.login_success'),
                'token_type'    => 'Bearer',
                'access_token'  => $token,
                'mfa_duration'  => $mfaTimeDuration,
                'data'          => $data
            ];
    
            return $this->setStatusCode(200)->respondOk($reponseData);
       
        }catch (Exception $e) {
            DB::rollBack();
            \Log::info('Error in LoginController::verifyMfa (' . $e->getCode() . '): ' . $e->getMessage() . ' at line ' . $e->getLine());
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
        } 
    }


    public function resetGoogle2FA(Request $request)
    {
        $mfaMethod  = getSetting('mfa_method');
        $request->validate([
            'user_email' => ['required', 'email', 'regex:/^(?!.*[\/]).+@(?!.*[\/]).+\.(?!.*[\/]).+$/i', 'exists:users,user_email'],
        ],[],[
            'user_email' => 'email',
        ]);

        try {
            DB::beginTransaction();
            if($mfaMethod == 'google'){

                $deletedUser = User::where('user_email', $request->user_email)->onlyTrashed()->first();
                if ($deletedUser && $deletedUser->deleted_at) {
                    return $this->setStatusCode(403)->respondWithError(trans('auth.account_suspended'));
                }
    
                $auth_user = User::where('user_email', $request->user_email)->first();
    
                $qrcodeUrl = $this->generateGoogle2faSecret($auth_user);

                $base64QRCode = 'data:image/svg+xml;base64,' . base64_encode($qrcodeUrl);
    
                Mail::to($auth_user->user_email)->queue(new MfaGoogleMail($auth_user->full_name, $base64QRCode));

                DB::commit();

                return $this->respondOk([
                    'status'     => true,
                    'message'    => trans('auth.reset_google_authenticator_success'),
                ])->setStatusCode(Response::HTTP_OK);
            }

            return $this->setStatusCode(400)->respondWithError(trans('auth.invalid_mfa_method_google'));
            
        } catch (Exception $e) {
            DB::rollBack();
            
            // dd('Error in LoginController::resetGoogle2FA (' . $e->getCode() . '): ' . $e->getMessage() . ' at line ' . $e->getLine());

            \Log::info('Error in LoginController::resetGoogle2FA (' . $e->getCode() . '): ' . $e->getMessage() . ' at line ' . $e->getLine());
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
        }
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


            $user_details['trust']               = $user->trusts ? $user->trusts()->value('id') : null;
            $user_details['trust_name']          = $user->trusts ? $user->trusts()->value('trust_name') :  null;

            $user_details['hospital']            = $user->getHospitals()->pluck('hospital_name', 'id')->toArray();
            
            $user_details['speciality']          = $user->specialityDetail()->value('id');
            $user_details['speciality_name']     = $user->specialityDetail()->value('speciality_name');

            $user_details['sub_speciality']      = $user->subSpecialityDetail()->value('id');
            $user_details['sub_speciality_name'] = $user->subSpecialityDetail()->value('sub_speciality_name');

            $user_details['created_by']          = $user->createdBy ? $user->createdBy->full_name : null;

            $user_details['last_login_at']       = $user->last_login_at ? dateFormat($user->last_login_at,'D, d M Y - h:i A') : null;
            
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
