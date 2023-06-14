<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Models\User;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseTypesController;
use App\Mail\RegisterMail;
use Illuminate\Support\Facades\Mail;

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
})->name('api.index');

Route::get("/dev", function(Request $request) {
    $user = User::findOrFail("4ed5642e-908f-4b33-82b8-cea8bef728e9");
    // return $user;
    Mail::to('f.voelker@lan1.de')->send(new RegisterMail($user));
    return "ok";
})->name("api.dev");

Route::get('/error', function (Request $request) {
    return response()->json(['success' => false, 'message' => 'unauthorize'], 401);
})->name('api.error');

Route::get("/special_error/{code}", function(Request $request, $code) {
    return response()->json(['success' => false, 'message' => 'unauthorize'], $code);
})->name("api.special_error");


Route::prefix("auth")->group(function($router) {
    Route::post("login", [AuthController::class, "login"])->name("api.auth.login");
    Route::post("register", [AuthController::class, "register"])->name("api.auth.register");
    Route::post("logout", [AuthController::class, "logout"])->name("api.auth.logout");
});

Route::get("me", [UserController::class, "me"])->name("api.users.me");

Route::get("users", [UserController::class, "index"])->middleware("role:admin|developer")->name("api.users.index");
Route::post("user", [UserController::class, "store"])->middleware("role:admin|developer")->name("api.users.store");
Route::get("user/{id}", [UserController::class, "show"])->middleware("role:admin|developer")->name("api.users.show");
Route::put("user/{id}", [UserController::class, "update"])->middleware("role:admin|developer")->name("api.users.update");
Route::delete("user/{id}", [UserController::class, "destroy"])->middleware("role:admin|developer")->name("api.users.destroy");
Route::get("profile/{id}", [UserController::class, "profile"])->name("api.users.profile");
Route::get("me/dashboard", [UserController::class, "getDashboardData"])->name("api.users.dashboard");
Route::put("self", [UserController::class, "update_self"])->name("api.users.update_self");
Route::get("certificate", [UserController::class, "generateCertificate"])->name("api.users.certificate");
Route::delete("self", [UserController::class, "deleteSelf"])->name("api.users.delete_self");

Route::get("courses", [CourseController::class, "index"])->name("api.courses.index");
Route::post("course", [CourseController::class, "store"])->middleware("role:admin|developer")->name("api.courses.store");
Route::get("course/{id}", [CourseController::class, "show"])->name("api.courses.show");
Route::put("course/{id}", [CourseController::class, "update"])->middleware("role:admin|developer")->name("api.courses.update");
Route::delete("course/{id}", [CourseController::class, "destroy"])->middleware("role:admin|developer")->name("api.courses.destroy");
Route::post("course/{id}/enroll", [CourseController::class, "enroll"])->name("api.courses.enroll");
Route::post("course/{id}/cancel", [CourseController::class, "unenroll"])->name("api.courses.cancel");
Route::get("me/courses", [CourseController::class, "my_courses"])->name("api.courses.my_courses");
Route::get("admin/courses", [CourseController::class, "adminIndex"])->middleware("role:admin|developer")->name("api.courses.admin_courses");


Route::get("dropdown/course_types", [CourseTypesController::class, "dropdown"])->middleware("role:admin|developer")->name("api.course_types.dropdown");
Route::get("course_types", [CourseTypesController::class, "index"])->middleware("role:admin|developer")->name("api.course_types.index");
Route::post("course_type", [CourseTypesController::class, "store"])->middleware("role:admin|developer")->name("api.course_types.store");
Route::get("course_type/{id}", [CourseTypesController::class, "show"])->middleware("role:admin|developer")->name("api.course_types.show");
Route::put("course_type/{id}", [CourseTypesController::class, "update"])->middleware("role:admin|developer")->name("api.course_types.update");
Route::delete("course_type/{id}", [CourseTypesController::class, "destroy"])->middleware("role:admin|developer")->name("api.course_types.destroy");
