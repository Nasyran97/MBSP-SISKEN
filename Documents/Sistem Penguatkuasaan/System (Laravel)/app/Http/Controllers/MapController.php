<?php

namespace App\Http\Controllers;

use App\Models\Premis;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class MapController extends Controller
{
    public function getMapData()
    {
        try {
            $locations = DB::connection('mssql')->table('dbo.Harta') 
                ->select('HtID', 'HtAUID', 'HtLongitude', 'HtLatitude', 'HtLot1', 'HtNoRumah', 'HtNamaJln', 'HtNamaTaman', 'HtTempat', 'HtPoskodBdr', 'HtDaerah', 'HtMukim', 'HtZon', 'HtKodPilih') 
                ->whereNotNull('HtLongitude') // Filter rows where HtLongitude is not null
                ->whereNotNull('HtLatitude')  // Filter rows where HtLatitude is not null
                ->where('HTDaerah', 'T') // Filter where HTDaerah equals 'T'
                ->where('HtType', operator: '7') // Filter where HTDaerah equals 'T'
                // ->where('HtAUID', '135/06/081/081') // Filter where HTDaerah equals 'T'
                ->limit(1500)
                ->get();

            return response()->json($locations); // Ensure this returns JSON
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500); // Return JSON on error
        }
    }

    // New function to filter by latitude and longitude range
    public function getNearbyLocations(Request $request): JsonResponse
    {
        try {
            $longitude = $request->query('longitude');
            $latitude = $request->query('latitude');
            $radius = $request->query('radius', 0.01); // Default radius (≈ 1.1 km)

            // Validate inputs
            if (!$longitude || !$latitude) {
                return response()->json(['error' => 'Longitude and latitude are required'], 400);
            }

            // Convert single latitude & longitude into a bounding box
            $swLat = $latitude - $radius;
            $swLng = $longitude - $radius;
            $neLat = $latitude + $radius;
            $neLng = $longitude + $radius;

            // Query database using bounding box
            $locations = DB::connection('mssql')->table('dbo.Harta')
                ->select('HtID', 'HtAUID', 'HtType', 'HtLongitude', 'HtLatitude', 'HtLot1', 'HtNoRumah', 'HtNamaJln', 'HtNamaTaman', 'HtTempat', 'HtPoskodBdr', 'HtDaerah', 'HtMukim', 'HtZon', 'HtKodPilih')
                ->where('HtType', operator: '7') // Filter where HTDaerah equals 'T'
                ->whereNotNull('HtLongitude')
                ->whereNotNull('HtLatitude')
                ->whereBetween('HtLongitude', [$swLng, $neLng])
                ->whereBetween('HtLatitude', [$swLat, $neLat])
                ->limit(500) // Limit results for performance
                ->get();

            return response()->json([
                'status' => 'success',
                'swLat' => $swLat,
                'swLng' => $swLng,
                'neLat' => $neLat,
                'neLng' => $neLng,
                'data' => $locations
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
