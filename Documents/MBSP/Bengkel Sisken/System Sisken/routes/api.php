<?php

use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoadtaxController;
use App\Http\Controllers\KenderaanController;


Route::post('/login-test', [LoginController::class, 'login']);
Route::get('/roadtax/update-status', [RoadtaxController::class, 'updateRoadtaxStatus']);  //daily run for update roadtax status

Route::get('/kenderaan/update-department', [KenderaanController::class, 'updateDepartment']);  //update department name based on depCode