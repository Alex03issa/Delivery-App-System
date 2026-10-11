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

        $userId = session('otp_user_id');
        if (!$userId) {
            return redirect()->route('login')->withErrors(['otp' => 'Session expired. Please login again.']);
        }

        $user = User::find($userId);

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

        if ((int) ($otpData['id'] ?? 0) !== (int) $user->id || !hash_equals((string) $otpData['code'], (string) $request->otp)) {
            return back()->withErrors(['otp' => 'Incorrect OTP.']);
        }

        $remember = session('otp_remember', false); 

        if ($remember) {
            $user->skip_otp_once = true;
        }

        $user->otp_code = null;
        $user->save();

        Auth::login($user, $remember);
        session()->forget(['otp_user_id', 'otp_remember']);
        session()->regenerate();

        Log::info("OTP verified and user logged in: {$user->id}");

        return redirect()->route($user->role === 'admin' ? 'admin.dashboard' :
            ($user->role === 'client' ? 'client.dashboard' : 'driver.dashboard')
        )->with('success', 'Welcome back!');
    }
}
