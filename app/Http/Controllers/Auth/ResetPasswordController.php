<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\User;

class ResetPasswordController extends Controller
{
    // ── Show Reset Password Form ─────────────────────────
    public function showForm(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');

        if (!$token || !$email) {
            return redirect()->route('login')->withErrors([
                'email' => 'Invalid password reset link.',
            ]);
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    // ── Handle Reset Password ────────────────────────────
    public function reset(Request $request)
    {
        $request->validate([
            'token'                 => 'required|string',
            'email'                 => 'required|email',
            'password'              => 'required|string|min:8|confirmed',
        ]);

        // Find reset record
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord) {
            return back()->withErrors([
                'email' => 'No password reset request found for this email.',
            ])->withInput();
        }

        // Verify token
        if (!Hash::check($request->token, $resetRecord->token)) {
            return back()->withErrors([
                'email' => 'Invalid or expired reset token.',
            ])->withInput();
        }

        // Check token expiry (60 minutes)
        if (Carbon::parse($resetRecord->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors([
                'email' => 'This reset link has expired. Please request a new one.',
            ])->withInput();
        }

        // Find user and update password
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'No account found with this email address.',
            ])->withInput();
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Delete token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')
            ->with('success', 'Password has been reset successfully. You can now login with your new password.');
    }
}
