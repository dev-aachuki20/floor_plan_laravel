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
                'user_email'          => 'systemadmin@sobex.io',
                'password'            => bcrypt('123123123'),
                'remember_token'      => null,
                'email_verified_at'   => date('Y-m-d H:i:s'),
            ],
        ];
        foreach($users as $key=>$user){
            $createdUser =  User::create($user);
        }
    }
}
