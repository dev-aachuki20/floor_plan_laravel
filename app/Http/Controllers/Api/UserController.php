<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Hospital;
use Illuminate\Http\Request;
use App\Http\Requests\User\StoreRequest;
use App\Http\Requests\User\UpdateRequest;
use App\Http\Controllers\Api\APIController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Exports\UsersExport;
use App\Mail\WelcomeEmail;
use App\Mail\UserUpdatedMail;
use App\Mail\UserDeletedMail;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Notifications\SendNotification;
use PragmaRX\Google2FAQRCode\Google2FA; 



class UserController extends APIController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'filter_value'   => 'nullable|array',
            'filter_value.*' => 'integer',
        ]);

        try {
            DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");

            $user = auth()->user();

            $model = User::query()->with(['role:id,role_name'])->select('id', 'uuid', 'full_name', 'primary_role','last_login_at');

            //Start Apply filters
            if ($request->search) {

                $searchValue = trim($request->search);

                $model = $model->where(function ($query) use ($searchValue) {

                    $query->where('full_name', 'like', '%' . $searchValue . '%')
                        ->orWhere('user_email','like', '%' . $searchValue . '%')
                        ->orWhereRelation('role', 'role_name', 'like', '%' . $searchValue . '%')
                        ->orWhereRelation('specialityDetail', 'speciality_name', 'like', '%' . $searchValue . '%')
                        ->orWhereRelation('subSpecialityDetail', 'sub_speciality_name', 'like', '%' . $searchValue . '%')
                        ->orWhereRelation('getHospitals', 'hospital_name', 'like', '%' . $searchValue . '%');
                });
            }

            if ($request->filter_by) {

                if ($request->filter_by == 'role' && $request->filter_value) {

                    $model->whereRelation('role', function ($query) use ($request) {
                        $query->whereIn('id', $request->filter_value);
                    });
                } else if ($request->filter_by == 'speciality' && $request->filter_value) {

                    $model->whereRelation('specialityDetail', function ($query) use ($request) {
                        $query->whereIn('id', $request->filter_value);
                    });
                } else if ($request->filter_by == 'sub_speciality' && $request->filter_value) {

                    $model->whereRelation('subSpecialityDetail', function ($query) use ($request) {
                        $query->whereIn('id', $request->filter_value);
                    });
                } else if ($request->filter_by == 'hospital' && $request->filter_value) {

                    $model->whereRelation('getHospitals', function ($query) use ($request) {
                        $query->whereIn('id', $request->filter_value);
                    });
                }
            }
            //End Apply filters

            if ($user->is_trust_admin) {

                $hospital_ids = $user->getHospitals()->select('hospital_id')->groupBy('hospital_id')->pluck('hospital_id')->toArray();

                $model = $model->whereRelation('getHospitals', function ($query) use ($hospital_ids) {
                    $query->whereIn('hospital.id', $hospital_ids);
                });

            } else if ($user->is_hospital_admin) {

                $hospital_ids = $user->getHospitals()->select('hospital_id')->groupBy('hospital_id')->pluck('hospital_id')->toArray();

                $model = $model->whereRelation('getHospitals', function ($query) use ($hospital_ids) {
                    $query->whereIn('hospital.id', $hospital_ids);
                });
            }

            //only Trashed Users
            if($request->is_deleted){
                $model = $model->onlyTrashed();
            }

            // Filter based on the authenticated user's role
            $getAllRecords = $model->where(function ($qu) use ($user) {
                $qu->whereRelation('role', 'id', '!=', config('constant.roles.system_admin'));
                if ($user->is_trust_admin) {
                    $qu->whereRelation('role', 'id', '!=', config('constant.roles.trust_admin'));
                }
                if ($user->is_hospital_admin) {
                    $qu->whereRelation('role', 'id', '!=', config('constant.roles.trust_admin'))
                        ->whereRelation('role', 'id', '!=', config('constant.roles.hospital_admin'));
                }
            })->orderBy('created_at', 'desc')->paginate(10);


            if ($getAllRecords->count() > 0) {
                foreach ($getAllRecords as $record) {
                    $record->full_name          = ucwords($record->full_name);
                    $record->speciality         = $record->specialityDetail()->value('speciality_name');
                    $record->sub_speciality     = $record->subSpecialityDetail()->value('sub_speciality_name');
                    $record->trust              = $record->trusts()->pluck('trust_name', 'id')->toArray();
                    $record->hospitals          = $record->getHospitals()->pluck('hospital_name')->toArray();
                    $record->last_login_at      = $record->last_login_at ? dateFormat($record->last_login_at,'D, d M Y - h:i A') : null;
                }
            }

            return $this->respondOk([
                'status'   => true,
                'message'   => trans('messages.record_retrieved_successfully'),
                'data'      => $getAllRecords,
            ])->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            \Log::info('Error in UserController::index (' . $e->getCode() . '): ' . $e->getMessage() . ' at line ' . $e->getLine());
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        try {
            DB::beginTransaction();
            // $password = $request->password;
            $user = User::create([
                'primary_role' => $request->role,
                'full_name'    => $request->full_name,
                'user_email'   => $request->user_email,
                // 'password'     => Hash::make($password),
                'email_verified_at' => now(),
            ]);

            //Set Password Url
            $token = generateRandomString(64);
            $setPasswordUrl = config('app.site_url').'/set-password?token='.$token;

            DB::table('password_reset_tokens')
            ->where('email', $user->user_email)
            ->delete();

            DB::table('password_reset_tokens')->insert([
                'email' => $user->user_email,
                'token' => $token,
                'created_at' => Carbon::now()
            ]);
            //End Set Password Url

            //MFA Method
            $mfaMethod = getSetting('mfa_method');
            $otpTokenExpireTime = getSetting('otp_expire_time') ? (int)getSetting('otp_expire_time') : 10;
            $otp = null;
            $otp_expiry = null;
            $base64QRCode = null;
            if ($mfaMethod === 'email') {
                
                $otp = generateToken(8);
                $otp_expiry = now()->addMinutes($otpTokenExpireTime); 

                $user->otp = $otp;
                $user->otp_expires_at = $otp_expiry;
                $user->save();

            } elseif ($mfaMethod === 'google') {

                if (!$user->google2fa_secret) {
                    $qrcodeUrl = $this->generateGoogle2faSecret($user);
            
                    // Save the SVG to a file
                    $uploadId = null;
                    $actionType = 'save';
                    if($qrCodeImageRecord = $user->qrCodeImage){
                        $uploadId = $qrCodeImageRecord->id;
                        $actionType = 'update';
                    }

                    $qrCodeImage = uploadQRcodeImage($user, $qrcodeUrl, $actionType, $uploadId);
                    $base64QRCode = $qrCodeImage->file_url;
                }

            }
            //End MFA Method

            // Send welcome email
            $mailData = [
                'mfaMethod'  => $mfaMethod,
                'otp'        => $otp,
                'otp_expiry' => $otp_expiry,
                'base64QRCode' => $base64QRCode,
                'setPasswordUrl' => $setPasswordUrl
            ];
          /* \Log::info('Sending WelcomeEmail', [
                'mfaMethod'      => $mfaMethod,
                'setPasswordUrl' => $setPasswordUrl,
                'otp'            => $otp,
                'otp_expiry'     => $otp_expiry,
                'base64QRCode'   => $base64QRCode,
            ]);*/
            Mail::to($user->user_email)->send(new WelcomeEmail($user, $mailData));

            //Verification mail sent
            // $user->NotificationSendToVerifyEmail();

            $trustId = $this->getTrustId($request);
            $user->getHospitals()->attach($request->hospital, ['trust_id' => $trustId]);

            if(($user->primary_role != config('constant.roles.booker'))){

                if($request->speciality){
                    $specialities = [
                        $request->speciality => ['sub_speciality_id' => $request->sub_speciality ?? null],
                    ];
                    // Sync specialities with additional pivot data
                    $user->specialityDetail()->sync($specialities);
                }
               
            }

            DB::commit();

            return $this->respondOk([
                'status'   => true,
                'message'   => trans('messages.user_created_and_welcome_email_sent')
            ])->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();

            // dd('Error in UserController::store (' . $e->getCode() . '): ' . $e->getMessage() . ' at line ' . $e->getLine());

            \Log::info('Error in UserController::store (' . $e->getCode() . '): ' . $e->getMessage() . ' at line ' . $e->getLine());
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($uuid)
    {
        try {
            $user_details = [];
            $user = User::where('uuid', $uuid)->withTrashed()->first();

            if ($user) {
                $user_details['uuid']          = $user->uuid;
                $user_details['full_name']     = ucwords($user->full_name);
                $user_details['primary_role']  = $user->primary_role;
                $user_details['role_name']     = $user->role->role_name;
                $user_details['user_email']    = $user->user_email;
                $user_details['phone']         = $user->phone;

                $user_details['hospital'] = $user->getHospitals()->pluck('hospital_name', 'id')->toArray();

                $user_details['trust'] = $user->trusts ? $user->trusts()->value('id') : null;
                $user_details['trust_name'] = $user->trusts ? $user->trusts()->value('trust_name') : null;

                if($user->primary_role != config('constant.roles.booker')){
                    $user_details['speciality']      = $user->specialityDetail()->value('id');
                    $user_details['speciality_name'] = $user->specialityDetail()->value('speciality_name');

                    $user_details['sub_speciality']      =  $user->subSpecialityDetail()->value('id');
                    $user_details['sub_speciality_name'] = $user->subSpecialityDetail()->value('sub_speciality_name');
                }

                $user_details['last_login_at'] = $user->last_login_at ? dateFormat($user->last_login_at,'D, d M Y - h:i A') : null;

                $user_details['created_by']    = $user->createdBy ? $user->createdBy->full_name : null;
                $user_details['deleted_by']    = $user->deletedBy ? $user->deletedBy->full_name : null;
                $user_details['deleted_at']    = $user->deleted_at ? $user->deleted_at->format('d-m-Y h:i A') : null;

            }

            return $this->respondOk([
                'status'   => true,
                'message'   => trans('messages.user_record_retrieved_successfully'),
                'data'      => $user_details,
            ])->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            \Log::info('Error in UserController::show (' . $e->getCode() . '): ' . $e->getMessage() . ' at line ' . $e->getLine());
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, $uuid)
    {
        try {
            DB::beginTransaction();

            $authUser = auth()->user();
            $updatedFields = [];

            $user = User::where('uuid', $uuid)->firstOrFail();
            $userEmail = $user->user_email;

            $currentTrust = $user->getHospitals()->groupBy('trust_id')->value('trust_id');
            $currentHospitalList = $user->getHospitals()->pluck('id')->toArray();

            if($user->primary_role != $request->role){
                $updatedFields['primary_role'] = true;
            }

            if($user->full_name != $request->full_name){
                $updatedFields['full_name'] = true;
            }

            if($user->user_email != $request->user_email){
                $updatedFields['user_email'] = true;
            }

            if ($request->filled('password') && !Hash::check($request->password, $user->password)) {
                $updatedFields['password'] = true;
            }

           $user->update([
                'primary_role' => $request->role,
                'full_name'    => $request->full_name ?? $user->full_name,
                'user_email'   => $request->user_email ?? $user->user_email,
                // 'password'     => $request->filled('password') ? Hash::make($request->password) : $user->password,
            ]);

            $user = User::where('uuid', $uuid)->first();
         
            $trustId = $this->getEditUserTrustId($request, $user);
            $user->getHospitals()->detach();
            $user->getHospitals()->attach($request->hospital, ['trust_id' => $trustId]);

            if($currentTrust != $trustId){
                $updatedFields['trust'] = true;
            }

            $updatedHospitalList = $user->getHospitals()->pluck('id')->toArray();

            if (array_diff($currentHospitalList, $updatedHospitalList) || array_diff($updatedHospitalList, $currentHospitalList)) {

                $updatedFields['hospital'] = true;

            }

            // Sync speciality and sub_speciality
            if($request->role != config('constant.roles.booker')){

                $currentSpecialities = $user->specialityDetail()->pluck('speciality_id')->toArray();

                if (!in_array($request->speciality, $currentSpecialities)) {
                    $updatedFields['speciality'] = true;
                }

                $currentSubSpecialities = $user->subSpecialityDetail()->value('id');
                if($request->sub_speciality != $currentSubSpecialities){
                    $updatedFields['sub_speciality'] = true;
                }

                $newSpeciality = $request->speciality;
                if($newSpeciality){
                    $specialities = [
                        $newSpeciality => ['sub_speciality_id' => $request->sub_speciality ?? null],
                    ];
                    $user->specialityDetail()->sync($specialities);
                }else{
                    $user->specialityDetail()->sync([]);
                }

                if (!in_array($newSpeciality, $currentSpecialities)) {
                    if ($user->rotaSessions()->exists()) {
                        $user->rotaSessions()->sync([]);
                    }
                }
                
            }else{
                $user->specialityDetail()->sync([]);
            }


            if(count($updatedFields) > 0){
                $subject  = trans('messages.notify_subject.admin_updated_own_user');
              
                Mail::to($userEmail)->queue(new UserUpdatedMail($subject, $user,$updatedFields));
            }

            DB::commit();

            return $this->respondOk([
                'status'   => true,
                'message'  => trans('messages.user_updated_successfully')
            ])->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            // dd('Error in UserController::update (' . $e->getCode() . '): ' . $e->getMessage() . ' at line ' . $e->getLine());
            \Log::info('Error in UserController::update (' . $e->getCode() . '): ' . $e->getMessage() . ' at line ' . $e->getLine());
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $uuid)
    {
        try {
            $user = User::where('uuid', $uuid)->firstOrFail();

            if ($user) {

                $authUser = auth()->user();

                $user->delete();

                //Send notification to system admin, trust admin and hospital admin
                $deletedUser = User::onlyTrashed()->where('uuid', $uuid)->first();

                $subject = trans('messages.notify_subject.user_deleted_by_admin',['user_name'=>$deletedUser->full_name,'admin_name'=>$authUser->full_name]);

                //Send mail to deleted user
                Mail::to($deletedUser->user_email)->queue(new UserDeletedMail($subject, $deletedUser, $authUser));
                

                //Send mail and notification to system admin
                if($authUser->primary_role != config('constant.roles.system_admin')){
                    $systemAdmin = User::where('primary_role', config('constant.roles.system_admin'))->select('id', 'full_name', 'user_email')->first();

                    $notification_type = array_search(config('constant.notification_type.user_deleted_by_admin'), config('constant.notification_type'));
                    $messageContent = null;
                    $key = array_search(config('constant.notification_section.announcements'), config('constant.notification_section'));

                    $messageData = [
                            'notification_type' => $notification_type,
                            'section'           => $key,
                            'subject'           => $subject,
                            'message'           => $messageContent,
                            'rota_session'      => null,
                            'created_by'        => $authUser->id,
                            'deletedUser'       => $deletedUser,
                            'authUser'          => $authUser,
                    ];

                    $systemAdmin->notify(new SendNotification($messageData));
                }
                    
                //End Send notification to system admin, trust admin and hospital admin     
            }

            return $this->respondOk([
                'status'   => true,
                'message'   => trans('messages.user_deleted_successfully'),
            ])->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $e) {
            \Log::info('Error in UserController::destroy (' . $e->getCode() . '): ' . $e->getMessage() . ' at line ' . $e->getLine());
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function authUserDestroy(Request $request)
    {
        $request->validate([
            'confirm_password' => ['required', 'string', 'min:8'],
        ],[],[
            'confirm_password' => 'password',
        ]);

        try {
            $authUser = auth()->user();

            $confirmPassword = $request->confirm_password;
            if (!Hash::check($confirmPassword, $authUser->password)) {
                return $this->setStatusCode(500)->respondWithError(trans('messages.invalid_password'));
            }

            //Send notification to system admin, trust admin and hospital admin
            $hospitalIds = $authUser->getHospitals()->pluck('id')->toArray();

            $hospitals = Hospital::whereIn('id',$hospitalIds)->get();

            $uniqueUserIds = collect();

            foreach($hospitals as $hospital) {
                $adminUsers = $hospital->users()
                    ->whereIn('primary_role', [
                        config('constant.roles.trust_admin'),
                        config('constant.roles.hospital_admin')
                    ])
                    ->select('id', 'full_name', 'user_email')
                    ->get();

                $superAdmin = User::where('primary_role', config('constant.roles.system_admin'))
                    ->select('id', 'full_name', 'user_email')
                    ->first();

                if ($superAdmin) {
                    $adminUsers = $adminUsers->concat([$superAdmin]);
                }

                $uniqueUserIds = $uniqueUserIds->merge($adminUsers->pluck('id'));
            }
            $uniqueUserIds = $uniqueUserIds->unique();
            
            $adminUsers = User::select('id', 'full_name', 'user_email')->whereIn('id',$uniqueUserIds->toArray())->get();

            foreach($adminUsers as $user){

                $subject = trans('messages.notify_subject.user_deleted_by_own',['user_name'=>$authUser->full_name]);
                $notification_type = array_search(config('constant.notification_type.user_deleted_by_own'), config('constant.notification_type'));

                $messageContent = null;

                $key = array_search(config('constant.notification_section.announcements'), config('constant.notification_section'));

                $messageData = [
                    'notification_type' => $notification_type,
                    'section'           => $key,
                    'subject'           => $subject,
                    'message'           => $messageContent,
                    'rota_session'      => null,
                    'created_by'        => $authUser->id,
                    'authUser'          => $authUser,
                ];

                $user->notify(new SendNotification($messageData));
            }
            //End Send notification to system admin, trust admin and hospital admin
        
            $authUser->delete();
            auth()->logout();
            JWTAuth::invalidate(JWTAuth::getToken());

            return $this->respondOk([
                'status'   => true,
                'message'   => trans('messages.user_deleted_successfully'),
            ])->setStatusCode(Response::HTTP_OK);

        } catch (\Exception $e) {
            \Log::info('Error in UserController::authUserDestroy (' . $e->getCode() . '): ' . $e->getMessage() . ' at line ' . $e->getLine());
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
        }

    }

    public function exportUserData(Request $request)
    {
        $user = auth()->user();
        return Excel::download(new UsersExport($user), 'users.xlsx');
    }

    private function getTrustId(Request $request)
    {
        $user = Auth::user();
        $tid = $user->getHospitals->value('trust');

        if ($user->is_system_admin) {
            $trustId = $request->trust;
        } elseif ($user->is_trust_admin) {
            $trustId = $request->filled('trust') ? $request->trust : $user->id;
        } elseif ($user->is_hospital_admin) {
            $trustId = $tid;
        }

        return $trustId;
    }

    private function getEditUserTrustId(Request $request, $user)
    {
        $authUser = Auth::user();
        $tid = $user->getHospitals->value('trust');
        if ($authUser->is_system_admin) {
            $trustId = $user ?  $request->trust : $tid;
        } else {
            $trustId = $tid;
        }

        return $trustId;
    }

    public function updateIstos(){

        try {

            DB::beginTransaction();

            $authUser = auth()->user();

            $authUser->is_tos = 0;

            $authUser->save();

            $reponseData = [
                'uuid'                  => $authUser->uuid,
                'full_name'             => $authUser->full_name,
                'user_email'            => $authUser->user_email,
                'primary_role'          => $authUser->primary_role,
                'role'                  => $authUser->role->role_name,
                'trust'                 => $authUser->trusts ? $authUser->trusts()->value('id') : null,
                'trust_name'            => $authUser->trusts ? $authUser->trusts()->value('trust_name') : null,
                'hospital'              => $authUser->getHospitals()->pluck('hospital_name', 'id')->toArray(),
                'is_tos'                => $authUser->is_tos,
            ];

            if($authUser->primary_role != config('constant.roles.booker')){
                $reponseData['speciality']     = $authUser->specialityDetail()->value('speciality_name');
                $reponseData['sub_speciality'] = $authUser->subSpecialityDetail()->value('sub_speciality_name');
            }

            DB::commit();

            return $this->respondOk([
                'status'   => true,
                'message'   => trans('messages.record_updated_successfully'),
                'data'     => $reponseData
            ])->setStatusCode(Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::info('Error in UserController::updateIstos (' . $e->getCode() . '): ' . $e->getMessage() . ' at line ' . $e->getLine());
            return $this->setStatusCode(500)->respondWithError(trans('messages.error_message'));
        }

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
}

