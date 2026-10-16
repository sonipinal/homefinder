<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HomeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    // 6 made-up communities in real North Texas cities
    $communities = [
        ['name' => 'Cedar Ridge',      'city' => 'Frisco'],
        ['name' => 'Willow Creek',     'city' => 'Plano'],
        ['name' => 'Prairie View',     'city' => 'McKinney'],
        ['name' => 'Stonebridge Park', 'city' => 'Prosper'],
        ['name' => 'Bluebonnet Hills', 'city' => 'Celina'],
        ['name' => 'Oak Meadow',       'city' => 'Fort Worth'],
    ];

    foreach ($communities as $data) {
        $community = \App\Models\Community::create($data);

        // 8 homes in each community = 48 homes
        \App\Models\Home::factory()
            ->count(8)
            ->create(['community_id' => $community->id]);
    }
}
}
