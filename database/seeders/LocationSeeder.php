<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\State;
use App\Models\City;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        // Create India
        $india = Country::firstOrCreate(
            ['name' => 'India'],
            ['code' => 'IN', 'is_active' => true]
        );

        // Indian states with their major cities
        $statesAndCities = [
            'Maharashtra' => [
                ['name' => 'Mumbai', 'latitude' => 19.0760, 'longitude' => 72.8777, 'is_popular' => true],
                ['name' => 'Pune', 'latitude' => 18.5204, 'longitude' => 73.8567, 'is_popular' => true],
                ['name' => 'Nagpur', 'latitude' => 21.1458, 'longitude' => 79.0882, 'is_popular' => false],
                ['name' => 'Thane', 'latitude' => 19.2183, 'longitude' => 72.9781, 'is_popular' => false],
                ['name' => 'Nashik', 'latitude' => 19.9975, 'longitude' => 73.7898, 'is_popular' => false],
            ],
            'Delhi' => [
                ['name' => 'New Delhi', 'latitude' => 28.6139, 'longitude' => 77.2090, 'is_popular' => true],
                ['name' => 'Delhi', 'latitude' => 28.7041, 'longitude' => 77.1025, 'is_popular' => true],
            ],
            'Karnataka' => [
                ['name' => 'Bangalore', 'latitude' => 12.9716, 'longitude' => 77.5946, 'is_popular' => true],
                ['name' => 'Mysore', 'latitude' => 12.2958, 'longitude' => 76.6394, 'is_popular' => false],
                ['name' => 'Mangalore', 'latitude' => 12.9141, 'longitude' => 74.8560, 'is_popular' => false],
            ],
            'Tamil Nadu' => [
                ['name' => 'Chennai', 'latitude' => 13.0827, 'longitude' => 80.2707, 'is_popular' => true],
                ['name' => 'Coimbatore', 'latitude' => 11.0168, 'longitude' => 76.9558, 'is_popular' => false],
                ['name' => 'Madurai', 'latitude' => 9.9252, 'longitude' => 78.1198, 'is_popular' => false],
            ],
            'Telangana' => [
                ['name' => 'Hyderabad', 'latitude' => 17.3850, 'longitude' => 78.4867, 'is_popular' => true],
                ['name' => 'Secunderabad', 'latitude' => 17.4399, 'longitude' => 78.4983, 'is_popular' => false],
            ],
            'West Bengal' => [
                ['name' => 'Kolkata', 'latitude' => 22.5726, 'longitude' => 88.3639, 'is_popular' => true],
                ['name' => 'Howrah', 'latitude' => 22.5958, 'longitude' => 88.2636, 'is_popular' => false],
            ],
            'Gujarat' => [
                ['name' => 'Ahmedabad', 'latitude' => 23.0225, 'longitude' => 72.5714, 'is_popular' => true],
                ['name' => 'Surat', 'latitude' => 21.1702, 'longitude' => 72.8311, 'is_popular' => true],
                ['name' => 'Vadodara', 'latitude' => 22.3072, 'longitude' => 73.1812, 'is_popular' => false],
                ['name' => 'Rajkot', 'latitude' => 22.3039, 'longitude' => 70.8022, 'is_popular' => false],
            ],
            'Rajasthan' => [
                ['name' => 'Jaipur', 'latitude' => 26.9124, 'longitude' => 75.7873, 'is_popular' => true],
                ['name' => 'Jodhpur', 'latitude' => 26.2389, 'longitude' => 73.0243, 'is_popular' => false],
                ['name' => 'Udaipur', 'latitude' => 24.5854, 'longitude' => 73.7125, 'is_popular' => false],
            ],
            'Uttar Pradesh' => [
                ['name' => 'Lucknow', 'latitude' => 26.8467, 'longitude' => 80.9462, 'is_popular' => true],
                ['name' => 'Noida', 'latitude' => 28.5355, 'longitude' => 77.3910, 'is_popular' => true],
                ['name' => 'Kanpur', 'latitude' => 26.4499, 'longitude' => 80.3319, 'is_popular' => false],
                ['name' => 'Agra', 'latitude' => 27.1767, 'longitude' => 78.0081, 'is_popular' => false],
                ['name' => 'Varanasi', 'latitude' => 25.3176, 'longitude' => 82.9739, 'is_popular' => false],
                ['name' => 'Ghaziabad', 'latitude' => 28.6692, 'longitude' => 77.4538, 'is_popular' => false],
            ],
            'Kerala' => [
                ['name' => 'Kochi', 'latitude' => 9.9312, 'longitude' => 76.2673, 'is_popular' => true],
                ['name' => 'Thiruvananthapuram', 'latitude' => 8.5241, 'longitude' => 76.9366, 'is_popular' => false],
                ['name' => 'Kozhikode', 'latitude' => 11.2588, 'longitude' => 75.7804, 'is_popular' => false],
            ],
            'Punjab' => [
                ['name' => 'Chandigarh', 'latitude' => 30.7333, 'longitude' => 76.7794, 'is_popular' => true],
                ['name' => 'Ludhiana', 'latitude' => 30.9010, 'longitude' => 75.8573, 'is_popular' => false],
                ['name' => 'Amritsar', 'latitude' => 31.6340, 'longitude' => 74.8723, 'is_popular' => false],
            ],
            'Haryana' => [
                ['name' => 'Gurgaon', 'latitude' => 28.4595, 'longitude' => 77.0266, 'is_popular' => true],
                ['name' => 'Faridabad', 'latitude' => 28.4089, 'longitude' => 77.3178, 'is_popular' => false],
            ],
            'Madhya Pradesh' => [
                ['name' => 'Indore', 'latitude' => 22.7196, 'longitude' => 75.8577, 'is_popular' => true],
                ['name' => 'Bhopal', 'latitude' => 23.2599, 'longitude' => 77.4126, 'is_popular' => false],
            ],
            'Bihar' => [
                ['name' => 'Patna', 'latitude' => 25.5941, 'longitude' => 85.1376, 'is_popular' => false],
            ],
            'Odisha' => [
                ['name' => 'Bhubaneswar', 'latitude' => 20.2961, 'longitude' => 85.8245, 'is_popular' => false],
            ],
            'Andhra Pradesh' => [
                ['name' => 'Visakhapatnam', 'latitude' => 17.6868, 'longitude' => 83.2185, 'is_popular' => false],
                ['name' => 'Vijayawada', 'latitude' => 16.5062, 'longitude' => 80.6480, 'is_popular' => false],
            ],
            'Goa' => [
                ['name' => 'Panaji', 'latitude' => 15.4909, 'longitude' => 73.8278, 'is_popular' => false],
            ],
        ];

        foreach ($statesAndCities as $stateName => $cities) {
            $state = State::firstOrCreate(
                ['name' => $stateName, 'country_id' => $india->id],
                ['is_active' => true]
            );

            foreach ($cities as $cityData) {
                City::firstOrCreate(
                    ['name' => $cityData['name'], 'state_id' => $state->id],
                    [
                        'latitude' => $cityData['latitude'],
                        'longitude' => $cityData['longitude'],
                        'is_active' => true,
                        'is_popular' => $cityData['is_popular'],
                        'listings_count' => 0,
                    ]
                );
            }
        }

        $this->command->info('Seeded ' . City::count() . ' Indian cities!');
    }
}
