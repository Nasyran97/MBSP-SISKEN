<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MapController;
use App\Http\Controllers\EnforceController;
use App\Http\Controllers\SpeedbizLesenController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/map/data/nearby', [MapController::class, 'getNearbyLocations']); // Get premis (type 7) details from Penilaian // Needs param longitude and latitude

//Penilaian
Route::get('/enforce/retrieveSalahPremis', [EnforceController::class, 'retrieveKesalahanPremis']); // Get kesalahan premis by AUID from oracle ENFORCE, table TEST_KESALAHAN (READ)
Route::post('/enforce/storeSalahPremis', [EnforceController::class, 'storeKesalahanPremis']); // Insert kesalahan premis by AUID into oracle ENFORCE, table TEST_KESALAHAN (CREATE)
Route::post('/enforce/deleteSalahPremis', [EnforceController::class, 'deleteKesalahanPremis']); // Change STATUS_DATA of AUID at oracle ENFORCE, table TEST_KESALAHAN from Active to Delete (DELETE)
Route::post('/enforce/updateSalahPremis', [EnforceController::class, 'updateKesalahanPremis']); // Update the data by AUID at oracle ENFORCE, table TEST_KESALAHAN(UPDATE)

//Perancang


//Bangunan


//Lesen
Route::get('/speedbiz/licenses', [SpeedbizLesenController::class, 'getLicenseData']);