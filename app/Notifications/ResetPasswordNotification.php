<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token)
    {
        $this->locale = app()->getLocale();
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->locale($this->locale)
            ->subject(__('notification.subject'))
            ->greeting(__('notification.greeting'))
            ->line(__('notification.line1'))
            ->action(__('notification.action'), $url)
            ->line(__('notification.line2', ['count' => config('auth.passwords.users.expire')]))
            ->line(__('notification.line3'));
    }
}
