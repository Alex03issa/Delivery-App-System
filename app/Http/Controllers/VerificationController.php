<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Exception;
use Carbon\Carbon;

class VerificationController extends Controller
{


    public function showHomepageWithVerification()
    {
        if (auth()->check() && auth()->user()->is_verified) {
            
            return view('home', [
                'title' => 'Home '
            ]);
        }

        return redirect()->route('login')->with('error', 'Please verify your email before accessing the homepage.');
    }
    /**
     * Handle email verification (Web).
     *
     * @param string $encryptedToken
     * @return \Illuminate\Http\RedirectResponse
     */

     
    public function verify($encryptedToken)
    {
        try {
            Log::info('Starting email verification (Web).', ['token' => $encryptedToken]);

            $token = Crypt::decryptString($encryptedToken);
            $decodedToken = json_decode($token, true);

            if (!isset($decodedToken['data'], $decodedToken['hmac'])) {
                throw new Exception('Invalid token structure.');
            }

            $tokenData = $decodedToken['data'];
            $providedHmac = $decodedToken['hmac'];
            $calculatedHmac = hash_hmac('sha256', $tokenData, env('HASH_SECRET'));

            if ($calculatedHmac !== $providedHmac) {
                throw new Exception('Token integrity verification failed.');
            }

            $decodedData = json_decode($tokenData, true);
            $normalizedEmail = strtolower(trim($decodedData['email']));
            
            if (!isset($decodedData['user_id'], $normalizedEmail, $decodedData['timestamp'])) {
                throw new Exception('Invalid token data.');
            }

            if ($decodedData['timestamp'] < now()->timestamp) {
                throw new Exception('Token has expired.');
            }

            $hashedEmail = hash_hmac('sha256', $normalizedEmail, env('HASH_SECRET'));
            $user = User::where('id', $decodedData['user_id'])
                        ->where('hashed_email', $hashedEmail)
                        ->first();

            if (!$user) {
                throw new Exception('User not found for the given token.');
            }

            if ($user->is_verified) {
                return redirect()->route('login')->with('info', 'Email is already verified.');
            }

            $user->is_verified = true;
            $user->email_verified_at = now();
            $user->verification_token = null;
            $user->save();

            Auth::login($user);
            Log::info('Email verified successfully.', ['user_id' => $user->id]);
            
            if ($user->role === 'driver') {
                return redirect()->route('driver.register.details', ['user_id' => $user->id])
                                 ->with('success', 'Email verified. Please complete your driver registration.');
            }
            
            return redirect()->route('home.verified')
                             ->with('success', 'Email verified successfully.');
            
        } catch (Exception $e) {
            Log::error('Verification Error (Web): ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Verification failed: ' . $e->getMessage());
        }
    }
}
