<?php

namespace EscolaLms\Auth\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as NotificationsResetPassword;

class ResetPassword extends NotificationsResetPassword
{
    private ?string $url;

    public function __construct(string $token, ?string $url)
    {
        $this->url = $url;
        parent::__construct($token);
    }

    protected function resetUrl($notifiable)
    {
        if (!empty($this->url)) {
            return $this->url .
                '?email=' . $notifiable->getEmailForPasswordReset() .
                '&token=' . $this->token;
        }
        return parent::resetUrl($notifiable);
    }
}
