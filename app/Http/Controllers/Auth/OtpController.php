<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OtpController extends Controller
{
    public function showForm()
    {
        return view('auth.otp_verify', [
            'title' => 'OTP Verifictaion'
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $email = session('otp_email');
        if (!$email) {
            return redirect()->route('login')->withErrors(['otp' => 'Session expired. Please login again.']);
        }

        $user = User::whereRaw("BINARY email = ?", [$email])->first(); // or use hashed_email if applicable

        if (!$user) {
            return redirect()->route('login')->withErrors(['otp' => 'User not found.']);
        }

        $otpData = json_decode($user->otp_code, true);
        if (
            !$otpData ||
            !isset($otpData['code'], $otpData['expires_at']) ||
            $otpData['expires_at'] < now()->timestamp
        ) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP.']);
        }

        if ($request->otp !== $otpData['code']) {
            return back()->withErrors(['otp' => 'Incorrect OTP.']);
        }

        $remember = session('otp_remember', false); 

        if ($remember) {
            $user->skip_otp_once = true;
        }

        $user->otp_code = null;
        $user->save();

        Auth::login($user, $remember);
        session()->forget(['otp_email', 'otp_remember']);

        Log::info("OTP verified and user logged in: {$user->id}");

        return redirect()->route($user->role === 'admin' ? 'admin.dashboard' :
            ($user->role === 'client' ? 'client.dashboard' : 'driver.dashboard')
        )->with('success', 'Welcome back!');
    }
}
