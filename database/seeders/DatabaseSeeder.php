<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Page;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin',
            'email' => 'admin@lentloads.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create categories
        $categories = [
            [
                'name' => 'Mobiles',
                'slug' => 'mobiles',
                'icon' => 'phone',
                'children' => ['Mobile Phones', 'Tablets', 'Accessories'],
            ],
            [
                'name' => 'Vehicles',
                'slug' => 'vehicles',
                'icon' => 'car',
                'children' => ['Cars', 'Motorcycles', 'Scooters', 'Bicycles', 'Commercial Vehicles'],
            ],
            [
                'name' => 'Property',
                'slug' => 'property',
                'icon' => 'home',
                'children' => ['For Sale: Houses', 'For Sale: Apartments', 'For Rent: Houses', 'For Rent: Apartments', 'Land & Plots'],
            ],
            [
                'name' => 'Electronics & Appliances',
                'slug' => 'electronics',
                'icon' => 'tv',
                'children' => ['TVs', 'Computers & Laptops', 'Cameras', 'Games & Entertainment', 'Kitchen Appliances', 'AC & Coolers'],
            ],
            [
                'name' => 'Furniture',
                'slug' => 'furniture',
                'icon' => 'sofa',
                'children' => ['Sofa & Dining', 'Beds & Wardrobes', 'Home Decor', 'Office Furniture'],
            ],
            [
                'name' => 'Fashion',
                'slug' => 'fashion',
                'icon' => 'shirt',
                'children' => ['Men', 'Women', 'Kids', 'Accessories'],
            ],
            [
                'name' => 'Jobs',
                'slug' => 'jobs',
                'icon' => 'briefcase',
                'children' => ['IT', 'Sales & Marketing', 'Finance', 'Healthcare', 'Driver', 'Other'],
            ],
            [
                'name' => 'Services',
                'slug' => 'services',
                'icon' => 'wrench',
                'children' => ['Electronics Repair', 'Home Services', 'Education', 'Health & Beauty'],
            ],
        ];

        foreach ($categories as $index => $categoryData) {
            $parent = Category::create([
                'name' => $categoryData['name'],
                'slug' => $categoryData['slug'],
                'order' => $index,
                'is_active' => true,
                'is_featured' => true,
            ]);

            foreach ($categoryData['children'] as $childIndex => $childName) {
                Category::create([
                    'name' => $childName,
                    'slug' => \Str::slug($childName),
                    'parent_id' => $parent->id,
                    'order' => $childIndex,
                    'is_active' => true,
                ]);
            }
        }

        // Create pages
        $pages = [
            ['title' => 'About Us', 'slug' => 'about', 'content' => '<h1>About Lentloads</h1><p>Welcome to Lentloads Marketplace...</p>'],
            ['title' => 'Terms of Service', 'slug' => 'terms', 'content' => '<h1>Terms of Service</h1><p>By using Lentloads...</p>'],
            ['title' => 'Privacy Policy', 'slug' => 'privacy', 'content' => '<h1>Privacy Policy</h1><p>Your privacy is important to us...</p>'],
            ['title' => 'Help Center', 'slug' => 'help', 'content' => '<h1>Help Center</h1><p>Find answers to common questions...</p>'],
            ['title' => 'Safety Tips', 'slug' => 'safety', 'content' => '<h1>Safety Tips</h1><p>Stay safe while buying and selling...</p>'],
            ['title' => 'Contact Us', 'slug' => 'contact', 'content' => '<h1>Contact Us</h1><p>Get in touch with us...</p>'],
        ];

        foreach ($pages as $page) {
            Page::create($page);
        }

        // Create settings
        $settings = [
            ['key' => 'site_name', 'value' => 'Lentloads Marketplace', 'group' => 'general'],
            ['key' => 'site_description', 'value' => 'Buy and sell anything locally', 'group' => 'general'],
            ['key' => 'contact_email', 'value' => 'support@lentloads.com', 'group' => 'contact'],
            ['key' => 'contact_phone', 'value' => '+91 1234567890', 'group' => 'contact'],
            ['key' => 'listing_expiry_days', 'value' => '30', 'type' => 'integer', 'group' => 'listings'],
            ['key' => 'max_images_per_listing', 'value' => '10', 'type' => 'integer', 'group' => 'listings'],
            ['key' => 'require_approval', 'value' => '1', 'type' => 'boolean', 'group' => 'listings'],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin login: admin@lentloads.com / password');
    }
}
