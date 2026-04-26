<?php
// SAVE AS: app/Http/Controllers/Fde/PortalSettingsController.php
// UPDATED: now stores in app_settings DB table via AppSetting model
//          (was: Cache::put/get — fragile, lost on cache:clear)

namespace App\Http\Controllers\Fde;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PortalSettingsController extends Controller
{
    use AuthorizesRequests;

    // ── Keys managed by this controller ──────────────────────────────
    private array $booleanKeys = [
        'portal_enabled', 'show_vacancy', 'show_oosc', 'show_p2p',
        'show_contact', 'show_sector_filter', 'show_school_map', 'banner_enabled',
        'show_merit_list',
    ];

    public function index()
    {
        $this->authorize('portal.settings');
        $settings = AppSetting::all_settings();
        return view('fde.portal_settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $this->authorize('portal.settings');

        $request->validate([
            'portal_title'         => 'required|string|max:120',
            'portal_tagline'       => 'nullable|string|max:200',
            'admission_message'    => 'nullable|string|max:1000',
            'portal_notice'        => 'nullable|string|max:500',
            'banner_text'          => 'nullable|string|max:500',
            'max_results_per_page' => 'required|integer|min:5|max:100',
            'portal_logo'          => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'portal_favicon'       => 'nullable|image|mimes:png,jpg,jpeg,ico|max:512',
            'portal_hero_bg'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'banner_image'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'banner_colour'        => 'nullable|in:amber,blue,green,red,navy',
            'banner_link_text'     => 'nullable|string|max:100',
            'banner_link_url'      => 'nullable|url|max:300',
            'show_merit_list'      => 'nullable|boolean',
            'merit_list_title'     => 'nullable|string|max:200',
            'merit_list_description' => 'nullable|string|max:1000',
            'merit_list_file'      => 'nullable|file|mimes:pdf,xlsx,xls,csv|max:10240',
        ]);

        $current = AppSetting::all_settings();

        // ── Portal Logo ───────────────────────────────────────────────
        if ($request->hasFile('portal_logo')) {
            if (!empty($current['portal_logo']) && Storage::disk('public')->exists($current['portal_logo'])) {
                Storage::disk('public')->delete($current['portal_logo']);
            }
            AppSetting::set('portal_logo', $request->file('portal_logo')->store('portal/branding', 'public'));
        }
        if ($request->boolean('remove_portal_logo')) {
            if (!empty($current['portal_logo']) && Storage::disk('public')->exists($current['portal_logo'])) {
                Storage::disk('public')->delete($current['portal_logo']);
            }
            AppSetting::set('portal_logo', null);
        }

        // ── Portal Favicon ────────────────────────────────────────────
        if ($request->hasFile('portal_favicon')) {
            if (!empty($current['portal_favicon']) && Storage::disk('public')->exists($current['portal_favicon'])) {
                Storage::disk('public')->delete($current['portal_favicon']);
            }
            AppSetting::set('portal_favicon', $request->file('portal_favicon')->store('portal/branding', 'public'));
        }
        if ($request->boolean('remove_portal_favicon')) {
            if (!empty($current['portal_favicon']) && Storage::disk('public')->exists($current['portal_favicon'])) {
                Storage::disk('public')->delete($current['portal_favicon']);
            }
            AppSetting::set('portal_favicon', null);
        }

        // ── Hero background image ─────────────────────────────────────
        if ($request->hasFile('portal_hero_bg')) {
            if (!empty($current['portal_hero_bg']) && Storage::disk('public')->exists($current['portal_hero_bg'])) {
                Storage::disk('public')->delete($current['portal_hero_bg']);
            }
            AppSetting::set('portal_hero_bg', $request->file('portal_hero_bg')->store('portal/banners', 'public'));
        }
        if ($request->boolean('remove_portal_hero_bg')) {
            if (!empty($current['portal_hero_bg']) && Storage::disk('public')->exists($current['portal_hero_bg'])) {
                Storage::disk('public')->delete($current['portal_hero_bg']);
            }
            AppSetting::set('portal_hero_bg', null);
        }

        // ── Banner image ──────────────────────────────────────────────
        if ($request->hasFile('banner_image')) {
            if (!empty($current['banner_image']) && Storage::disk('public')->exists($current['banner_image'])) {
                Storage::disk('public')->delete($current['banner_image']);
            }
            AppSetting::set('banner_image', $request->file('banner_image')->store('portal/banners', 'public'));
        }
        if ($request->boolean('remove_banner')) {
            if (!empty($current['banner_image']) && Storage::disk('public')->exists($current['banner_image'])) {
                Storage::disk('public')->delete($current['banner_image']);
            }
            AppSetting::set('banner_image', null);
        }

        // ── Boolean toggles ───────────────────────────────────────────
        $boolData = [];
        foreach ($this->booleanKeys as $key) {
            $boolData[$key] = $request->boolean($key) ? '1' : '0';
        }

        // ── Scalar fields ─────────────────────────────────────────────
        AppSetting::setMany(array_merge($boolData, [
            'portal_title'         => $request->portal_title,
            'portal_tagline'       => $request->portal_tagline,
            'admission_message'    => $request->admission_message,
            'portal_notice'        => $request->portal_notice,
            'banner_text'          => $request->banner_text,
            'banner_colour'        => $request->input('banner_colour', 'amber'),
            'banner_link_text'     => $request->input('banner_link_text'),
            'banner_link_url'      => $request->input('banner_link_url'),
            'merit_list_title'     => $request->input('merit_list_title'),
            'merit_list_description' => $request->input('merit_list_description'),
            'max_results_per_page' => (string) (int) $request->max_results_per_page,
        ]));

        // ── Merit list file upload ─────────────────────────────────────
        if ($request->hasFile('merit_list_file')) {
            $old = AppSetting::get('merit_list_file');
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }
            $file     = $request->file('merit_list_file');
            $filename = 'merit-list-' . now()->format('Y-m-d-His') . '.' . $file->getClientOriginalExtension();
            $path     = $file->storeAs('portal/merit-lists', $filename, 'public');
            AppSetting::setMany([
                'merit_list_file'         => $path,
                'merit_list_original_name'=> $file->getClientOriginalName(),
                'merit_list_updated_at'   => now()->toDateTimeString(),
            ]);
        }

        // ── Remove merit list ──────────────────────────────────────────
        if ($request->boolean('remove_merit_list')) {
            $old = AppSetting::get('merit_list_file');
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }
            AppSetting::setMany([
                'merit_list_file'         => null,
                'merit_list_original_name'=> null,
                'merit_list_updated_at'   => null,
            ]);
        }

        return redirect()->route('fde.portal-settings.index')
            ->with('success', 'Portal settings saved successfully.');
    }

    // ── Static helper called by PortalController, public views ───────
    // Drop-in replacement for the old Cache::get version
    public static function get(string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return AppSetting::all_settings();
        }
        return AppSetting::get($key, $default);
    }
}
