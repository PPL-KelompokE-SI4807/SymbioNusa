<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * FND-03 (SYM-3): email tautan reset kata sandi dalam Bahasa Indonesia.
 */
class ResetPasswordNotification extends ResetPassword
{
    /**
     * @param  User  $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        $broker = config('auth.defaults.passwords');
        $minutes = (int) config("auth.passwords.{$broker}.expire", 60);

        return (new MailMessage)
            ->subject('Atur ulang kata sandi SymbioNusa')
            ->greeting('Halo, '.$notifiable->nama.'!')
            ->line('Kami menerima permintaan untuk mengatur ulang kata sandi akun SymbioNusa Anda.')
            ->action('Atur Ulang Kata Sandi', $url)
            ->line("Tautan ini berlaku selama {$minutes} menit.")
            ->line('Jika Anda tidak meminta pengaturan ulang, abaikan email ini; kata sandi Anda tidak berubah.')
            ->salutation('Salam, Tim SymbioNusa');
    }
}
