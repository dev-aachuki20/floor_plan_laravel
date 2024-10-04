<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $settings = [
            [
                'key'           => 'first_reminder',
                'value'         => 35,
                'type'          => 'number',
                'display_name'  => 'First Reminder',
                'group'         => 'site',
                'details'       => null,
                'status'        => 1,
                'created_at'    => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                'key'           => 'first_reminder_time',
                'value'         => '00:00',
                'type'          => 'text',
                'display_name'  => 'First Reminder Time',
                'group'         => 'site',
                'details'       => null,
                'status'        => 1,
                'created_at'    => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                'key'           => 'final_reminder',
                'value'         => 28,
                'type'          => 'number',
                'display_name'  => 'Final Reminder',
                'group'         => 'site',
                'details'       => null,
                'status'        => 1,
                'created_at'    => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                'key'           => 'final_reminder_time',
                'value'         => '00:00',
                'type'          => 'text',
                'display_name'  => 'Final Reminder Time',
                'group'         => 'site',
                'details'       => null,
                'status'        => 1,
                'created_at'    => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                'key'           => 'assign_backup_speciality',
                'value'         => 14,
                'type'          => 'number',
                'display_name'  => 'Assign Backup Speciality',
                'group'         => 'site',
                'details'       => null,
                'status'        => 1,
                'created_at'    => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                'key'           => 'assign_backup_speciality_time',
                'value'         => '00:00',
                'type'          => 'text',
                'display_name'  => 'Assign Backup Speciality Time',
                'group'         => 'site',
                'details'       => null,
                'status'        => 1,
                'created_at'    => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                'key'           => 'session_closed',
                'value'         => 7,
                'type'          => 'number',
                'display_name'  => 'Session Closed',
                'group'         => 'site',
                'details'       => null,
                'status'        => 1,
                'created_at'    => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                'key'           => 'session_closed_time',
                'value'         => '00:00',
                'type'          => 'text',
                'display_name'  => 'Session Closed Time',
                'group'         => 'site',
                'details'       => null,
                'status'        => 1,
                'created_at'    => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                'key'           => 'allow_registration',
                'value'         => 1,
                'type'          => 'number',
                'display_name'  => 'Allow Registration',
                'group'         => 'site',
                'details'       => null,
                'status'        => 1,
                'created_at'    => Carbon::now()->format('Y-m-d H:i:s'),
            ],

            [
                'key'           => 'lifespan_token',
                'value'         => 60,
                'type'          => 'number',
                'display_name'  => 'Lifespan Of Token (Minutes)',
                'group'         => 'site',
                'details'       => null,
                'status'        => 1,
                'created_at'    => Carbon::now()->format('Y-m-d H:i:s'),
            ],

            [
                'key'           => 'mfa_method',
                'value'         => 'email', // email, google
                'type'          => 'text',
                'display_name'  => 'MFA Method',
                'group'         => 'site',
                'details'       => null,
                'status'        => 1,
                'created_at'    => Carbon::now()->format('Y-m-d H:i:s'),
            ],

            [
                'key'           => 'mfa_time_duration',
                'value'         => '720',//In hours
                'type'          => 'number',
                'display_name'  => 'MFA Time Duration',
                'group'         => 'site',
                'details'       => 'Time should be in hours',
                'status'        => 1,
                'created_at'    => Carbon::now()->format('Y-m-d H:i:s'),
            ],

            [
                'key'           => 'otp_expire_time',
                'value'         => '10',
                'type'          => 'number',
                'display_name'  => 'OTP Expire Time',
                'group'         => 'site',
                'details'       => 'Time should be in minutes',
                'status'        => 1,
                'created_at'    => Carbon::now()->format('Y-m-d H:i:s'),
            ],

             

        ];

        Setting::insert($settings);
    }
}
