<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'primary_role'        => config('constant.roles.system_admin'), 
                'full_name'           => 'System Admin',
                'user_email'          => 'systemadmin@gmail.com',
                'password'            => bcrypt('12345678'),
                'remember_token'      => null,
                'email_verified_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'primary_role'        => config('constant.roles.system_admin'), 
                'full_name'           => 'Speciality Lead',
                'user_email'          => 'specialitylead@gmail.com',
                'password'            => bcrypt('12345678'),
                'remember_token'      => null,
                'email_verified_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'primary_role'        => config('constant.roles.system_admin'), 
                'full_name'           => 'Booker',
                'user_email'          => 'booker@gmail.com',
                'password'            => bcrypt('12345678'),
                'remember_token'      => null,
                'email_verified_at'   => date('Y-m-d H:i:s'),
            ],
        ];
        foreach($users as $key=>$user){
            $createdUser =  User::create($user);
        }
    }
}
