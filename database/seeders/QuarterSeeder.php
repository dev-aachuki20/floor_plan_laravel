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
                'start_date' => Carbon::create($currentYear, 1, 1),
                'end_date' => Carbon::create($currentYear, 3, 31),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'quarter_name' => "Quarter 2",
                'start_date' => Carbon::create($currentYear, 4, 1),
                'end_date' => Carbon::create($currentYear, 6, 30),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'quarter_name' => "Quarter 3",
                'start_date' => Carbon::create($currentYear, 7, 1),
                'end_date' => Carbon::create($currentYear, 9, 30),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'quarter_name' => "Quarter 4",
                'start_date' => Carbon::create($currentYear, 10, 1),
                'end_date' => Carbon::create($currentYear, 12, 31),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('quarters')->insert($quarters);
    }
}
