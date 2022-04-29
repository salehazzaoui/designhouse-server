<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\ResetPassword as Notification;

class ResetPassword extends Notification
{
    use Queueable;

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = url(config('app.client_url') . '/auth/password/reset?token=' . $this->token) .
            '&email=' . urlencode($notifiable->email);
        return (new MailMessage)
            ->line('You are reciving this email because we recived a password request for your account')
            ->action('Reset Password', $url)
            ->line('Thank you for using our application!');
    }
}
