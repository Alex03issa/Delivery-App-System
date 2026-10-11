<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Carbon\Carbon;
use App\Models\Client;
use Exception;

use App\Models\User;
use App\Mail\VerificationMail;

class signupController extends Controller
{
    /**
     * Show the sign-up page.
     */
    public function showSignUp()
    {
        return view('register.index', [
            'title' => 'Register'
        ]);
    }

    /**
     * Handle the sign-up form submission.
     */
    public function signUp(Request $request)
    {
        try {
            $role = in_array($request->input('role'), ['client', 'driver']) ? $request->input('role') : 'client';

            $normalizedEmail = strtolower(trim($request->input('email')));
            
            // Validate input
            $validator = Validator::make([
                'name' => $request->name,
                'email' => $normalizedEmail,
                'phone' => $request->phone,
                'password' => $request -> password,
                'password_confirmation' => $request -> password_confirmation,
                'role' => $role,
            ], [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'phone' => 'required|string|max:20',
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                    'regex:/[A-Z]/',
                    'regex:/[0-9]/',
                    'regex:/[^A-Za-z0-9]/'
                ],
                'role' => 'required|in:client,driver',
            ], [
                'password.regex' => 'Password must contain at least 1 uppercase letter, 1 number, and 1 special character.',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Hash & encrypt email
            $saltedHash = hash_hmac('sha256', $normalizedEmail, env('HASH_SECRET'));
            $firstEncryption = Crypt::encryptString($normalizedEmail);
            $encryptedEmail = Crypt::encryptString(json_encode([
                'email' => $firstEncryption,
                'hash' => $saltedHash,
            ]));

          
            // Save user
            $user = User::create([
                'name' => $request->name,
                'email' => $encryptedEmail,
                'hashed_email' => $saltedHash,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'provider_name' => 'deliveryapp',
                'role' => $role, 
                'created_at' => now(),
            ]);

            Client::create([
                'user_id' => $user->id,
            ]);
            
            Log::info("Client created for user {$user->id}");

              // Generate email verification token
              $tokenData = json_encode([
                'user_id' =>  $user->id,
                'email' => $normalizedEmail,
                'timestamp' => now()->addMinutes(30)->timestamp,
            ]);
            $hmac = hash_hmac('sha256', $tokenData, env('HASH_SECRET'));
            $verificationToken = Crypt::encryptString(json_encode(['data' => $tokenData, 'hmac' => $hmac]));
           
            $user->verification_token = $verificationToken;
            $user->save();

            // Send verification email
            $this->sendVerificationEmail($user);

            return redirect()->back()->with('success', 'Registration successful! Please check your email to verify your account.');

        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (QueryException $e) {
            Log::error("DB error during sign-up: " . $e->getMessage());
            return redirect()->back()->with('error', 'There was a problem creating your account.');
        } catch (Exception $e) {
            Log::error("Unexpected sign-up error: " . $e->getMessage());
            return redirect()->back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    /**
     * Send verification email with token.
     */
    protected function sendVerificationEmail(User $user)
    {
        try {
            $encryptedData = Crypt::decryptString($user->email);
            $data = json_decode($encryptedData, true);

            if (!isset($data['email'], $data['hash'])) {
                throw new Exception("Invalid encrypted email structure.");
            }

            $decryptedEmail = Crypt::decryptString($data['email']);
            $calculatedHash = hash_hmac('sha256', $decryptedEmail, env('HASH_SECRET'));

            if ($calculatedHash !== $data['hash']) {
                throw new Exception("Hash mismatch.");
            }

            Mail::to($decryptedEmail)->send(new VerificationMail($user));
            Log::info("Verification email sent to: {$decryptedEmail}");

        } catch (Exception $e) {
            Log::error("Failed to send verification email: " . $e->getMessage());
        }
    }
}
