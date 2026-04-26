<?php
// SAVE AS: app/Http/Controllers/Fde/ThemeController.php

namespace App\Http\Controllers\Fde;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    use AuthorizesRequests;

    // All theme keys with their defaults
    public static array $defaults = [
        // Colors
        'theme_primary'          => '#4bad46',
        'theme_secondary'        => '#28a745',
        'theme_dark_bg'          => '#0a0e27',
        'theme_dark_bg2'         => '#0d1235',
        'theme_dark_card'        => '#1a1f3a',
        'theme_dark_sidebar_bg'  => '#0d1235',
        'theme_light_bg'         => '#f0f2fb',
        'theme_light_card'       => '#ffffff',
        'theme_light_sidebar_bg' => '#ffffff',
        'theme_active_text_dark' => '#6dda67',
        'theme_active_text_light'=> '#1a6617',

        // Sidebar
        'theme_sidebar_width'    => '260',   // px
        'theme_sidebar_font_size'=> '13',    // px
        'theme_sidebar_link_py'  => '8.5',   // px padding top/bottom

        // Topbar
        'theme_topbar_height'    => '58',    // px
        'theme_topbar_font_size' => '15',    // px title

        // Cards
        'theme_radius'           => '12',    // px
        'theme_radius_sm'        => '8',     // px
        'theme_card_padding'     => '20',    // px

        // Typography
        'theme_font_family'      => 'Inter',
        'theme_base_font_size'   => '14',    // px body

        // Default mode
        'theme_default_mode'     => 'dark',  // dark | light
    ];

    public function index()
    {
        $this->authorize('portal.settings');
        $saved    = AppSetting::all_settings();
        $settings = array_merge(static::$defaults, array_intersect_key($saved, static::$defaults));
        return view('fde.theme.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $this->authorize('portal.settings');

        $request->validate([
            'theme_primary'           => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_secondary'         => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_dark_bg'           => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_dark_bg2'          => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_dark_card'         => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_dark_sidebar_bg'   => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_light_bg'          => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_light_card'        => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_light_sidebar_bg'  => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_active_text_dark'  => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_active_text_light' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_sidebar_width'     => 'required|integer|min:180|max:380',
            'theme_sidebar_font_size' => 'required|integer|min:10|max:16',
            'theme_sidebar_link_py'   => 'required|numeric|min:4|max:16',
            'theme_topbar_height'     => 'required|integer|min:44|max:80',
            'theme_topbar_font_size'  => 'required|integer|min:12|max:20',
            'theme_radius'            => 'required|integer|min:0|max:24',
            'theme_radius_sm'         => 'required|integer|min:0|max:16',
            'theme_card_padding'      => 'required|integer|min:8|max:40',
            'theme_font_family'       => 'required|string|in:Inter,Roboto,Poppins,DM Sans,Nunito,Outfit',
            'theme_base_font_size'    => 'required|integer|min:12|max:18',
            'theme_default_mode'      => 'required|in:dark,light',
        ]);

        $data = $request->only(array_keys(static::$defaults));
        AppSetting::setMany($data);

        return redirect()->route('fde.theme.index')
            ->with('success', 'Theme saved successfully. Refresh any open tab to see changes.');
    }

    public function reset()
    {
        $this->authorize('portal.settings');
        AppSetting::setMany(static::$defaults);
        return redirect()->route('fde.theme.index')
            ->with('success', 'Theme reset to defaults.');
    }

    // Called by app.blade.php to get all theme vars as array
    public static function get(): array
    {
        $saved = AppSetting::all_settings();
        return array_merge(static::$defaults, array_intersect_key($saved, static::$defaults));
    }
}
