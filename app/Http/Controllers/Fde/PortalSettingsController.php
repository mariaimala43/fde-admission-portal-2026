<?php
// SAVE AS: app/Http/Controllers/Fde/PortalSettingsController.php

namespace App\Http\Controllers\Fde;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class PortalSettingsController extends Controller
{
    private array $defaults = [
        'portal_enabled'       => true,
        'portal_title'         => 'FDE School Admission Portal 2026',
        'portal_subtitle'      => 'Federal Directorate of Education — Islamabad',
        'show_vacancy'         => true,
        'show_oosc'            => false,
        'show_p2p'             => false,
        'show_contact'         => true,
        'show_sector_filter'   => true,
        'show_school_map'      => false,
        'admission_message'    => '',
        'portal_notice'        => '',
        'max_results_per_page' => 20,
        'banner_image'         => null,
        'banner_text'          => '',
        'banner_enabled'       => true,
    ];

    public function index()
    {
        $settings = array_merge($this->defaults, Cache::get('portal_settings', []));
        return view('fde.portal_settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'portal_title'         => 'required|string|max:120',
            'portal_subtitle'      => 'nullable|string|max:200',
            'admission_message'    => 'nullable|string|max:1000',
            'portal_notice'        => 'nullable|string|max:500',
            'banner_text'          => 'nullable|string|max:500',
            'max_results_per_page' => 'required|integer|min:5|max:100',
            'banner_image'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $current = array_merge($this->defaults, Cache::get('portal_settings', []));

        // Banner image upload
        if ($request->hasFile('banner_image')) {
            if (!empty($current['banner_image']) && Storage::disk('public')->exists($current['banner_image'])) {
                Storage::disk('public')->delete($current['banner_image']);
            }
            $current['banner_image'] = $request->file('banner_image')
                ->store('portal/banners', 'public');
        }

        // Remove banner
        if ($request->boolean('remove_banner')) {
            if (!empty($current['banner_image']) && Storage::disk('public')->exists($current['banner_image'])) {
                Storage::disk('public')->delete($current['banner_image']);
            }
            $current['banner_image'] = null;
        }

        // Boolean toggles
        foreach (['portal_enabled','show_vacancy','show_oosc','show_p2p','show_contact',
                  'show_sector_filter','show_school_map','banner_enabled'] as $key) {
            $current[$key] = $request->boolean($key);
        }

        // Scalar fields
        $current['portal_title']         = $request->portal_title;
        $current['portal_subtitle']      = $request->portal_subtitle;
        $current['admission_message']    = $request->admission_message;
        $current['portal_notice']        = $request->portal_notice;
        $current['banner_text']          = $request->banner_text;
        $current['max_results_per_page'] = (int) $request->max_results_per_page;

        Cache::put('portal_settings', $current, now()->addYears(10));

        return redirect()->route('fde.portal-settings.index')
            ->with('success', 'Portal settings saved successfully.');
    }

    public static function get(string $key = null, mixed $default = null): mixed
    {
        $defaults = [
            'portal_enabled'    => true,
            'banner_enabled'    => true,
            'banner_image'      => null,
            'banner_text'       => '',
            'portal_notice'     => '',
            'admission_message' => '',
            'show_vacancy'      => true,
            'show_contact'      => true,
        ];
        $settings = array_merge($defaults, Cache::get('portal_settings', []));
        return $key === null ? $settings : ($settings[$key] ?? $default);
    }
}
