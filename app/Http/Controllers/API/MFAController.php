<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use ParagonIE\ConstantTime\Base32;
use PragmaRX\Google2FA\Google2FA;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class MFAController extends Controller
{
    /**
     * Active le MFA pour un utilisateur et génère un QR Code à scanner.
     */
    public function activateMfa($id)
    {
        // Trouver l'utilisateur par son ID
        $user = User::find($id);

        // Vérifier si l'utilisateur existe
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Utilisateur non trouvé.'
            ], 404);
        }

        // Vérifier si MFA est déjà activé pour cet utilisateur
        if ($user->google2fa_secret) {
            return response()->json([
                'status' => 'error',
                'message' => 'MFA est déjà activé pour cet utilisateur.'
            ], 400);
        }

        // Initialiser Google2FA
        $google2fa = new Google2FA();

        // Générer une clé secrète pour l'utilisateur (mais ne pas activer immédiatement)
        $secretKey = $google2fa->generateSecretKey();

        // Sauvegarder la clé sans activer le statut MFA pour l'instant
        $user->google2fa_secret = $secretKey;
        $user->save();

        // Générer un QR code à scanner avec Google Authenticator
        $QR_Image = $google2fa->getQRCodeUrl(
            config('app.name'), // Nom de ton application
            $user->email,
            $secretKey
        );

        return response()->json([
            'status' => 'success',
            'message' => 'QR Code généré avec succès. Scannez le QR code avec votre application authentificatrice.',
            'QR_Image' => $QR_Image
        ]);
    }

    /**
     * Vérifie le code MFA fourni et connecte l'utilisateur.
     */
    public function verifyMFA(Request $request, $id)
    {
        // Valider les données du formulaire
        $request->validate([
            'code' => 'required|numeric',
        ]);

        // Trouver l'utilisateur par son ID
        $user = User::find($id);

        // Vérifier si l'utilisateur existe
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Utilisateur non trouvé.'
            ], 404);
        }

        // Vérifier si l'utilisateur a une clé secrète MFA
        if (!$user->google2fa_secret) {
            return response()->json([
                'status' => 'error',
                'message' => 'Aucune clé MFA trouvée pour cet utilisateur.'
            ], 400);
        }

        // Initialiser Google2FA
        $google2fa = new Google2FA();

        // Générer le code OTP attendu (sans décodage)
        $expectedCode = $google2fa->getCurrentOtp($user->google2fa_secret);

        // Vérifier si le code MFA est valide
        $isValid = $google2fa->verifyKey($user->google2fa_secret, $request->code);

        // Affichage des valeurs pour débogage
        // dd([
        //     'Clé MFA' => $user->google2fa_secret,
        //     'Code attendu' => $expectedCode,
        //     'Code reçu' => $request->code,
        //     'Validation' => $isValid ? '✔️ Code valide' : '❌ Code invalide'
        // ]);

        if (!$isValid) {
            return response()->json([
                'status' => 'error',
                'message' => 'Code MFA invalide.'
            ], 400);
        }

        // Une fois que le code est validé, activer le MFA
        $user->google2fa_enabled = true; // Activer le MFA
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'MFA activé avec succès.'
        ]);
    }

    public function authVerifyMFA(Request $request, $id)
    {
        $request->validate([
            'code' => 'required|numeric',
        ]);

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Utilisateur non trouvé.'
            ], 404);
        }

        if (!$user->google2fa_secret) {
            return response()->json([
                'status' => 'error',
                'message' => 'Aucune clé MFA trouvée pour cet utilisateur.'
            ], 400);
        }

        $google2fa = new Google2FA();
        if (!$google2fa->verifyKey($user->google2fa_secret, $request->code, 2)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Code MFA invalide.'
            ], 400);
        }

        // Authentifier l'utilisateur et générer le token JWT
        $token = auth()->login($user);

        $user->google2fa_enabled = true;
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



    /**
     * Désactive le MFA pour un utilisateur.
     */
    public function disableMfa($id)
    {
        // Trouver l'utilisateur par son ID
        $user = User::find($id);

        // Vérifier si l'utilisateur existe
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Utilisateur non trouvé.'
            ], 404);
        }

        // Désactiver le MFA pour cet utilisateur
        $user->google2fa_secret = null;
        $user->google2fa_enabled = false; // ⚠️ Indiquer que le MFA est désactivé

        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'MFA désactivé avec succès pour cet utilisateur.',
        ]);
    }

    /**
     * Génère le QR code MFA pour un utilisateur donné.
     */
    public function getMFAQRCode($id)
    {
        $user = User::findOrFail($id);
        $google2fa = new Google2FA();

        // Générer une clé MFA si elle est absente
        if (empty($user->google2fa_secret)) {
            $user->google2fa_secret = $google2fa->generateSecretKey();
            $user->save();
        }

        // Construire l'URL OTP
        $QR_Image = "otpauth://totp/" . config('app.name') . ":" . urlencode($user->email) . "?secret=" . $user->google2fa_secret . "&issuer=" . config('app.name');

        // Générer le QR Code
        $qrCode = QrCode::format('svg')->size(300)->errorCorrection('H')->generate($QR_Image);

        return response()->json([
            'status' => 'success',
            'QR_Image' => 'data:image/svg+xml;base64,' . base64_encode($qrCode)
        ]);
    }
}
