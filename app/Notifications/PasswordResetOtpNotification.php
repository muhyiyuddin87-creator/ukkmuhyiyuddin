<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetOtpNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $code,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Kode OTP untuk mengatur ulang sandi')
            ->greeting('Permintaan atur ulang sandi')
            ->line('Gunakan kode berikut untuk mengatur ulang sandi akun Anda:')
            ->line($this->code)
            ->line('Kode berlaku selama 10 menit. Jangan bagikan kode ini kepada siapa pun.')
            ->line('Jika Anda tidak meminta pengaturan ulang sandi, abaikan email ini.');
    }
}
