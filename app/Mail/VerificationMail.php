<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Exception;

class VerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    /**
     * Create a new message instance.
     *
     * @param $user
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Build the verification email.
     *
     * @return $this
     */
    public function build()
    {
        try {
            $decryptedToken = Crypt::decryptString($this->user->verification_token);

            $decodedToken = json_decode($decryptedToken, true);
            if (!isset($decodedToken['data'], $decodedToken['hmac'])) {
                throw new Exception("Invalid token structure.");
            }

            $tokenData = $decodedToken['data'];
            $providedHmac = $decodedToken['hmac'];

            $calculatedHmac = hash_hmac('sha256', $tokenData, env('HASH_SECRET'));
            if ($providedHmac !== $calculatedHmac) {
                throw new Exception("HMAC validation failed.");
            }

            $tokenDataArray = json_decode($tokenData, true);
            if (!isset($tokenDataArray['timestamp'])) {
                throw new Exception("Invalid token data structure.");
            }

            $verificationUrl = route('verify.email', ['token' => $this->user->verification_token]);

            Log::info("Verification email sent for user ID {$this->user->id}");

            return $this->subject(__('Email Verification'))
                        ->view('emails.verify')
                        ->with([
                            'verificationUrl' => $verificationUrl,
                            'user' => $this->user,
                        ]);
        } catch (Exception $e) {
            Log::error("Error building verification email: " . $e->getMessage());
            throw new Exception("Unable to send the verification email.");
        }
    }
}
