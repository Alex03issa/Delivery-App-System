<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Mail\PasswordResetMail;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Crypt;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email', [
            'title' => 'Request Link Reset'
        ]);
    }

    public function sendResetLinkEmail(Request $request)
    {
        try {
            Log::info('Password reset request received.', ['email' => $request->email]);

            $request->validate(['email' => 'required|email']);

            $normalizedEmail = strtolower(trim($request->email));
            Log::info('Normalized email', ['email' => $normalizedEmail]);
            $hashedEmail = hash_hmac('sha256', $normalizedEmail, env('HASH_SECRET'));
            Log::info('Hashed email', ['hash' => $hashedEmail]);
            $user = User::where('hashed_email', $hashedEmail)->first();

            if (!$user) {
                Log::warning('No user found with hashed email.', ['hashed_email' => $hashedEmail]);
                return back()->withErrors(['email' => 'No user found with this email.']);
            }

            $tokenPayload = json_encode([
                'user_id'   => $user->id,
                'email'     => $normalizedEmail,
                'timestamp' => now()->addMinutes(30)->timestamp,
            ]);
            $hmac = hash_hmac('sha256', $tokenPayload, env('HASH_SECRET'));
            $encryptedToken = Crypt::encryptString(json_encode(['data' => $tokenPayload, 'hmac' => $hmac]));

            DB::table('password_reset_tokens')->updateOrInsert(
                ['hashed_email' => $hashedEmail],
                [
                    'hashed_email' => $hashedEmail,
                    'token'        => $encryptedToken,
                    'created_at'   => now(),
                ]
            );

            Mail::to($normalizedEmail)->send(new PasswordResetMail($user, $encryptedToken));

            Log::info("Password reset email sent to {$normalizedEmail}");
            return back()->with('success', 'Password reset link sent. Please check your email.');
        } catch (Exception $e) {
            Log::error('ForgotPasswordController error: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Unable to send reset email. Try again later.']);
        }
    }
}
