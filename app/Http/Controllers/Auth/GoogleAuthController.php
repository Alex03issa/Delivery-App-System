<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Models\Client;
use Exception;

class GoogleAuthController extends Controller
{
    public function redirectToGoogle()
    {
        try {
            return Socialite::driver('google')->redirect();
        } catch (Exception $e) {
            Log::error("Google redirect failed: {$e->getMessage()}");
            return redirect()->route('login')->with('error', 'Failed to initiate Google login.');
        }
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $normalizedEmail = strtolower(trim($googleUser->getEmail()));
            Log::info('Normalized email', ['email' => $normalizedEmail]);
            $hashedEmail = hash_hmac('sha256', $normalizedEmail, env('HASH_SECRET'));
            Log::info('Hashed email', ['hash' => $hashedEmail]);

            $user = User::where('email', $googleUser->getEmail())
                        ->orWhere('provider_id', $googleUser->getId())
                        ->orWhere('hashed_email', $hashedEmail)
                        ->first();

            if ($user) {
                $user->update([
                    'name' => $user->name ?? $googleUser->getName(),
                    'profile_picture' => $googleUser->getAvatar(),
                    'provider_name' => 'google',
                    'provider_id' => $googleUser->getId(),
                    'is_verified' => true,
                    'email_verified_at' => now(),
                ]);

                Auth::login($user);
                return redirect()->route('client.dashboard')->with('success', 'Logged in with Facebook!');
            }

            
            $firstEncryption = Crypt::encryptString($normalizedEmail);
            $finalEmail = Crypt::encryptString(json_encode([
                'email' => $firstEncryption,
                'hash'  => $hashedEmail,
            ]));

            $user = User::create([
                'name'              => $googleUser->getName(),
                'email'             => $finalEmail,
                'hashed_email'      => $hashedEmail,
                'phone'             => null,
                'password'          => Hash::make($this->generateRandomPassword()),
                'role'              => 'client',
                'profile_picture'   => $googleUser->getAvatar(),
                'provider_name'     => 'google',
                'provider_id'       => $googleUser->getId(),
                'is_verified'       => true,
                'email_verified_at' => now(),
            ]);

            Client::create([
                'user_id' => $user->id,
            ]);
            
            Log::info("Client created for user {$user->id}");

            Auth::login($user);
            return redirect()->route('client.dashboard')->with('success', 'Account created with Google!');

        } catch (InvalidStateException $e) {
            return redirect()->route('login')->with('error', 'Invalid login attempt. Try again.');
        } catch (QueryException $e) {
            Log::error("DB Error: " . $e->getMessage());
            return redirect()->route('login')->with('error', 'Database error during login.');
        } catch (Exception $e) {
            Log::error("Google Login Error: " . $e->getMessage());
            return redirect()->route('login')->with('error', 'Something went wrong. Please try again.');
        }
    }

    private function generateRandomPassword($length = 10)
    {
        return bin2hex(random_bytes($length / 2));
    }

}
