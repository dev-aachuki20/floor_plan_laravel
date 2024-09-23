<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class QuarterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $currentYear = Carbon::now()->year;

        $quarters = [
            [
                'quarter_name' => "Quarter 1",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'quarter_name' => "Quarter 2",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'quarter_name' => "Quarter 3",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'quarter_name' => "Quarter 4",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('quarters')->insert($quarters);
    }
}
