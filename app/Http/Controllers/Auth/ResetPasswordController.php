<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Exception;

class ResetPasswordController extends Controller
{
    /**
     * Show the password reset form.
     */
    public function showResetForm($token)
    {
        try {
            Log::info("Received password reset token.", ['token' => $token]);
    
            $decrypted = Crypt::decryptString($token);
            Log::info('Decrypted token string', ['decrypted' => $decrypted]);
    
            $parsed = json_decode($decrypted, true);
    
            if (!isset($parsed['data'], $parsed['hmac'])) {
                throw new Exception("Invalid token structure.");
            }
    
            $calculatedHmac = hash_hmac('sha256', $parsed['data'], env('HASH_SECRET'));
            if ($calculatedHmac !== $parsed['hmac']) {
                throw new Exception("HMAC validation failed.");
            }
    
            $tokenData = json_decode($parsed['data'], true);
    
            if (!isset($tokenData['email'], $tokenData['timestamp'])) {
                throw new Exception("Invalid token data.");
            }
    
            if (now()->timestamp > $tokenData['timestamp']) {
                throw new Exception("Token has expired.");
            }
    
            $hashedEmail = hash_hmac('sha256', $tokenData['email'], env('HASH_SECRET'));
            $resetRecord = DB::table('password_reset_tokens')->where('hashed_email', $hashedEmail)->first();
    
            if (!$resetRecord) {
                throw new Exception("Reset token record not found.");
            }
    
            $stored = json_decode(Crypt::decryptString($resetRecord->token), true);
    
            if (!isset($stored['data'], $stored['hmac']) || $stored['hmac'] !== $parsed['hmac']) {
                throw new Exception("Stored token mismatch.");
            }
    
            return view('auth.passwords.reset', [
                'token' => $token,
                'email' => $tokenData['email'],
                'title' => 'Password Reset',
            ]);
    
        } catch (Exception $e) {
            Log::error('Reset form error: ' . $e->getMessage());
            return view('auth.passwords.email', [
                'title' => 'Request Password Reset',
            ])->withErrors([
                'email' => 'Invalid or expired password reset link. Please request a new one.',
            ]);
            
        }
    }
    
    /**
     * Handle the password reset submission.
     */
    public function reset(Request $request)
    {
        try {
            Log::info('Password reset request received.', ['email' => $request->email]);

            $validator = Validator::make($request->all(), [
                'token'    => 'required|string',
                'email'    => 'required|email',
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                    'regex:/[A-Z]/',
                    'regex:/[0-9]/',
                    'regex:/[^A-Za-z0-9]/',
                ],
            ], [
                'password.regex' => 'Password must contain at least 1 uppercase letter, 1 number, and 1 special character.',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $incoming = json_decode(Crypt::decryptString($request->token), true);
            if (!isset($incoming['data'], $incoming['hmac'])) {
                throw new Exception("Invalid token format.");
            }

            $expectedHmac = hash_hmac('sha256', $incoming['data'], env('HASH_SECRET'));
            if ($expectedHmac !== $incoming['hmac']) {
                throw new Exception("HMAC mismatch.");
            }

            $tokenData = json_decode($incoming['data'], true);

            if (now()->timestamp > $tokenData['timestamp']) {
                throw new Exception("Token has expired.");
            }

            $hashedEmail = hash_hmac('sha256', $request->email, env('HASH_SECRET'));

            $resetRecord = DB::table('password_reset_tokens')->where('hashed_email', $hashedEmail)->first();

            if (!$resetRecord) {
                throw new Exception("Reset token record not found.");
            }

            $stored = json_decode(Crypt::decryptString($resetRecord->token), true);
            if (!isset($stored['data'], $stored['hmac']) || $stored['hmac'] !== $incoming['hmac']) {
                throw new Exception("Stored token mismatch.");
            }

            $user = User::where('hashed_email', $hashedEmail)->firstOrFail();
            $user->password = Hash::make($request->password);
            $user->save();

            DB::table('password_reset_tokens')->where('hashed_email', $hashedEmail)->delete();

            Log::info("Password reset successful for user ID {$user->id}");
            return redirect()->route('login')->with('success', 'Your password has been reset successfully.');

        } catch (Exception $e) {
            Log::error('Password reset error: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Password reset failed. Please try again.']);
        }
    }
}
