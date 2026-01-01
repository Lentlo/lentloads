<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Listing;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Str;

class ListingSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();

        $listings = [
            // Mobiles
            [
                'category_slug' => 'mobiles-mobile-phones',
                'title' => 'iPhone 14 Pro Max 256GB - Deep Purple',
                'description' => 'Selling my iPhone 14 Pro Max in excellent condition. Comes with original box, charger, and case. Battery health 95%. No scratches or dents. Purchased 6 months ago.',
                'price' => 89999,
                'condition' => 'like_new',
                'brand' => 'Apple',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
            ],
            [
                'category_slug' => 'mobiles-mobile-phones',
                'title' => 'Samsung Galaxy S23 Ultra 512GB',
                'description' => 'Brand new Samsung Galaxy S23 Ultra. Sealed box with warranty. Phantom Black color. Best camera phone with S Pen included.',
                'price' => 124999,
                'condition' => 'new',
                'brand' => 'Samsung',
                'city' => 'Delhi',
                'state' => 'Delhi',
            ],
            [
                'category_slug' => 'mobiles-mobile-phones',
                'title' => 'OnePlus 11 5G - 16GB RAM',
                'description' => 'OnePlus 11 5G in Titan Black. 16GB RAM, 256GB storage. Hasselblad camera. Used for 2 months only. With bill and warranty.',
                'price' => 52999,
                'condition' => 'like_new',
                'brand' => 'OnePlus',
                'city' => 'Bangalore',
                'state' => 'Karnataka',
            ],

            // Vehicles - Cars
            [
                'category_slug' => 'vehicles-cars',
                'title' => 'Maruti Swift VXI 2021 - First Owner',
                'description' => 'Well maintained Maruti Swift VXI petrol. First owner, company serviced. 25000 km driven. New tyres, insurance valid till Dec 2024. White color.',
                'price' => 599000,
                'condition' => 'good',
                'brand' => 'Maruti Suzuki',
                'city' => 'Chennai',
                'state' => 'Tamil Nadu',
            ],
            [
                'category_slug' => 'vehicles-cars',
                'title' => 'Hyundai Creta SX 2022 Diesel',
                'description' => 'Hyundai Creta SX(O) diesel automatic. Polar White color. Panoramic sunroof, ventilated seats. 18000 km only. All original documents available.',
                'price' => 1450000,
                'condition' => 'like_new',
                'brand' => 'Hyundai',
                'city' => 'Pune',
                'state' => 'Maharashtra',
            ],

            // Vehicles - Motorcycles
            [
                'category_slug' => 'vehicles-motorcycles',
                'title' => 'Royal Enfield Classic 350 - Chrome',
                'description' => 'Royal Enfield Classic 350 in Chrome variant. 2023 model. Only 5000 km driven. Comes with saddle bags and leg guards. Perfect condition.',
                'price' => 175000,
                'condition' => 'like_new',
                'brand' => 'Royal Enfield',
                'city' => 'Jaipur',
                'state' => 'Rajasthan',
            ],

            // Property
            [
                'category_slug' => 'property-for-sale-apartments',
                'title' => '3 BHK Apartment in Powai - Lake View',
                'description' => 'Spacious 3 BHK apartment in premium society. 1450 sq ft carpet area. Lake facing with beautiful views. 24x7 security, gym, pool. Ready to move.',
                'price' => 32500000,
                'condition' => 'good',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
            ],
            [
                'category_slug' => 'property-for-rent-apartments',
                'title' => '2 BHK Furnished Flat for Rent - Koramangala',
                'description' => 'Fully furnished 2 BHK in Koramangala 5th Block. Close to metro. Modern kitchen, AC in all rooms, washing machine. Rent 35000 + maintenance.',
                'price' => 35000,
                'price_type' => 'fixed',
                'condition' => 'good',
                'city' => 'Bangalore',
                'state' => 'Karnataka',
            ],

            // Electronics
            [
                'category_slug' => 'electronics-computers-laptops',
                'title' => 'MacBook Pro M2 14-inch 16GB RAM',
                'description' => 'Apple MacBook Pro 14-inch with M2 Pro chip. 16GB RAM, 512GB SSD. Space Gray. AppleCare+ till 2025. Perfect for developers and creators.',
                'price' => 165000,
                'condition' => 'like_new',
                'brand' => 'Apple',
                'city' => 'Hyderabad',
                'state' => 'Telangana',
            ],
            [
                'category_slug' => 'electronics-tvs',
                'title' => 'Sony Bravia 55 inch 4K Smart TV',
                'description' => 'Sony Bravia 55 inch 4K HDR Smart TV. Google TV built-in. Dolby Vision & Atmos. 2 years old, excellent picture quality. With wall mount.',
                'price' => 45000,
                'condition' => 'good',
                'brand' => 'Sony',
                'city' => 'Kolkata',
                'state' => 'West Bengal',
            ],
            [
                'category_slug' => 'electronics-cameras',
                'title' => 'Canon EOS R6 with RF 24-105mm Lens',
                'description' => 'Canon EOS R6 mirrorless camera with RF 24-105mm f/4 lens. Low shutter count. Includes extra battery, SD cards, and camera bag.',
                'price' => 185000,
                'condition' => 'like_new',
                'brand' => 'Canon',
                'city' => 'Delhi',
                'state' => 'Delhi',
            ],

            // Furniture
            [
                'category_slug' => 'furniture-sofa-dining',
                'title' => 'L-Shaped Sectional Sofa - Grey Fabric',
                'description' => '7 seater L-shaped sectional sofa in premium grey fabric. 2 years old. No stains or tears. Very comfortable. Relocating hence selling.',
                'price' => 35000,
                'condition' => 'good',
                'city' => 'Noida',
                'state' => 'Uttar Pradesh',
            ],
            [
                'category_slug' => 'furniture-beds-wardrobes',
                'title' => 'King Size Bed with Hydraulic Storage',
                'description' => 'Solid wood king size bed with hydraulic storage. Includes mattress. Bought from Urban Ladder 1 year ago. Excellent condition.',
                'price' => 28000,
                'condition' => 'like_new',
                'city' => 'Gurgaon',
                'state' => 'Haryana',
            ],

            // Fashion
            [
                'category_slug' => 'fashion-men',
                'title' => 'Raymond Suit - Navy Blue - Size 40',
                'description' => 'Premium Raymond wool blend suit. Navy blue color. Size 40. Worn only twice for events. Dry cleaned and ready. Original price 25000.',
                'price' => 8000,
                'condition' => 'like_new',
                'brand' => 'Raymond',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
            ],

            // Jobs
            [
                'category_slug' => 'jobs-it',
                'title' => 'Senior React Developer - Remote',
                'description' => 'Looking for Senior React Developer with 4+ years experience. Remote work. Good salary. Skills: React, TypeScript, Node.js. Contact for details.',
                'price' => 0,
                'price_type' => 'contact',
                'condition' => 'new',
                'city' => 'Bangalore',
                'state' => 'Karnataka',
            ],

            // Services
            [
                'category_slug' => 'services-home-services',
                'title' => 'Professional Home Deep Cleaning Service',
                'description' => 'Complete home deep cleaning service. Trained staff with all equipment. Sofa cleaning, carpet cleaning, kitchen deep clean. Affordable rates.',
                'price' => 2500,
                'price_type' => 'fixed',
                'condition' => 'new',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
            ],
        ];

        foreach ($listings as $listingData) {
            $category = Category::where('slug', $listingData['category_slug'])->first();

            if (!$category) {
                $this->command->warn("Category not found: " . $listingData['category_slug']);
                continue;
            }

            Listing::create([
                'uuid' => Str::uuid(),
                'user_id' => $user->id,
                'category_id' => $category->id,
                'title' => $listingData['title'],
                'slug' => Str::slug($listingData['title']) . '-' . Str::random(8),
                'description' => $listingData['description'],
                'price' => $listingData['price'],
                'price_type' => $listingData['price_type'] ?? 'negotiable',
                'condition' => $listingData['condition'],
                'brand' => $listingData['brand'] ?? null,
                'city' => $listingData['city'],
                'state' => $listingData['state'],
                'country' => 'India',
                'status' => 'active',
                'published_at' => now(),
                'expires_at' => now()->addDays(30),
            ]);
        }

        $this->command->info('Created ' . count($listings) . ' test listings!');
    }
}
