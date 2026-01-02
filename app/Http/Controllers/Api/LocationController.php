<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\Listing;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function countries()
    {
        $countries = Country::active()->orderBy('name')->get();

        return $this->successResponse($countries);
    }

    public function states($countryId)
    {
        $states = State::where('country_id', $countryId)
            ->active()
            ->orderBy('name')
            ->get();

        return $this->successResponse($states);
    }

    public function cities(Request $request, $stateId)
    {
        $query = City::where('state_id', $stateId)->active();

        if ($request->filled('q')) {
            $query->where('name', 'like', "%{$request->q}%");
        }

        $cities = $query->orderBy('name')->get();

        return $this->successResponse($cities);
    }

    public function popularCities()
    {
        $cities = City::with('state:id,name')
            ->popular()
            ->active()
            ->orderByDesc('listings_count')
            ->limit(20)
            ->get();

        return $this->successResponse($cities);
    }

    public function searchCities(Request $request)
    {
        $query = $request->input('q', '');

        if (strlen($query) < 2) {
            return $this->successResponse([]);
        }

        $cities = City::with('state:id,name')
            ->where('name', 'like', "%{$query}%")
            ->active()
            ->orderByDesc('listings_count')
            ->limit(10)
            ->get()
            ->map(function ($city) {
                return [
                    'id' => $city->id,
                    'name' => $city->name,
                    'state' => $city->state->name,
                    'full_name' => $city->full_name,
                    'latitude' => $city->latitude,
                    'longitude' => $city->longitude,
                ];
            });

        return $this->successResponse($cities);
    }

    public function citiesWithListings()
    {
        $cities = Listing::active()
            ->selectRaw('city, state, COUNT(*) as listings_count')
            ->groupBy('city', 'state')
            ->orderByDesc('listings_count')
            ->limit(50)
            ->get();

        return $this->successResponse($cities);
    }

    public function detectLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        // Find nearest city
        $city = City::selectRaw("*,
            (6371 * acos(cos(radians(?))
            * cos(radians(latitude))
            * cos(radians(longitude) - radians(?))
            + sin(radians(?))
            * sin(radians(latitude)))) AS distance", [
            $request->latitude,
            $request->longitude,
            $request->latitude,
        ])
            ->having('distance', '<', 100)
            ->orderBy('distance')
            ->first();

        if ($city) {
            return $this->successResponse([
                'city' => $city->name,
                'state' => $city->state->name ?? null,
                'country' => $city->state->country->name ?? 'India',
                'latitude' => $city->latitude,
                'longitude' => $city->longitude,
            ]);
        }

        // No city found within 100km, return the coordinates anyway
        return $this->successResponse([
            'city' => null,
            'state' => null,
            'country' => 'India',
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);
    }

    /**
     * Detect location based on user's IP address
     */
    public function detectLocationByIp(Request $request)
    {
        try {
            $ip = $request->ip();

            // Don't use local IPs
            if (in_array($ip, ['127.0.0.1', '::1']) || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.')) {
                // Use a fallback for local development
                return $this->successResponse([
                    'city' => 'Chennai',
                    'state' => 'Tamil Nadu',
                    'country' => 'India',
                    'latitude' => 13.0827,
                    'longitude' => 80.2707,
                    'source' => 'fallback',
                ]);
            }

            // Use ip-api.com (free, no API key needed, 45 requests/min limit)
            $response = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,city,regionName,country,lat,lon");

            if ($response) {
                $data = json_decode($response, true);

                if ($data && $data['status'] === 'success') {
                    // Try to find matching city in our database
                    $city = City::where('name', 'like', $data['city'] . '%')
                        ->with('state:id,name')
                        ->first();

                    return $this->successResponse([
                        'city' => $city ? $city->name : $data['city'],
                        'state' => $city ? $city->state->name : $data['regionName'],
                        'country' => $data['country'],
                        'latitude' => $city ? $city->latitude : $data['lat'],
                        'longitude' => $city ? $city->longitude : $data['lon'],
                        'source' => 'ip',
                    ]);
                }
            }

            // Fallback to default location (India center)
            return $this->successResponse([
                'city' => null,
                'state' => null,
                'country' => 'India',
                'latitude' => 20.5937,
                'longitude' => 78.9629,
                'source' => 'default',
            ]);
        } catch (\Exception $e) {
            return $this->successResponse([
                'city' => null,
                'state' => null,
                'country' => 'India',
                'latitude' => 20.5937,
                'longitude' => 78.9629,
                'source' => 'error',
            ]);
        }
    }
}
