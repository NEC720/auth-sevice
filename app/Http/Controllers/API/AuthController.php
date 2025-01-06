<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Employee;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'cyber_id' => 'nullable|numeric',
            'bio' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $employeeId = null;
        // Si les informations de l'employé sont fournies, créez l'employé
        if ($request->has('cyber_id') && $request->has('bio')) {
            $employee = Employee::create($request->only('cyber_id', 'bio', 'status_id'));
            $employeeId = $employee->id;
        } else {
            $employeeId = null; // Pas d'employé associé
        }
        $roleId = $request->input('role_id', 1);
        // Remplacez 1 par l'ID de votre rôle par défaut
        // $statusId = $request->input('status_id', 13); // Remplacez 1 par l'ID de votre statut par défaut
        $user = User::create([
            'name' => $request->firstName . ' ' . $request->lastName,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'last_name' => $request->last_name,
            'first_name' => $request->first_name,
            'phone' => $request->phone,
            'role_id' => $roleId,
            'employee_id' => $employeeId, // Peut être null si pas d'employé
        ]);

        $token = JWTAuth::fromUser($user);
        $hashedToken = Hash::make($token);
        $user->api_token = $hashedToken;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ], 201);
    }

    // public function registera(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'email' => 'required|string|email|max:255|unique:users',
    //         'password' => 'required|string|min:6|confirmed',
    //         'firstName' => 'required|string|max:255',
    //         'lastName' => 'required|string|max:255',
    //         'phone' => 'nullable|string|max:20',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors(), 422);
    //     }

    //     $user = User::create([
    //         'name' => $request->firstName . ' ' . $request->lastName,
    //         'email' => $request->email,
    //         'password' => Hash::make($request->password),
    //         'first_name' => $request->firstName,
    //         'last_name' => $request->lastName,
    //         'phone' => $request->phone,
    //     ]);

    //     $token = JWTAuth::fromUser($user);
    //     $hashedToken = Hash::make($token);
    //     $user->api_token = $hashedToken;
    //     $user->save();

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'User created successfully',
    //         'user' => $user,
    //         'authorisation' => [
    //             'token' => $token,
    //             'type' => 'bearer',
    //         ]
    //     ], 201);
    // }
    public function login_employee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $credentials = $request->only('email', 'password');

        if (!$token = Auth::attempt($credentials)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        /**
         * @var \App\Models\User $user
         */
        $user = Auth::user();
        dd($user);

        // Vérification de l'existence d'un ID d'employé
        if (!$user->employee_id ?? null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized user',
            ], 403);
        }
        // Charger les relations de l'utilisateur
        $user->load('employee', 'role');
        $hashedToken = Hash::make($token);
        $user->api_token = $hashedToken;
        $user->save();

        return response()->json([
            'status' => 'success',
            'user' => [
                'id' => $user->id,
                'img' => $user->img,
                'name' => $user->name,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'employee' => [
                    'id' => $user->employee->id,
                    'bio' => $user->employee->bio,
                    'status' => [
                        'id' => $user->employee->status->id, // ID du statut
                        'name' => $user->employee->status->name, // Nom du statut
                        // Ajoutez d'autres champs que vous souhaitez récupérer
                    ],
                    'cyber' => [
                        'id' => $user->employee->cyber->id, // ID du cyber
                        'name' => $user->employee->cyber->name, // Nom du cyber
                        // 'opening_hours' => $user->employee->cyber->opening_hours,
                        // Ajoutez d'autres champs que vous souhaitez récupérer
                    ],
                ],
                'role' => [
                    'id' => $user->role->id, // ID du cyber
                    'name' => $user->role->name, // Nom du cyber
                ], // Données du rôle
            ],
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }



    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);


        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $credentials = $request->only('email', 'password');

        if (!$token = Auth::attempt($credentials)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        /**
         * @var \App\Models\User $user
         */
        $user = Auth::user();
        $hashedToken = Hash::make($token);
        $user->api_token = $hashedToken;
        $user->save();

        return response()->json([
            'status' => 'success',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->bearerToken();
        JWTAuth::invalidate($token);

        /**
         * @var \App\Models\User $user
         */
        $user = Auth::user();
        $user->api_token = null;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh(Request $request)
    {
        $token = JWTAuth::refresh(JWTAuth::getToken());

        /**
         * @var \App\Models\User $user
         */
        $user = Auth::user();
        $hashedToken = Hash::make($token);
        $user->api_token = $hashedToken;
        $user->save();

        return response()->json([
            'status' => 'success',
            'user' => $user,
            'authorisation' => [
                'new_token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function me()
    {
        $curent_user = Auth::user();
        if (!$curent_user) {
            return response()->json(['message' => 'Aucun utilisateur connecte.'], 402);
        }
        return response()->json(Auth::user());
    }

    public function verifyToken(Request $request)
    {
        try {
            // Récupère le token passé dans la requête
            $token = $request->input('token');

            // Parse le token pour obtenir le payload
            $payload = JWTAuth::parseToken()->getPayload();

            // Obtenir l'utilisateur à partir du token
            $user = JWTAuth::authenticate($token);
            // dd($user, Hash::check($token, $user->api_token));

            // Vérifie si le token correspond bien à l'API_TOKEN de l'utilisateur
            if ($user) {
                // Le token est valide
                return response()->json([
                    'valid' => true,
                    'message' => 'Le Token est valide!',
                    'payload' => $payload,
                    'created' => date('Y-m-d H:i:s', $payload['nbf']),
                    'expires' => date('Y-m-d H:i:s', $payload['exp'])
                ]);
            } else {
                // Le token ne correspond pas à l'API_TOKEN de l'utilisateur
                return response()->json(['valid' => false, 'message' => 'Le Token est invalide!'/*, 'user_id'=> $payload['sub']? $payload['sub']:'null'*/]);
            }
        } catch (JWTException $e) {
            // Le token est invalide
            return response()->json(['valid' => false, 'error' => $e->getMessage()]);
        }
    }




    public function validateToken(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            // $payload = JWTAuth::parseToken()->getPayload();
            return response()->json(['valid' => true], 200);
        } catch (JWTException $e) {
            return response()->json(['valid' => false], 401);
        }
    }

    /* --------------------------------
    |  ----     Social Login       ----
    /* -------------------------------*/
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        // Gazzul dans Socialite (marche)
        try {
            $client = new Client([
                'base_uri' => 'https://www.googleapis.com',
                'verify' => storage_path('cacert.pem'),
            ]);

            $response = $client->post('/oauth2/v4/token', [
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'code' => request()->get('code'),
                    'redirect_uri' => env('GOOGLE_REDIRECT'),
                    'client_id' => env('GOOGLE_CLIENT_ID'),
                    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
                ],
            ]);

            $body = json_decode($response->getBody());

            // Vérifiez si nous avons un token d'accès
            if (isset($body->access_token)) {
                // Utilisez le token d'accès pour obtenir les informations utilisateur
                $userResponse = $client->get('/oauth2/v3/userinfo', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $body->access_token,
                    ],
                ]);

                // Décodage de la réponse JSON
                $userInfo = json_decode($userResponse->getBody(), true);

                // Débogage ou traitement des informations utilisateur
                // dd($userInfo);
                return $this->handleProviderCallback($userInfo);
            } else {
                // Gérer le cas où le token n'est pas retourné
                dd('Token d\'accès non reçu.');
            }
            // dd($body); // Pour afficher la réponse

        } catch (RequestException $e) {
            dd($e->getMessage()); // Affiche l’erreur
        }

        // //Socialite Debug
        // try {
        //     $user = Socialite::driver('google')->stateless()->user();
        //     dd($user);
        // } catch (\Exception $e) {
        //     dd($e->getMessage());
        // }

        // $user = Socialite::driver('google')->user();
        // return $this->handleProviderCallback($user);
    }

    public function redirectToGitHub()
    {
        return Socialite::driver('github')->redirect();
    }

    public function handleGitHubCallback()
    {
        $userIportantInfos = [];
        try {
            // Guzzle client pour GitHub
            $client = new Client([
                'base_uri' => 'https://github.com',
                'verify' => storage_path('cacert.pem'),
            ]);

            // Échange du code d'autorisation contre un token d'accès
            $response = $client->post('/login/oauth/access_token', [
                'form_params' => [
                    'client_id' => env('GITHUB_CLIENT_ID'),
                    'client_secret' => env('GITHUB_CLIENT_SECRET'),
                    'code' => request()->get('code'),
                    'redirect_uri' => env('GITHUB_REDIRECT'),
                ],
                'headers' => [
                    'Accept' => 'application/json', // GitHub nécessite ce header pour recevoir une réponse JSON
                ],
            ]);

            $body = json_decode($response->getBody());

            // Vérifiez si nous avons un token d'accès
            if (isset($body->access_token)) {
                // Utilisez le token d'accès pour obtenir les informations utilisateur
                $userResponse = $client->get('https://api.github.com/user', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $body->access_token,
                        'Accept' => 'application/json',
                    ],
                ]);

                // Décodage de la réponse JSON
                $userInfo = json_decode($userResponse->getBody(), true);

                // Récupérer les emails de l'utilisateur
                $emailResponse = $client->get('https://api.github.com/user/emails', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $body->access_token,
                        'Accept' => 'application/json',
                    ],
                ]);

                $emails = json_decode($emailResponse->getBody(), true);

                // Filtrer l'email principal (primary)
                $primaryEmail = collect($emails)->firstWhere('primary', true)['email'] ?? null;

                // Si l'email principal est trouvé, l'ajouter aux informations utilisateur
                if ($primaryEmail) {
                    // $userInfo['email'] = $primaryEmail;
                    $userIportantInfos = array(
                        'name' => $userInfo['login'],
                        'email' => $primaryEmail,
                        'github_id' => $userInfo['id'],
                        'github_token' => $body->access_token,
                        // 'github_refresh_token' => $body->refresh_token,
                    );
                } else {
                    // Gérer le cas où aucun email principal n'est trouvé
                    dd('Email principal non trouvé.');
                }
                // dd($body, $userInfo, $emails, $primaryEmail, $userIportantInfos);

                // Traitez les informations utilisateur
                return $this->handleProviderCallback($userIportantInfos);
            } else {
                // Gérer le cas où le token n'est pas retourné
                dd('Token d\'accès non reçu.');
            }
        } catch (RequestException $e) {
            // Gérer les exceptions de requête
            dd($e->getMessage());
        }

        // // $user = Socialite::driver('github')->user();
        // $githubUser = Socialite::driver('github')->user();
        // dd($githubUser);

        // $user = User::updateOrCreate([
        //     'github_id' => $githubUser->id,
        // ], [
        //     'name' => $githubUser->name,
        //     'email' => $githubUser->email,
        //     'github_token' => $githubUser->token,
        //     'github_refresh_token' => $githubUser->refreshToken,
        // ]);

        // Auth::login($user);
        // dd($user);
        // return $this->handleProviderCallback($user);
    }

    public function redirectToLinkedIn()
    {
        return Socialite::driver('linkedin')->redirect();
    }

    public function handleLinkedInCallback()
    {
        $user = Socialite::driver('linkedin')->user();
        return $this->handleProviderCallback($user);
    }

    protected function handleProviderCallback($socialUser)
    {
        $user = User::where('email', $socialUser['email'])->first();

        if (!$user) {
            $user = User::create([
                'name' => $socialUser['name'],
                'email' => $socialUser['email'],
                'email_verified_at' => now(),
                'password' => bcrypt('1DefaultPassword'), // Crée un mot de passe aléatoire 1st with uniqid()
            ]);
        }

        // Connecter l'utilisateur
        Auth::login($user);

        // Générer un token JWT
        $token = JWTAuth::fromUser($user);
        $hashedToken = Hash::make($token);
        $user->api_token = $hashedToken;
        $user->save();
        // dd($token);

        return redirect()->away(env('FRONTEND_URL') . '/auth/callback?token=' . $token);

        // return response()->json([
        //     'status' => 'success',
        //     'message' => 'User log successfully',
        //     'user' => $user,
        //     'authorisation' => [
        //         'token' => $token,
        //         'type' => 'bearer',
        //     ]
        // ]);
    }
}
