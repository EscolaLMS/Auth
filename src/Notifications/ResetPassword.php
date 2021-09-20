<?php

namespace EscolaLms\Auth\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as NotificationsResetPassword;

class ResetPassword extends NotificationsResetPassword
{
    private ?string $url;

    public function __construct(string $token, ?string $url)
    {
        $this->url = $url;
        self::$createUrlCallback = fn ($notifiable) => $this->resetUrl($notifiable);
        parent::__construct($token);
    }

    protected function resetUrl($notifiable)
    {
        if (!empty($this->url)) {
            return $this->url .
                '?email=' . $notifiable->getEmailForPasswordReset() .
                '&token=' . $this->token;
        }
        return url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }
}
