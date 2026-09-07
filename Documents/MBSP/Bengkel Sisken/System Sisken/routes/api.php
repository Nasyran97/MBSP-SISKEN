<?php

use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoadtaxController;
use App\Http\Controllers\KenderaanController;
use App\Http\Controllers\PenyelenggaraanController;


Route::post('/login-test', [LoginController::class, 'login']);
Route::get('/roadtax/update-status', [RoadtaxController::class, 'updateRoadtaxStatus']);  //daily run for update roadtax status

Route::get('/kenderaan/update-department', [KenderaanController::class, 'updateDepartment']);  //update department name based on depCode

Route::get('/penyelenggaraan/expire-pending', [PenyelenggaraanController::class, 'expirePendingRequests']);  //must run daily - after 5pm, auto delete penyelenggaraan requests still PENDING