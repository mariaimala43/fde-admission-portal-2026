<?php
// SAVE AS: app/Http/Controllers/Fde/AppSettingsController.php

namespace App\Http\Controllers\Fde;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AppSettingsController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('portal.settings');
        $settings = AppSetting::all_settings();
        return view('fde.app_settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $this->authorize('portal.settings');

        $request->validate([
            'app_name'            => 'required|string|max:100',
            'app_tagline'         => 'nullable|string|max:200',
            'sidebar_footer_text' => 'nullable|string|max:100',
            'primary_color'       => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color'     => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'support_email'       => 'nullable|email|max:100',
            'support_phone'       => 'nullable|string|max:30',
            'app_logo'            => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'app_favicon'         => 'nullable|image|mimes:png,jpg,jpeg,ico|max:512',
            'maintenance_message' => 'nullable|string|max:500',
        ]);

        $current = AppSetting::all_settings();

        // ── App Logo upload ───────────────────────────────────────────
        if ($request->hasFile('app_logo')) {
            if (!empty($current['app_logo']) && Storage::disk('public')->exists($current['app_logo'])) {
                Storage::disk('public')->delete($current['app_logo']);
            }
            $path = $request->file('app_logo')->store('app/branding', 'public');
            AppSetting::set('app_logo', $path);
        }
        if ($request->boolean('remove_app_logo')) {
            if (!empty($current['app_logo']) && Storage::disk('public')->exists($current['app_logo'])) {
                Storage::disk('public')->delete($current['app_logo']);
            }
            AppSetting::set('app_logo', null);
        }

        // ── Favicon upload ────────────────────────────────────────────
        if ($request->hasFile('app_favicon')) {
            if (!empty($current['app_favicon']) && Storage::disk('public')->exists($current['app_favicon'])) {
                Storage::disk('public')->delete($current['app_favicon']);
            }
            $path = $request->file('app_favicon')->store('app/branding', 'public');
            AppSetting::set('app_favicon', $path);
        }
        if ($request->boolean('remove_app_favicon')) {
            if (!empty($current['app_favicon']) && Storage::disk('public')->exists($current['app_favicon'])) {
                Storage::disk('public')->delete($current['app_favicon']);
            }
            AppSetting::set('app_favicon', null);
        }

        // ── Scalar + boolean fields ───────────────────────────────────
        AppSetting::setMany([
            'app_name'            => $request->app_name,
            'app_tagline'         => $request->app_tagline,
            'sidebar_footer_text' => $request->sidebar_footer_text,
            'primary_color'       => $request->primary_color,
            'secondary_color'     => $request->secondary_color,
            'support_email'       => $request->support_email,
            'support_phone'       => $request->support_phone,
            'show_public_portal'  => $request->boolean('show_public_portal') ? '1' : '0',
            'maintenance_mode'    => $request->boolean('maintenance_mode') ? '1' : '0',
            'maintenance_message' => $request->maintenance_message,
        ]);

        return redirect()->route('fde.app-settings.index')
            ->with('success', 'Application settings saved successfully.');
    }
}
