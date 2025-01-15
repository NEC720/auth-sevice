<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre code MFA</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f4f4f4; border-radius: 8px; border: 1px solid #ddd;">
        <h2 style="color: #333; text-align: center;">Votre code de vérification MFA</h2>
        <p>Bonjour,</p>
        <p>Pour sécuriser votre connexion, veuillez utiliser le code suivant :</p>
        <h3 style="font-size: 24px; color: #4CAF50; text-align: center; margin: 20px 0;">{{ $code }}</h3>
        <p style="text-align: center;">Ce code est valide pendant 10 minutes.</p>
        <p>Si vous n'avez pas demandé ce code, veuillez ignorer cet email.</p>
        <p style="color: #888;">L'équipe de support.</p>
    </div>
</body>
</html>
