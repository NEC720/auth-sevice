<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class MFAController extends Controller
{
    /**
     * Vérifie un code MFA et retourne un jeton d'authentification en cas de succès
     */
    public function verifyMFA(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'mfa_code' => 'required|numeric',
        ]);
    
        $user = User::where('email', $request->email)->first();
    
        if (!$user || $user->mfa_code !== $request->mfa_code || $user->mfa_expires_at < now()) {
            return response()->json(['message' => 'Code MFA invalide ou expiré.'], 401);
        }
    
        // Valider le code MFA et générer un token
        $token = $user->createToken('auth_token')->plainTextToken;
        $user->mfa_verified_at = now();
        $user->save();
    
        return response()->json([
            'message' => 'MFA validé avec succès.',
            'status' => 'success',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
            // 'access_token' => $token,
            // 'token_type' => 'Bearer'
        ]);
    }
        /**
     * Génère un code MFA pour un utilisateur
     */
    // public function generateMfaCode(Request $request)
    // {
    //     $request->validate([
    //         'email' => 'required|email',
    //     ]);

    //     $user = User::where('email', $request->email)->first();

    //     if (!$user) {
    //         return response()->json(['message' => 'User not found'], 404);
    //     }

    //     $user->mfa_code = random_int(100000, 999999); // Génère un code aléatoire à 6 chiffres

    //     $user->mfa_expires_at = Carbon::now()->addMinutes(config('auth.mfa.code_expiration'));
    //     $user->save();

    //     return response()->json([
    //         'message' => 'MFA code generated'
    //     ]);
    // }
}
