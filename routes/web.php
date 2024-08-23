<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Google
Route::get('redirect/google', [AuthController::class, 'redirectToGoogle']);
Route::get('callback/google', [AuthController::class, 'handleGoogleCallback']);

// GitHub
Route::get('redirect/github', [AuthController::class, 'redirectToGitHub']);
Route::get('callback/github', [AuthController::class, 'handleGitHubCallback']);

// LinkedIn
Route::get('redirect/linkedin', [AuthController::class, 'redirectToLinkedIn']);
Route::get('callback/linkedin', [AuthController::class, 'handleLinkedInCallback']);