<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use App\Mail\VerificationMail;
use App\Mail\LoginOtpMail;

use Exception;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showSignIn()
    {
        return view('login.index', [
            'title' => 'Login'
        ]);
    }


    /**
     * Handle the login request.
     */
    public function signIn(Request $request)
    {
        try {
            Log::info('Sign-in attempt started.');

            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            $hashedEmail = hash_hmac('sha256', $request->email, env('HASH_SECRET'));
            $user = User::where('hashed_email', $hashedEmail)->first();

            if (!$user) {
                return back()->withErrors(['email' => 'Invalid credentials.']);
            }

            try {
                $decrypted = json_decode(Crypt::decryptString($user->email), true);
                if (!isset($decrypted['email'], $decrypted['hash'])) {
                    Log::error("Invalid email structure for user ID {$user->id}");
                    return back()->withErrors(['email' => 'Invalid email format.']);
                }

                $email = Crypt::decryptString($decrypted['email']);
                if ($email !== $request->email) {
                    return back()->withErrors(['email' => 'Invalid credentials.']);
                }

                if (!Hash::check($request->password, $user->password)) {
                    Log::warning("Password mismatch for user ID {$user->id}");
                    return back()->withErrors(['password' => 'Invalid email or password.']);
                }

                if (!$user->is_verified) {
                    $this->regenerateVerificationToken($user);
                    return redirect()->route('login')->with('error', 'Email not verified. A new verification link has been sent.');
                }

                if (Hash::needsRehash($user->password)) {
                    $user->password = Hash::make($request->password);
                    $user->save();
                    Log::info("Password rehashed for user ID {$user->id}");
                }

              
                if ($user->skip_otp_once) {
                    $user->skip_otp_once = false;
                    $user->save();
                    Auth::login($user, true);
                    return $this->redirectByRole($user);
                }

                if ($request->has('remember')) {
                    $user->skip_otp_once = true;
                    $user->save();
                }
                
                // Otherwise, generate OTP and redirect to verify page
                $otpData = [
                    'id' => $user->id,
                    'code' => strval(rand(100000, 999999)),
                    'expires_at' => now()->addMinutes(3)->timestamp
                ];
                $user->otp_code = json_encode($otpData);
                $user->save();
                
                session([
                    'otp_email' => $user->email,
                    'otp_remember' => $request->has('remember'),
                ]);
                
                Mail::to($this->getDecryptedEmail($user))->send(new LoginOtpMail($user, $otpData));
                
                
                return redirect()->route('otp.verify.view');
                

            } catch (Exception $e) {
                Log::error("Decryption failed for user ID {$user->id}: " . $e->getMessage());
                return back()->withErrors(['email' => 'Login failed due to email processing error.']);
            }

        } catch (ValidationException $e) {
            Log::error("Validation error: {$e->getMessage()}");
            return back()->withErrors($e->validator)->withInput();
        } catch (QueryException $e) {
            Log::error("Query error: {$e->getMessage()}");
            return back()->with('error', 'A database error occurred. Please try again.');
        } catch (Exception $e) {
            Log::error("Unexpected login error: {$e->getMessage()}");
            return back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    protected function redirectByRole($user)
    {
        switch ($user->role) {
            case 'admin':
                return redirect()->route('admin.dashboard')->with('success', 'Logged in as Admin!');
            case 'client':
                return redirect()->route('client.dashboard')->with('success', 'Welcome, Client!');
            case 'driver':
                return redirect()->route('driver.dashboard')->with('success', 'Welcome, Driver!');
            default:
                Log::warning("Unknown role for user ID {$user->id}");
                return back()->withErrors(['email' => 'Unknown user role. Contact support.']);
        }
    }

protected function getDecryptedEmail($user)
{
    $data = json_decode(Crypt::decryptString($user->email), true);
    return Crypt::decryptString($data['email']);
}


    /**
     * Logout the user and invalidate session.
     */
    public function logout()
    {
        try {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();
            return redirect()->route('homepage')->with('success', 'Logged out successfully!');
        } catch (Exception $e) {
            return redirect()->route('homepage')->with('error', 'Logout failed. Try again.');
        }
    }

    /**
     * Regenerate and send verification token to user.
     */
    protected function regenerateVerificationToken(User $user)
    {
        try {
            $decryptedEmail = Crypt::decryptString(json_decode(Crypt::decryptString($user->email), true)['email']);

            $tokenData = json_encode([
                'user_id'   => $user->id,
                'email'     => $decryptedEmail,
                'timestamp' => now()->addMinutes(30)->timestamp,
            ]);

            $hmac = hash_hmac('sha256', $tokenData, env('HASH_SECRET'));
            $token = json_encode(['data' => $tokenData, 'hmac' => $hmac]);
            $user->update(['verification_token' => Crypt::encryptString($token)]);

            $this->sendVerificationEmail($user);
            Log::info("Verification token regenerated for user ID {$user->id}");
        } catch (Exception $e) {
            Log::error("Failed to regenerate token for user ID {$user->id}: {$e->getMessage()}");
        }
    }

    /**
     * Send the verification email to user.
     */
    protected function sendVerificationEmail(User $user)
    {
        try {
            $data = json_decode(Crypt::decryptString($user->email), true);
            if (!isset($data['email'], $data['hash'])) {
                throw new Exception("Invalid email structure.");
            }

            $decryptedEmail = Crypt::decryptString($data['email']);
            $calculatedHash = hash_hmac('sha256', $decryptedEmail, env('HASH_SECRET'));

            if ($calculatedHash !== $data['hash']) {
                throw new Exception("Hash verification failed.");
            }

            Mail::to($decryptedEmail)->send(new VerificationMail($user));
            Log::info("Verification email sent to: {$decryptedEmail}");
        } catch (Exception $e) {
            Log::error("Failed to send verification email: " . $e->getMessage());
        }
    }
}
