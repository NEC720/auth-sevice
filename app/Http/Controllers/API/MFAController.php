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
        ]);
    }
       
}
