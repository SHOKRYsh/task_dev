<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SendVerificationCodeSMS extends Notification
{
    use Queueable;

    protected $verificationCode;

    public function __construct($verificationCode)
    {
        $this->verificationCode = $verificationCode;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'verification_code' => $this->verificationCode,
        ];
    }

    public function toSms($notifiable)
    {
        Log::info('Verification code for ' . $notifiable->phone . ': ' . $this->verificationCode);
        return 'Your verification code is: ' . $this->verificationCode;
    }
}
