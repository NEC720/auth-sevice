<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\Auth\VerificationController;
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

Route::get('/', function () {
    return [
        'success' => true,
        'message' => "Bienvenue sur l'API du 'Service d'Authantification DossCopy' !",
        'data' => [
            'name' => 'Auth Service',
            'description' => "API DossCopy pour l'Authantification",
            'version' => '1.0.0',
            'language' => app()->getLocale(),
            'supports' => [
                "contact@ittraininghub.io",
                "https://www.ittraininghub.io",
            ],
            'authors' => [
                'It Training Hub Team - CG',
            ],
            'links' => [
                [
                    'rel' => 'documentation',
                    'href' => 'https://localhost:8000/api/documentation',
                ],
                // More links...
                // For example, authentication link...
            ],
            'endpoints' => [
                [
                    'path' => '/cybers',
                    'description' => 'List all cybers',
                    'method' => 'GET',
                    'parameters' => [],
                    'response' => [
                        'code' => 200,
                        'type' => 'array',
                        'content' => [
                            'cyber_id' => 'int',
                            'name' => 'string',
                            'description' => 'string',
                        ],
                        'examples' => [
                            [
                                'cyber_id' => 1,
                                'name' => 'cyber 1',
                                'description' => 'Description du cyber 1',
                            ],
                            // More products...
                        ],
                        'error_responses' => [
                            [
                                'code' => 401,
                                'type' => 'object',
                                'content' => [
                                    'error' => 'string',
                                    'message' => 'string',
                                    'trace' => 'array',
                                ],
                            ],
                            // More error responses...
                            // Forbidden response...
                        ],
                    ],
                    'error_responses' => [
                        [
                            'code' => 404,
                            'type' => 'object',
                            'content' => [
                                'error' => 'string',
                                'message' => 'string',
                                'trace' => 'array',
                            ],
                        ],
                    ],
                ],
                // More endpoints...
                // For example, authentication middleware...
            ],
        ]


    ];
});



Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::middleware('auth.jwt')->group(function () {
    // Place protected routes here
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me']);
});

Route::post('verifytoken', [AuthController::class, 'verifyToken']);
Route::post('validate-token', [AuthController::class, 'validateToken']);
// Route::post('validate-token', [AuthController::class, 'AuthController@validateToken']);

// Route::post('register', [AuthController::class, 'register']); // vérifiée
// Route::post('login', [AuthController::class, 'login']); // vérifiée
// Route::post('verifytoken', [AuthController::class, 'verifyToken']); //'inactivity.logout' // vérifiée
// Route::post('logout', [AuthController::class, 'logout'])->middleware(['auth:api', 'verify.jwt',]); //'inactivity.logout' // vérifiée
// Route::post('refresh', [AuthController::class, 'refresh'])->middleware(['auth:api', 'verify.jwt',]); //'inactivity.logout' // vérifiée
// Route::get('me', [AuthController::class, 'me'])->middleware(['auth:api', 'verify.jwt',]); //'inactivity.logout' // vérifiée
// Route::post('validate-token', [AuthController::class, 'validateToken']); //'inactivity.logout' // vérifiée



// // Google
// Route::get('redirect/google', [AuthController::class, 'redirectToGoogle']);
// Route::get('callback/google', [AuthController::class, 'handleGoogleCallback']);

// // GitHub
// Route::get('redirect/github', [AuthController::class, 'redirectToGitHub']);
// Route::get('callback/github', [AuthController::class, 'handleGitHubCallback']);

// // LinkedIn
// Route::get('redirect/linkedin', [AuthController::class, 'redirectToLinkedIn']);
// Route::get('callback/linkedin', [AuthController::class, 'handleLinkedInCallback']);


// Route::post('/email/verification-notification', [VerificationController::class, 'sendVerificationEmail'])
//     ->middleware('auth:api', 'throttle:6,1')->name('verification.send');

// Route::get('/email/verify/{token}', [VerificationController::class, 'verify'])
//     ->middleware('auth:api', 'signed')->name('verification.verify');

// Route::get('/email/verify', [VerificationController::class, 'showVerificationNotice'])
//     ->middleware('auth:api')->name('verification.notice');

// Routes protégées par le middleware d'authentification

// Route::middleware(['auth:api'])->group(function () {
    // Route pour vérifier l'email
    Route::get('email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');

    // Route pour réenvoyer l'email de vérification
    Route::post('email/resend', [VerificationController::class, 'resend'])->name('verification.resend');
// });