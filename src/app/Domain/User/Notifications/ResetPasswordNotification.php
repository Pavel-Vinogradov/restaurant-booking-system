<?php

declare(strict_types=1);

namespace App\Domain\User\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $token,
    ) {}

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
        return (new MailMessage)
            ->subject('Сброс пароля')
            ->line('Вы получили это письмо, потому что запросили сброс пароля.')
            ->line('Ваш код для сброса пароля: **'.$this->token.'**')
            ->line('Код действителен в течение 60 минут.')
            ->line('Если вы не запрашивали сброс пароля, проигнорируйте это письмо.');
    }
}
