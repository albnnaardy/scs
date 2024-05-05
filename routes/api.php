<?php

use App\Http\Controllers\MusadaqaController;
use App\Http\Controllers\PaymentShaveController;
use App\Http\Controllers\PurchaseTokenController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


// master data
Route::get('index', [StudentController::class, 'index']);
Route::post('add-student', [StudentController::class, 'addstudent']);
Route::put('edit/{id}', [StudentController::class, 'edit']);
Route::delete('delete/{id}', [StudentController::class, 'delete']);

// admin
Route::put('shave-token-purchases/{studentId}', [PurchaseTokenController::class, 'edit']);
Route::delete('deletetoken', [PurchaseTokenController::class, 'delete']);
Route::post('buy', [PurchaseTokenController::class, 'buy']);


// barber
Route::get('vip', [PurchaseTokenController::class, 'showVip']);
Route::get('reguler', [PurchaseTokenController::class, 'showReguler']);
Route::post('check-token', [PaymentShaveController::class, 'checkToken']);
Route::post('pay', [PaymentShaveController::class, 'pay']);


// User_auth
Route::get('login', [MusadaqaController::class, 'login']);
Route::get('users', [UserController::class, 'index']);
Route::get('search', [UserController::class, 'searchuser']);
Route::post('addusers', [UserController::class, 'adduser']);
Route::put('users/{id}', [UserController::class, 'update']);
Route::delete('users/{id}', [UserController::class, 'delete']);


// Route::middleware(['auth'])->group(function () {
//     Route::get('login', [AuthController::class, 'login']);
//     Route::get('users', [UserController::class, 'index']);
//     Route::get('search', [UserController::class, 'searchuser']);
//     Route::post('addusers', [UserController::class, 'adduser']);
//     Route::put('users/{id}', [UserController::class, 'update']);
//     Route::delete('users/{id}', [UserController::class, 'delete']);

// });
