<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LoginOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $otpCode;
    public $expiresAt;

    public function __construct($user, $otpData)
    {
        $this->user = $user;
        $this->otpCode = $otpData['code'];
        $this->expiresAt = date('H:i:s', $otpData['expires_at']);
    }

    public function build()
    {
        return $this->subject('Your Login OTP')
                    ->view('emails.login_otp')
                    ->with([
                        'user' => $this->user,
                        'otpCode' => $this->otpCode,
                        'expiresAt' => $this->expiresAt,
                    ]);
    }
}
