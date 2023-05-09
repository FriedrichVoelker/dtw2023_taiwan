<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;

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

Route::get('/', function (Request $request) {
    return response()->json([
        'success' => true,
        'service' => env('APP_NAME', 'ms_xxxxx'),
        'version' => env('APP_VERSION', '0.0.1'),
        'stage' => env('APP_STAGE', 'development'),
        'health' => env('APP_MAINTENANCE', false) ? 'false' : 'ok',
        'ip' => $request->header()['x-forwarded-for'] ?? 'unknown',
    ], 200);
})->name('info');


Route::prefix("/auth")->group(function() {
    Route::post("login", [UserController::class, "login"]);
});


Route::get("users", [UserController::class, "index"])->middleware("role:admin|developer");
