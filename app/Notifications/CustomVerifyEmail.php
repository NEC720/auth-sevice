<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class CustomVerifyEmail extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Générer l'URL de vérification personnalisée
        $verificationUrl = $this->customVerificationUrl($notifiable);
        // dd($verificationUrl);
        // Créer le message de notification en utilisant Laravel's MailMessage facade
        return (new MailMessage)
                    ->subject('Confirmez votre adresse email')
                    ->greeting('Bonjour!')
                    ->line('Merci de vous être inscrit. Veuillez cliquer sur le bouton ci-dessous pour vérifier votre adresse email.')
                    ->action('Confirmer mon email', $verificationUrl)
                    ->line('Si vous n\'avez pas créé de compte, aucune action n\'est requise.')
                    ->salutation('Cordialement, L\'équipe de ' . config('app.name'));
                    // ->line('The introduction to the notification.')
                    // ->action('Notification Action', url('/'))
                    // ->line('Thank you for using our application!');
    }

    /**
     * Générer une URL de vérification personnalisée.
     */
    /**
     * Générer une URL de vérification personnalisée.
     */
    protected function customVerificationUrl($notifiable)
    {
        // Créer l'URL signée avec un temps d'expiration de 60 minutes
        $temporarySignedURL = URL::temporarySignedRoute(
            'verification.verify', // Nom de la route de vérification
            Carbon::now()->addMinutes(60), // Temps d'expiration
            ['id' => $notifiable->getKey(), 'hash' => sha1($notifiable->getEmailForVerification())]
        );
        // dd($temporarySignedURL);

        // Ajouter l'URL du frontend depuis le fichier .env
        $frontendUrl = config('app.frontend_url');

        // Construire l'URL finale en utilisant l'URL du frontend
        return $frontendUrl . '/inscription/email-verified?url=' . urlencode($temporarySignedURL);
    }


    // protected function customVerificationUrl($notifiable)
    // {
    //     // Ajouter l'URL du api depuis le fichier .env
    //     $verifyUrl = config('app.api_url');

    //     // Créer l'URL signée avec un temps d'expiration de 60 minutes
    //     $temporarySignedURL = URL::temporarySignedRoute(
    //         $verifyUrl . '/email/verify/{id}/{hash}', // Nom de la route de vérification
    //         Carbon::now()->addMinutes(60), // Temps d'expiration
    //         ['id' => $notifiable->getKey(), 'hash' => sha1($notifiable->getEmailForVerification())]
    //     );

    //     // Ajouter l'URL du frontend depuis le fichier .env
    //     $frontendUrl = config('app.frontend_url');

    //     // Construire l'URL finale en utilisant l'URL du frontend
    //     return $frontendUrl . '/inscription/email-verified?url=' . urlencode($temporarySignedURL);
    // }

    //  protected function customVerificationUrl($notifiable)
    // {
    //     // Ajouter l'URL du frontend depuis le fichier .env
    //     $verifyUrl = config('app.api_url');

    //     // Créer l'URL signée avec un temps d'expiration de 60 minutes
    //     $temporarySignedURL = URL::temporarySignedRoute(
    //         $verifyUrl . '/email/verify/{id}/{hash}', // Nom de la route de vérification
    //         Carbon::now()->addMinutes(60), // Temps d'expiration
    //         ['id' => $notifiable->getKey(), 'hash' => sha1($notifiable->getEmailForVerification())]
    //     );

    //     // Ajouter un paramètre supplémentaire (par exemple, une invitation)
    //     return $temporarySignedURL . '&invitation=12345';
    // }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
