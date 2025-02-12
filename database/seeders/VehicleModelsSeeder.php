<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class VehicleModelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $models = [
            'Toyota' => ['Camry', 'Corolla', 'Highlander', 'RAV4', 'Tacoma'],
            'Honda' => ['Accord', 'Civic', 'CR-V', 'Pilot', 'Odyssey'],
            'Ford' => ['F-150', 'Mustang', 'Explorer', 'Escape', 'Bronco'],
        ];

        foreach ($models as $make => $modelList) {
            $makeId = DB::table('vehicleMakes')->where('name', $make)->value('id');

            if (!$makeId) {
                echo "❌ Make '$make' not found! Skipping...\n";
                continue;
            }

            foreach ($modelList as $model) {
                DB::table('vehicleModels')->insert([
                    'makeId' => $makeId,
                    'name' => $model,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
