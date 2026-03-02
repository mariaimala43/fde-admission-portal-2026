<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    // ── Show Forgot Password Form ────────────────────────
    public function showForm()
    {
        return view('auth.forgot-password');
    }

    // ── Send Reset Link ──────────────────────────────────
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'No account found with this email address.',
            ])->withInput();
        }

        if (!$user->is_active) {
            return back()->withErrors([
                'email' => 'This account has been deactivated. Contact FDE admin.',
            ])->withInput();
        }

        // Generate token
        $token = Str::random(64);

        // Delete any existing token for this email
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Store new token
        DB::table('password_reset_tokens')->insert([
            'email'      => $request->email,
            'token'      => bcrypt($token),
            'created_at' => Carbon::now(),
        ]);

        // Build reset URL
        $resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $request->email,
        ]);

        // Send email
        Mail::send('auth.emails.reset-password', [
            'user'     => $user,
            'resetUrl' => $resetUrl,
        ], function ($message) use ($request) {
            $message->to($request->email);
            $message->subject('FDE Admission Portal — Reset Your Password');
        });

        return back()->with('success', 'Password reset link has been sent to your email address.');
    }
}
