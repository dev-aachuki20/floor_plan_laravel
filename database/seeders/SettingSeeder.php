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
                'key'           => 'allow_registration',
                'value'         => 1,
                'type'          => 'number',
                'display_name'  => 'Allow Registration',
                'group'         => 'site',
                'details'       => null,
                'status'        => 1,
                'created_at'    => Carbon::now()->format('Y-m-d H:i:s'),
            ],
           
        ];

        Setting::insert($settings);
    }
}
