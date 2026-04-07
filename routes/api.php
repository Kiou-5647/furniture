<?php

use App\Http\Controllers\Api\GeodataController;
use Illuminate\Support\Facades\Route;

Route::get('/geodata/provinces', [GeodataController::class, 'provinces']);
Route::get('/geodata/wards', [GeodataController::class, 'wards']);
