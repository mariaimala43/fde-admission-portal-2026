<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\AuditLog;

class AuthController extends Controller
{
    // ── Show Login Page ────────────────────────────────────
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    // ── Handle Login ───────────────────────────────────────
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'email' => 'Invalid email or password.',
            ])->withInput();
        }

        if (!$user->is_active) {
            return back()->withErrors([
                'email' => 'Your account has been deactivated.',
            ])->withInput();
        }

        Auth::login($user, $request->boolean('remember'));

        AuditLog::record(
            action: 'login',
            institutionId: $user->institution_id
        );

        return redirect()->route('dashboard');
    }

    // ── Logout ─────────────────────────────────────────────
    public function logout(Request $request)
    {
        AuditLog::record(action: 'logout');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // ── Dashboard ──────────────────────────────────────────
    public function dashboard()
    {
        $user = Auth::user();

        // HoI must complete profile setup first
        if ($user->hasRole('hoi') && !$user->institution_id) {
            return redirect()->route('hoi.profile.setup');
        }

        // FDE Cell → dedicated FDE dashboard
        if ($user->hasRole('fde_cell')) {
            return redirect()->route('fde.dashboard');
        }

        // AEO → dedicated AEO sector dashboard
        if ($user->hasRole('aeo')) {
            return redirect()->route('aeo.dashboard');
        }

        // Director → dedicated director dashboard
        if ($user->hasRole('director')) {
            return redirect()->route('director.dashboard');
        }

        // ── Daily admission reminder for HOI ──────────────────────────
        $showReminder    = false;
        $reminderMessage = null;
        $activeYear      = null;

        if (
            $user->hasRole('hoi') &&
            $user->institution_id &&
            $user->institution?->classes_configured
        ) {
            $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
            $sessionKey = 'admission_reminder_shown_' . today()->toDateString();

            if (
                $activeYear &&
                $activeYear->isAdmissionOpen() &&
                ! $activeYear->isCutoffPassed() &&
                ! session()->has($sessionKey)
            ) {
                $todayDoneCount = \App\Models\DailyAdmission::where('institution_id', $user->institution_id)
                    ->whereDate('admission_date', today())
                    ->whereIn('status', ['submitted', 'verified'])
                    ->count();

                if ($todayDoneCount === 0) {
                    $showReminder    = true;
                    $cutoffTime      = \Carbon\Carbon::createFromTimeString($activeYear->daily_cutoff_time)
                        ->format('h:i A');
                    $reminderMessage = "Please update today's admission data before {$cutoffTime}.";
                    session([$sessionKey => true]);
                }
            }
        }

        return view('dashboard', compact('user', 'showReminder', 'reminderMessage', 'activeYear'));
    }

    // ── Me ─────────────────────────────────────────────────
    public function me()
    {
        $user = Auth::user()->load('institution');
        return response()->json([
            'success' => true,
            'data'    => [
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'role'        => $user->getRoleNames()->first(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
                'institution' => $user->institution ? [
                    'id'   => $user->institution->id,
                    'name' => $user->institution->name,
                ] : null,
            ],
        ]);
    }
}
