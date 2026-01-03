<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AppVersionController extends Controller
{
    /**
     * Get the latest app version info
     *
     * Update these values when releasing new APK
     */
    public function check(Request $request): JsonResponse
    {
        // Current latest version - UPDATE THIS when releasing new APK
        $latestVersion = '1.0.0';

        // Minimum required version (for force updates)
        $minimumVersion = '1.0.0';

        // Release notes for the latest version
        $releaseNotes = 'Initial release of Lentlo Ads app.';

        // Download URLs
        $androidUrl = 'https://play.google.com/store/apps/details?id=com.lentlo.ads';
        $iosUrl = 'https://apps.apple.com/app/lentlo-ads/id000000000';

        // Get client info
        $clientVersion = $request->header('X-App-Version', '0.0.0');
        $platform = $request->header('X-Platform', 'android');

        // Check if force update is required
        $forceUpdate = $this->isVersionLower($clientVersion, $minimumVersion);

        return response()->json([
            'success' => true,
            'data' => [
                'version' => $latestVersion,
                'minimum_version' => $minimumVersion,
                'android_url' => $androidUrl,
                'ios_url' => $iosUrl,
                'release_notes' => $releaseNotes,
                'force_update' => $forceUpdate,
            ],
        ]);
    }

    /**
     * Compare two version strings
     */
    private function isVersionLower(string $version, string $minimum): bool
    {
        $versionParts = array_map('intval', explode('.', $version));
        $minimumParts = array_map('intval', explode('.', $minimum));

        $maxLength = max(count($versionParts), count($minimumParts));

        for ($i = 0; $i < $maxLength; $i++) {
            $v = $versionParts[$i] ?? 0;
            $m = $minimumParts[$i] ?? 0;

            if ($v < $m) return true;
            if ($v > $m) return false;
        }

        return false;
    }
}
