<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class EnforceController extends Controller
{
    public function retrievePremis(Request $request): JsonResponse
    {
        try {
            // Validate input
            $request->validate([
                'AUID' => 'required|string'
            ]);

            $AUID = $request->input('AUID');

            // Fetch data from Oracle ENFORCE database
            $records = DB::connection('oracle_enforce')
                ->table('TEST_KESALAHAN')
                ->where('AUID', $AUID)
                ->where('STATUS_DATA', 'Active')
                ->get();

            // Check if records exist
            if ($records->isEmpty()) {
                return response()->json([
                    'status' => 'not found',
                    'message' => 'No records found for the given AUID'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $records
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function storeKesalahanPremis(Request $request): JsonResponse
    {
        try {
            // Validate incoming data
            $validatedData = $request->validate([
                'AUID' => 'required|string',
                'JENIS_KESALAHAN' => 'required|string'
            ]);

            // Insert into Oracle database
            DB::connection('oracle_enforce')->table('TEST_KESALAHAN')->insert($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Data inserted successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteKesalahanPremis(Request $request): JsonResponse
    {
        try {
            $AUID = $request->input('AUID');

            if (!$AUID) {
                return response()->json(['error' => 'AUID is required'], 400);
            }

            // Update STATUS_DATA to 'Delete' instead of deleting the record
            $updated = DB::connection('oracle_enforce')
                ->table('TEST_KESALAHAN')
                ->where('AUID', $AUID)
                ->update(['STATUS_DATA' => 'Delete']);

            if ($updated) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Record marked as deleted'
                ], 200);
            } else {
                return response()->json(['error' => 'Record not found or already deleted'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateKesalahanPremis(Request $request): JsonResponse
    {
        try {
            // Validate the request
            $validatedData = $request->validate([
                'AUID' => 'required|string', // AUID is required
                'JENIS_KESALAHAN' => 'required|string', // New value for JENIS_KESALAHAN
            ]);

            // Find the record by AUID
            $affected = DB::connection('oracle_enforce')
                ->table('TEST_KESALAHAN')
                ->where('AUID', $validatedData['AUID'])
                ->where('STATUS_DATA', 'Active')
                ->update([
                    'JENIS_KESALAHAN' => $validatedData['JENIS_KESALAHAN']
                ]);

            // Check if any row was updated
            if ($affected) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Record updated successfully'
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Record not found or no changes made'
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
