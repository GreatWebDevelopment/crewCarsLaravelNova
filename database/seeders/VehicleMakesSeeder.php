<?php

namespace Database\Seeders;

use App\Models\VehicleMake;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleMakesSeeder extends Seeder
{
    public function run()
    {
        $makes = [
            ['name' => 'Toyota'],
            ['name' => 'Honda'],
            ['name' => 'Ford'],
            ['name' => 'Chevrolet'],
            ['name' => 'Nissan'],
            ['name' => 'BMW'],
            ['name' => 'Mercedes-Benz'],
            ['name' => 'Volkswagen'],
            ['name' => 'Hyundai'],
            ['name' => 'Kia'],
            ['name' => 'Audi'],
            ['name' => 'Lexus'],
            ['name' => 'Subaru'],
            ['name' => 'Mazda'],
            ['name' => 'Dodge'],
            ['name' => 'Ram'],
            ['name' => 'Jeep'],
            ['name' => 'Tesla'],
            ['name' => 'Porsche'],
            ['name' => 'Ferrari'],
            ['name' => 'Lamborghini'],
            ['name' => 'Mitsubishi'],
            ['name' => 'Jaguar'],
            ['name' => 'Land Rover'],
            ['name' => 'Buick'],
            ['name' => 'Chrysler'],
            ['name' => 'Cadillac'],
            ['name' => 'Infiniti'],
            ['name' => 'Lincoln'],
            ['name' => 'Acura'],
            ['name' => 'Volvo'],
            ['name' => 'Mini'],
            ['name' => 'Genesis'],
            ['name' => 'Alfa Romeo'],
            ['name' => 'Fiat'],
            ['name' => 'GMC'],
            ['name' => 'Peugeot'],
            ['name' => 'Renault'],
            ['name' => 'Citroën'],
            ['name' => 'Suzuki'],
        ];

        foreach ($makes as $make) {
            VehicleMake::firstOrCreate($make);
        }
    }
}
