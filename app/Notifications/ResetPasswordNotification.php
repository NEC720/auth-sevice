<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    protected $token;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
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
        $url = env('VITE_ADMIN_DOSSCOPY_BASE_URL') . "/reset-password/{$this->token}?email=" . urlencode($notifiable->email);
    
        return (new MailMessage)
                    ->subject('Réinitialisation du mot de passe')
                    ->greeting('Bonjour!')
                    ->line('Vous avez demandé une réinitialisation de mot de passe.')
                    ->action('Changer le mot de passe', $url)
                    ->line('Si vous n\'avez pas fait cette demande, aucune action supplémentaire n\'est requise.')
                    ->salutation('Cordialement, L\'équipe de IT Training Hub');
    }
    

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
