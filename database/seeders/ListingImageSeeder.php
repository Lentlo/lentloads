<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\ListingImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ListingImageSeeder extends Seeder
{
    /**
     * Real product images from Unsplash (free to use)
     * Using specific search terms for each listing type
     */
    protected $imageUrls = [
        // Listing 1: iPhone 14 Pro Max
        1 => [
            'https://images.unsplash.com/photo-1678685888221-cda773a3dcdb?w=800&q=80', // iPhone 14 Pro
            'https://images.unsplash.com/photo-1695048065319-e90c62def21e?w=800&q=80', // iPhone back
        ],
        // Listing 2: Samsung Galaxy S23 Ultra
        2 => [
            'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=800&q=80', // Samsung phone
            'https://images.unsplash.com/photo-1585060544812-6b45742d762f?w=800&q=80', // Samsung display
        ],
        // Listing 3: OnePlus 11 5G
        3 => [
            'https://images.unsplash.com/photo-1546054454-aa26e2b734c7?w=800&q=80', // Android phone
            'https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=800&q=80', // Phone in hand
        ],
        // Listing 4: Maruti Swift
        4 => [
            'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?w=800&q=80', // White hatchback
            'https://images.unsplash.com/photo-1502877338535-766e1452684a?w=800&q=80', // Car interior
            'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?w=800&q=80', // Car on road
        ],
        // Listing 5: Hyundai Creta
        5 => [
            'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?w=800&q=80', // White SUV
            'https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=800&q=80', // SUV front
            'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?w=800&q=80', // Car side
        ],
        // Listing 6: Royal Enfield
        6 => [
            'https://images.unsplash.com/photo-1558981806-ec527fa84c39?w=800&q=80', // Classic motorcycle
            'https://images.unsplash.com/photo-1558980664-769d59546b3d?w=800&q=80', // Motorcycle detail
            'https://images.unsplash.com/photo-1609630875171-b1321377ee65?w=800&q=80', // Bike on road
        ],
        // Listing 7: 3 BHK Apartment Powai
        7 => [
            'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800&q=80', // Living room
            'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&q=80', // Bedroom
            'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=800&q=80', // Kitchen
            'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=800&q=80', // Building exterior
        ],
        // Listing 8: 2 BHK Furnished Flat
        8 => [
            'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800&q=80', // Apartment living
            'https://images.unsplash.com/photo-1493809842364-78817add7ffb?w=800&q=80', // Modern room
            'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=800&q=80', // Bathroom
        ],
        // Listing 9: MacBook Pro M2
        9 => [
            'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=800&q=80', // MacBook
            'https://images.unsplash.com/photo-1541807084-5c52b6b3adef?w=800&q=80', // MacBook open
            'https://images.unsplash.com/photo-1611186871348-b1ce696e52c9?w=800&q=80', // MacBook workspace
        ],
        // Listing 10: Sony Bravia 55 inch TV
        10 => [
            'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?w=800&q=80', // Smart TV
            'https://images.unsplash.com/photo-1558888401-3cc1de77652d?w=800&q=80', // TV in room
        ],
        // Listing 11: Canon EOS R6
        11 => [
            'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=800&q=80', // DSLR camera
            'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=800&q=80', // Camera lens
            'https://images.unsplash.com/photo-1617005082133-548c4dd27f35?w=800&q=80', // Camera setup
        ],
        // Listing 12: L-Shaped Sofa
        12 => [
            'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=800&q=80', // Grey sofa
            'https://images.unsplash.com/photo-1493663284031-b7e3aefcae8e?w=800&q=80', // Living room sofa
            'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=800&q=80', // Sofa detail
        ],
    ];

    public function run(): void
    {
        $this->command->info('Downloading and adding real images to listings...');

        foreach ($this->imageUrls as $listingId => $urls) {
            $listing = Listing::find($listingId);
            if (!$listing) {
                $this->command->warn("Listing {$listingId} not found, skipping...");
                continue;
            }

            // Delete existing images
            foreach ($listing->images as $image) {
                $image->delete();
            }

            $this->command->info("Processing listing {$listingId}: {$listing->title}");

            foreach ($urls as $index => $url) {
                try {
                    // Download image
                    $imageContent = file_get_contents($url);
                    if (!$imageContent) {
                        $this->command->warn("  Failed to download: {$url}");
                        continue;
                    }

                    // Generate filename
                    $filename = Str::uuid() . '.jpg';
                    $directory = "listings/{$listingId}";
                    $path = "{$directory}/{$filename}";
                    $thumbnailPath = "{$directory}/thumb_{$filename}";
                    $mediumPath = "{$directory}/medium_{$filename}";

                    // Ensure directory exists
                    Storage::disk('public')->makeDirectory($directory);

                    // Save original
                    Storage::disk('public')->put($path, $imageContent);

                    // For thumbnails, we'll just save the same image
                    // (In production, you'd use Intervention Image to resize)
                    Storage::disk('public')->put($thumbnailPath, $imageContent);
                    Storage::disk('public')->put($mediumPath, $imageContent);

                    // Create database record
                    ListingImage::create([
                        'listing_id' => $listingId,
                        'path' => $path,
                        'thumbnail' => $thumbnailPath,
                        'medium' => $mediumPath,
                        'original_name' => $filename,
                        'size' => strlen($imageContent),
                        'mime_type' => 'image/jpeg',
                        'order' => $index,
                        'is_primary' => $index === 0,
                    ]);

                    $this->command->info("  Added image " . ($index + 1) . "/" . count($urls));
                } catch (\Exception $e) {
                    $this->command->error("  Error: " . $e->getMessage());
                }
            }
        }

        $this->command->info('Done! Images added to all listings.');
    }
}
