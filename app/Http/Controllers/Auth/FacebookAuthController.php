<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\Client;
use Exception;

class FacebookAuthController extends Controller
{
    public function redirectToFacebook()
    {
        try {
            return Socialite::driver('facebook')->redirect();
        } catch (Exception $e) {
            Log::error("Facebook redirect failed: {$e->getMessage()}");
            return redirect()->route('login')->with('error', 'Facebook login failed. Try again.');
        }
    }

    public function handleFacebookCallback()
    {
        try {
            $fbUser = Socialite::driver('facebook')->stateless()->user();

            $normalizedEmail = strtolower(trim($fbUser->getEmail()));
            $hashedEmail = hash_hmac('sha256', $normalizedEmail, env('HASH_SECRET'));

            $user = User::where('provider_id', $fbUser->getId())
                        ->orWhere('hashed_email', $hashedEmail)
                        ->first();

            if ($user) {
                $user->update([
                    'name'              => $user->name ?? $fbUser->getName(),
                    'provider_name'     => 'facebook',
                    'provider_id'       => $fbUser->getId(),
                    'profile_picture'   => $fbUser->getAvatar(),
                    'is_verified'       => true,
                    'email_verified_at' => now(),
                ]);

                Auth::login($user);
                return redirect('http://127.0.0.1:8000/client/dashboard')->with('success', 'Logged in with Facebook!');
            }

            $emailEncrypted = Crypt::encryptString($normalizedEmail);
            $finalEmail = Crypt::encryptString(json_encode([
                'email' => $emailEncrypted,
                'hash'  => $hashedEmail,
            ]));

            $user = User::create([
                'name'              => $fbUser->getName(),
                'email'             => $finalEmail,
                'hashed_email'      => $hashedEmail,
                'phone'             => null,
                'password'          => Hash::make(bin2hex(random_bytes(5))),
                'role'              => 'client',
                'provider_name'     => 'facebook',
                'provider_id'       => $fbUser->getId(),
                'profile_picture'   => $fbUser->getAvatar(),
                'is_verified'       => true,
                'email_verified_at' => now(),
            ]);

            
            Client::create([
                'user_id' => $user->id,
            ]);
            
            Log::info("Client created for user {$user->id}");


            Auth::login($user);
            return redirect('http://127.0.0.1:8000/client/dashboard')->with('success', 'Account created and logged in with Facebook!');

        } catch (Exception $e) {
            Log::error("Facebook login error: " . $e->getMessage());
            return redirect()->route('login')->with('error', 'Facebook login failed. Please try again.');
        }
    }

    
}
