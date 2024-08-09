<?php

// app/Console/Commands/ImportRegions.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Province;
use App\Models\City;
use App\Models\Subdistrict;
use Illuminate\Support\Facades\Http;

class ImportRegions extends Command
{
    protected $signature = 'import:regions';
    protected $description = 'Import regions data from API';

    public function handle()
    {
        // Import provinces
        $response = Http::withHeaders([
            'key' => '61188f687d4fd93cb7100b56ff22424b'
        ])->get('https://pro.rajaongkir.com/api/province');

        $provinces = $response->json()['rajaongkir']['results'];

        foreach ($provinces as $province) {
            Province::updateOrCreate(
                ['id' => $province['province_id']],
                ['name' => $province['province']]
            );
        }

        // Import cities
        $response = Http::withHeaders([
            'key' => '61188f687d4fd93cb7100b56ff22424b'
        ])->get('https://pro.rajaongkir.com/api/city');

        $cities = $response->json()['rajaongkir']['results'];

        foreach ($cities as $city) {
            City::updateOrCreate(
                ['id' => $city['city_id']],
                [
                    'province_id' => $city['province_id'],
                    'name' => $city['city_name'],
                    'type' => $city['type'],
                    'postal_code' => $city['postal_code']
                ]
            );
        }

        // Import subdistricts
        $cities = City::all();
        foreach ($cities as $city) {
            $response = Http::withHeaders([
                'key' => '61188f687d4fd93cb7100b56ff22424b'
            ])->get("https://pro.rajaongkir.com/api/subdistrict?city={$city->id}");

            $subdistricts = $response->json()['rajaongkir']['results'];

            foreach ($subdistricts as $subdistrict) {
                Subdistrict::updateOrCreate(
                    ['id' => $subdistrict['subdistrict_id']],
                    [
                        'city_id' => $subdistrict['city_id'],
                        'name' => $subdistrict['subdistrict_name']
                    ]
                );
            }
        }

        $this->info('Regions imported successfully.');
    }
}

