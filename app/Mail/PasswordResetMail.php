<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $encryptedToken;

    /**
     * Create a new message instance.
     *
     * @param $user
     * @param $encryptedToken
     */
    public function __construct($user, $encryptedToken)
    {
        $this->user = $user;
        $this->encryptedToken = $encryptedToken;

        Log::info("PasswordResetMail Mailable constructed for user.");
    }

    /**
     * Build the email message.
     *
     * @return $this
     */
    public function build()
    {
        try {
            $resetUrl = url("/password/reset/{$this->encryptedToken}");

            Log::info("Building PasswordResetMail for user.");

            return $this->subject('Reset Your Password')
                        ->view('emails.password_reset')
                        ->with([
                            'resetUrl' => $resetUrl,
                            'user' => $this->user,
                        ]);
        } catch (\Exception $e) {
            Log::error("Error building PasswordResetMail: " . $e->getMessage());
            throw new \Exception("Unable to build the password reset email. Please try again later.");
        }
    }
}
