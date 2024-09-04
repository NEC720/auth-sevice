<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    //
    /**
     * Vérifie l'adresse email de l'utilisateur.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @param  string  $hash
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify(Request $request, $id, $hash)
    {
        // Récupérer l'utilisateur correspondant à l'ID
        $user = User::find($id);

        // Vérifier si l'utilisateur existe
        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé.'], 404);
        }

        // Vérifier si le hash correspond
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Lien de vérification invalide.'], 403);
        }

        // Si l'utilisateur a déjà vérifié son email
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email déjà vérifié.'], 200);
        }

        // Marquer l'email comme vérifié et déclencher l'événement
        $user->markEmailAsVerified();
        event(new Verified($user));

        return response()->json(['message' => 'Email vérifié avec succès.'], 200);
    }

    /**
     * Réenvoie l'email de vérification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resend(Request $request)
    {
        // return response()->json(['data' => $request->all() ], 405);
        // dd($request, $request->input('id'));
        // Récupérer l'utilisateur via l'ID passé dans la requête
        $user = User::find($request->input('id'));

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Vérifiez si l'utilisateur a déjà vérifié son email
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.'], 200);
        }

        // Envoyer la notification de vérification de l'email
        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification email resent.'], 200);
    }

    // public function showVerificationNotice()
    // {
    //     return response()->json(['message' => 'Please verify your email.']);
    // }
}
