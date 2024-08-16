<?php

use App\Models\Setting;
use App\Models\Uploads;
use App\Models\User;
use App\Models\RotaSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str as Str;

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

			'password.regex' => 'The :attribute must be at least 8 characters and contain at least one uppercase character, one number, and one special character.',
			'password.regex' => 'The :attribute must be at least 8 characters and contain at least one uppercase character, one number, and one special character.',
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

if (!function_exists('getWithDateTimezone')) {
	function getWithDateTimezone($date)
	{
		$newdate = Carbon::parse($date)->setTimezone(config('app.timezone'))->format('d-m-Y H:i:s');
		return $newdate;
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
	function uploadImage($directory, $file, $folder, $type = "profile", $fileType = "jpg", $actionType = "save", $uploadId = null, $orientation = null)
	{
		$oldFile = null;
		if ($actionType == "save") {
			$upload               		= new Uploads;
		} else {
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


if (!function_exists('getSetting')) {
	function getSetting($key)
	{
		$result = null;
		$setting = Setting::where('key', $key)->where('status', 1)->first();
		if ($setting->type == 'image') {
			$result = $setting->image_url;
		} elseif ($setting->type == 'file') {
			$result = $setting->doc_url;
		} elseif ($setting->type == 'json') {
			$result = $setting->value ? json_decode($setting->value, true) : null;
		} else {
			$result = $setting->value;
		}
		return $result;
	}
}


if (!function_exists('str_limit_custom')) {
	/**
	 * Limit the number of characters in a string.
	 *
	 * @param  string  $value
	 * @param  int  $limit
	 * @param  string  $end
	 * @return string
	 */
	function str_limit_custom($value, $limit = 100, $end = '...')
	{
		return \Illuminate\Support\Str::limit($value, $limit, $end);
	}
}

if (!function_exists('getSvgIcon')) {
	function getSvgIcon($icon)
	{
		return view('components.svg-icons', ['icon' => $icon])->render();
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

if (!function_exists('generateSlug')) {

	function generateSlug($name, $tableName, $ignoreId = null)
	{
		// Convert the name to a slug
		$slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $name));

		// Ensure no multiple hyphens
		$slug = preg_replace('/-+/', '-', $slug);

		// Trim hyphens from both ends
		$slug = trim($slug, '-');

		// Ensure the slug is unique
		$originalSlug = $slug;
		$count = 1;

		$query = \DB::table($tableName)->where('slug', $slug)->whereNull('deleted_at');

		// Ignore the current record if updating
		if ($ignoreId) {
			$query->where('id', '!=', $ignoreId);
		}

		while ($query->exists()) {
			$slug = "{$originalSlug}-{$count}";
			$count++;

			// Update the query to check for the new slug
			$query = \DB::table($tableName)->where('slug', $slug)->whereNull('deleted_at');
			if ($ignoreId) {
				$query->where('id', '!=', $ignoreId);
			}
		}

		return $slug;
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
