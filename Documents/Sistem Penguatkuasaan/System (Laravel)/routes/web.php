<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MapController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes(['verify' => true]);

Route::get('/', [App\Http\Controllers\HomeController::class, 'root'])->name('root');

// customers route
Route::get('/customers', [App\Http\Controllers\CustomerController::class, 'index'])->name('customer.list');
Route::post('/update-status-customers', [App\Http\Controllers\CustomerController::class, 'updateStatus'])->name('update.status');
Route::get('/edit-customer/{id}', [App\Http\Controllers\CustomerController::class, 'editCustomer'])->name('customer.edit');
Route::put('/customers/{id}', [App\Http\Controllers\CustomerController::class, 'updateCustomer'])->name('customer.update');
Route::post('/mark-deleted', [App\Http\Controllers\CustomerController::class, 'markAsDeleted'])->name('mark.deleted');

// mssql route
Route::get('/customers', [App\Http\Controllers\MssqlController::class, 'index'])->name('customer.list');

//Update User Details
Route::post('/update-profile/{id}', [App\Http\Controllers\HomeController::class, 'updateProfile'])->name('updateProfile');
Route::post('/update-password/{id}', [App\Http\Controllers\HomeController::class, 'updatePassword'])->name('updatePassword');

Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index'])->name('index');

//Language Translation
Route::get('index/{locale}', [App\Http\Controllers\HomeController::class, 'lang']);

// Route for displaying the map view
Route::get('/map', function () {return view('map');})->name('map.view');

// Route for fetching map data
Route::get('/map/get-map-data', [App\Http\Controllers\MapController::class, 'getMapData'])->name('get-map-data');