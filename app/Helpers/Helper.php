<?php

use App\Models\Setting;
use App\Models\Uploads;
use App\Models\User;
use App\Models\RotaSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str as Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

if (!function_exists('getCommonValidationRuleMsgs')) {
	function getCommonValidationRuleMsgs()
	{
		return [
			'currentpassword.required' => 'The current password is required.',
			'password.required' => 'The new password is required.',
			'password.min' => 'The new password must be at least 8 characters',
			'password.different' => 'The new password and current password must be different.',
			'password.confirmed' => 'The password confirmation does not match.',
			'password_confirmation.required' => 'The new password confirmation is required.',
			'password_confirmation.min' => 'The new password confirmation must be at least 8 characters',
			'email.required' => 'Please enter email address.',
			'email.email' => 'Email is not valid. Enter email address for example test@gmail.com',
			'email.exists' => "Please Enter Valid Registered Email!",
			'password_confirmation.same' => 'The confirm password and new password must match.',

			'password.regex' => 'The :attribute must be at least 8 characters long, contain at least one lowercase letter, one uppercase letter, one number, one special character, and must not contain any spaces.',
			'password_confirmation.regex' => 'The :attribute must be at least 8 characters long, contain at least one lowercase letter, one uppercase letter, one number, one special character, and must not contain any spaces.',
		];
	}
}

if (!function_exists('generateRandomString')) {
	function generateRandomString($length = 20)
	{
		$randomString = Str::random($length);
		return $randomString;
	}
}


if (!function_exists('getSetting')) {
	function getSetting($key)
	{
		$result = null;
		$setting = Setting::where('key', $key)->where('status', 1)->first();
		if($setting){
			if ($setting->type == 'image') {
				$result = $setting->image_url;
			} elseif ($setting->type == 'file') {
				$result = $setting->doc_url;
			} elseif ($setting->type == 'json') {
				$result = $setting->value ? json_decode($setting->value, true) : null;
			} else {
				$result = $setting->value;
			}
		}
		
		return $result;
	}
}

if (!function_exists('dateFormat')) {
	function dateFormat($date, $format = '')
	{
		$startDate = Carbon::parse($date);
		$formattedDate = $startDate->format($format);
		return $formattedDate;
	}
}

if (!function_exists('calculateRotaTableStatistics')) {

	function calculateRotaTableStatistics($hospital_id,$date,$role=null)
	{
		$rolesId = [
			config('constant.roles.speciality_lead'),
			config('constant.roles.staff_coordinator'),
			config('constant.roles.anesthetic_lead'),
		];


		$totaRotaSession = RotaSession::whereDate('week_day_date',$date)->where('hospital_id',$hospital_id)->where('speciality_id','!=',config('constant.unavailable_speciality_id'))->whereNotNull('speciality_id')->count();

        $totalConfirmedUsers = RotaSession::whereHas('users', function ($query) use ($rolesId) {
            $query->whereIn('rota_session_users.role_id', $rolesId)
                  ->where('rota_session_users.status', 1);
        })
        ->whereDate('week_day_date', $date)
        ->where('hospital_id', $hospital_id)
        ->whereNotNull('speciality_id')
        ->where('speciality_id', '!=', config('constant.unavailable_speciality_id'))
        ->withCount(['users as confirmed_users_count' => function ($query) use ($rolesId) {
            $query->whereIn('rota_session_users.role_id', $rolesId)
                  ->where('rota_session_users.status', 1);
        }])
        ->get()
        ->sum('confirmed_users_count');

		if($role){

            $totalConfirmedUsers = RotaSession::whereHas('users', function ($query) use ($role) {
                $query->where('rota_session_users.role_id', $role)
                      ->where('rota_session_users.status', 1);
            })
            ->whereDate('week_day_date', $date)
            ->where('hospital_id', $hospital_id)
            ->whereNotNull('speciality_id')
            ->where('speciality_id', '!=', config('constant.unavailable_speciality_id'))
            ->withCount(['users as confirmed_users_count' => function ($query) use ($role) {
                $query->where('rota_session_users.role_id', $role)
                      ->where('rota_session_users.status', 1);
            }])
            ->get()
            ->sum('confirmed_users_count');

		}else{
			$totaRotaSession = (int)$totaRotaSession * 3;
		}

		$result = $totalConfirmedUsers > 0 ? round(($totalConfirmedUsers / $totaRotaSession) * 100, 2) : 0;

		return $result;
	}
}

if (!function_exists('rotaTableReportStatistics')) {

	function rotaTableReportStatistics($hospital_id,$week_days,$role=null)
	{
		$rolesId = [
			config('constant.roles.speciality_lead'),
			config('constant.roles.staff_coordinator'),
			config('constant.roles.anesthetic_lead'),
		];

		$totaRotaSession = RotaSession::whereIn('week_day_date',$week_days)->where('hospital_id',$hospital_id)->where('speciality_id','!=',config('constant.unavailable_speciality_id'))->whereNotNull('speciality_id')->count();

        $totalConfirmedUsers = RotaSession::whereHas('users', function ($query) use ($rolesId) {
            $query->whereIn('rota_session_users.role_id', $rolesId)
                  ->where('rota_session_users.status', 1);
        })
        ->whereIn('week_day_date', $week_days)
        ->where('hospital_id', $hospital_id)
        ->whereNotNull('speciality_id')
        ->where('speciality_id', '!=', config('constant.unavailable_speciality_id'))
        ->withCount(['users as confirmed_users_count' => function ($query) use ($rolesId) {
            $query->whereIn('rota_session_users.role_id', $rolesId)
                  ->where('rota_session_users.status', 1);
        }])
        ->get()
        ->sum('confirmed_users_count');


		if($role){

            $totalConfirmedUsers = RotaSession::whereHas('users', function ($query) use ($role) {
                $query->where('rota_session_users.role_id', $role)
                    ->where('rota_session_users.status', 1);
            })
            ->whereIn('week_day_date', $week_days)
            ->where('hospital_id', $hospital_id)
            ->whereNotNull('speciality_id')
            ->where('speciality_id', '!=', config('constant.unavailable_speciality_id'))
            ->withCount(['users as confirmed_users_count' => function ($query) use ($role) {
                $query->where('rota_session_users.role_id', $role)
                    ->where('rota_session_users.status', 1);
            }])
            ->get()
            ->sum('confirmed_users_count');

		}else{
			$totaRotaSession = (int)$totaRotaSession * 3;
		}

		$result = $totalConfirmedUsers > 0 ? round(($totalConfirmedUsers / $totaRotaSession) * 100, 2) : 0;

		return $result;
	}
}




if (!function_exists('sendNotification')) {
    function sendNotification($user_id, $subject, $message, $section, $notification_type = null, $data = null)
    {
        try {
        // 	$firebaseToken = User::where('is_active', 1)->where('id', $user_id)->whereNotNull('device_token')->pluck('device_token')->all();

			$firebaseToken = User::where('id', $user_id)->whereNotNull('device_token')->pluck('device_token')->all();


			\Log::info(['firebaseToken' => $firebaseToken,'user_id'=>$user_id]);

			$response = null;
			if($firebaseToken){
				$SERVER_API_KEY = env('FIREBASE_KEY');

				\Log::info(['SERVER_API_KEY' => $SERVER_API_KEY]);

				$notification = [
					"title" => $subject,
					"body" 	=> $message,
					"sound" => "default",
					"alert" => "New"
				];

				$bodydata = [
					"title"=> $subject,
					"body" => $message,
					"notification_id" => $data['notification_id'] ?? null,
					"data" => $data,
					"type" => $section,
				];

				$data = [
					"registration_ids"	=> $firebaseToken,
					"notification" 		=> $notification,
					"priority"			=> "high",
					"contentAvailable" 	=> true,
					"data" 				=> $bodydata
				];
				$encodedData = json_encode($data);
				$headers = [
					'Authorization: key=' . $SERVER_API_KEY,
					'Content-Type: application/json',
				];
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
				$response = curl_exec($ch);
			}
			\Log::info('Response ' . $response);
			return $response;
		} catch (\Exception $e) {
			\Log::info($e->getMessage().' '.$e->getFile().' '.$e->getCode());
		}
    }
}

if (!function_exists('determineQuarter')) {

	function determineQuarter(Carbon $date): int
	{
		$month = $date->month;

		if ($month >= 1 && $month <= 3) {
			return 1;
		} elseif ($month >= 4 && $month <= 6) {
			return 2;
		} elseif ($month >= 7 && $month <= 9) {
			return 3;
		} else {
			return 4;
		}
	}

}

if (!function_exists('getQuarterDates')) {
	function getQuarterDates($quarterNo, $year)
	{
		$quarters = [
			1 => ['start' => '01-01', 'end' => '03-31'],
			2 => ['start' => '04-01', 'end' => '06-30'],
			3 => ['start' => '07-01', 'end' => '09-30'],
			4 => ['start' => '10-01', 'end' => '12-31'],
		];

		if (!isset($quarters[$quarterNo])) {
			throw new \InvalidArgumentException('Invalid quarter number.');
		}

		$start = Carbon::create($year, ...explode('-', $quarters[$quarterNo]['start']));
		$end = Carbon::create($year, ...explode('-', $quarters[$quarterNo]['end']));

		return [$start, $end];
	}
}

if (!function_exists('isDateInQuarter')) {
    function isDateInQuarter($date, $quarterStartDate, $quarterEndDate) {
        $date = Carbon::parse($date);
        $quarterStart = Carbon::parse($quarterStartDate);
        $quarterEnd = Carbon::parse($quarterEndDate);

        return $date->between($quarterStart, $quarterEnd);
    }
}

if (!function_exists('generateOtp')) {

	function generateOtp()
	{
		// Generate a 8-digit OTP
		$otp = str_pad(rand(0, 999999), 8, '0', STR_PAD_LEFT);
		
		return $otp;
	}
}

if (!function_exists('generateToken')) {
    function generateToken($length = 8) {
		$digits    = '0123456789';
		$lowercase = 'abcdefghijklmnopqrstuvwxyz';
		$uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

		$randomString = $digits[rand(0, strlen($digits) - 1)]
					. $lowercase[rand(0, strlen($lowercase) - 1)]
					. $uppercase[rand(0, strlen($uppercase) - 1)];

		$characters = $digits . $lowercase . $uppercase;
		$charactersLength = strlen($characters);

		for ($i = 3; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}

		return str_shuffle($randomString);
    }
}

if (!function_exists('uploadImage')) {
	/**
	 * Upload Image.
	 *
	 * @param array $input
	 *
	 * @return array $input
	 */
	function uploadImage($directory, $file, $folder, $type="profile", $fileType="jpg",$actionType="save",$uploadId=null,$orientation=null)
	{
		$oldFile = null;
        if($actionType == "save"){
			$upload               		= new Uploads;
		}else{
			$upload               		= Uploads::find($uploadId);
			$oldFile = $upload->file_path;
		}
        $upload->file_path      	= $file->store($folder, 'public');
		$upload->extension      	= $file->getClientOriginalExtension();
		$upload->original_file_name = $file->getClientOriginalName();
		$upload->type 				= $type;
		$upload->file_type 			= $fileType;
		$upload->orientation 		= $orientation;
		$response             		= $directory->uploads()->save($upload);
        // delete old file
        if ($oldFile) {
            Storage::disk('public')->delete($oldFile);
        }

		return $upload;
	}
}

if (!function_exists('deleteFile')) {
	/**
	 * Destroy Old Image.	 *
	 * @param int $id
	 */
	function deleteFile($upload_id)
	{
		$upload = Uploads::find($upload_id);
		Storage::disk('public')->delete($upload->file_path);
		$upload->delete();
		return true;
	}
}

if (!function_exists('uploadQRcodeImage')) {
	/**
	 * Upload Image.
	 *
	 * @param array $input
	 *
	 * @return array $input
	 */

	function uploadQRcodeImage($user, $secret, $actionType = "save", $uploadId = null)
	{
		$oldFile = null;

        $issuer = config('app.name');  
        $accountName = $user->user_email;
      
        $qrcodeUrl = "otpauth://totp/{$issuer}:{$accountName}?secret={$secret}&issuer={$issuer}&algorithm=SHA1&digits=6&period=30";

        // Generate QR code image using Simple QrCode
        $qrCodeImagePath = 'qr_codes/qr_code_'.$user->uuid. '.png'; 
        $filePath = storage_path('app/public/' . $qrCodeImagePath); 
        
        if (!file_exists(dirname($filePath))) {
			mkdir(dirname($filePath), 0755, true); 
		}
        QrCode::size(300)->format('png')->generate($qrcodeUrl, $filePath);
                

		if ($actionType == "save") {
			$upload = new Uploads;
		} else {
			$upload = Uploads::find($uploadId);
			$oldFile = $upload->file_path;
		}

		$upload->file_path = $qrCodeImagePath; 
		$upload->extension = pathinfo($qrCodeImagePath, PATHINFO_EXTENSION); 
		$upload->original_file_name = basename($qrCodeImagePath); 
		$upload->type = 'qr_code'; 
		$upload->file_type = 'image/png'; 
		// $upload->orientation = $orientation; // Uncomment if needed

		$response = $user->uploads()->save($upload);

		// if ($oldFile) {
		// 	Storage::disk('public')->delete($oldFile);
		// }

		return $upload;
	}

}





