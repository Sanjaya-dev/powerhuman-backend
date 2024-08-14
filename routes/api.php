<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\TeamController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\CompanyController;


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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// company api
Route::prefix('company')->middleware('auth:sanctum')->name('company.')->group( function () {
        Route::get('',[CompanyController::class,'fetch'])->name('fetch');
        Route::post('',[CompanyController::class,'create'])->name('create');
        Route::post('update/{id}',[CompanyController::class,'update'])->name('update');
    });
    
// team api
Route::prefix('team')->middleware('auth:sanctum')->name('team.')->group( function () {
        Route::get('',[TeamController::class,'fetch'])->name('fetch');
        Route::post('',[TeamController::class,'create'])->name('create');
        Route::post('update/{id}',[TeamController::class,'update'])->name('update');
        Route::delete('{id}',[TeamController::class,'destroy'])->name('delete');
    });
// role api
Route::prefix('role')->middleware('auth:sanctum')->name('role.')->group( function () {
        Route::get('',[RoleController::class,'fetch'])->name('fetch');
        Route::post('',[RoleController::class,'create'])->name('create');
        Route::post('update/{id}',[RoleController::class,'update'])->name('update');
        Route::delete('{id}',[RoleController::class,'destroy'])->name('delete');
    });
    
// auth api
Route::name('auth.')->group(function () {
    Route::post('/login',[UserController::class, 'login'])->name('login');
    Route::post('/register',[UserController::class, 'register'])->name('register');
    Route::middleware('auth:sacnctum')->group(function () {
        Route::post('/logout',[UserController::class, 'logout'])->name('logout');
        Route::get('/user',[UserController::class, 'fetch'])->name('fetch');
    });
});

