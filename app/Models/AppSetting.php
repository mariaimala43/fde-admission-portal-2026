<?php

// SAVE AS: app/Models/AppSetting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    protected $primaryKey = 'key';
    public    $incrementing = false;
    protected $keyType      = 'string';

    protected $fillable = ['key', 'value'];

    // ── Default values ────────────────────────────────────────────────
    // Used when no row exists in DB yet (fresh install or after reset)
    public static array $defaults = [
        'app_name'           => 'FDE Admission Portal',
        'app_tagline'        => 'Federal Directorate of Education · Admission System 2026–27',
        'app_logo'           => null,   // path relative to storage/app/public
        'app_favicon'        => null,   // path relative to storage/app/public
        'primary_color'      => '#4bad46',
        'secondary_color'    => '#1d4ed8',
        'sidebar_footer_text'=> 'Admission System 2026–27',
        'support_email'      => '',
        'support_phone'      => '',
        'show_public_portal' => '1',
        'portal_title'       => 'FDE School Admission Portal',
        'portal_tagline'     => 'Find and apply to Federal Government Schools in Islamabad',
        'portal_logo'        => null,
        'portal_favicon'     => null,
        'portal_hero_bg'     => null,
        'maintenance_mode'   => '0',
        'maintenance_message'=> 'The portal is currently under maintenance. Please check back later.',

        // Portal banner (existing keys + enhancements)
        'banner_enabled'     => '0',
        'banner_text'        => null,
        'banner_colour'      => 'amber',
        'banner_link_text'   => null,
        'banner_link_url'    => null,

        // Merit list
        'show_merit_list'           => '0',
        'merit_list_title'          => null,
        'merit_list_description'    => null,
        'merit_list_file'           => null,
        'merit_list_original_name'  => null,
        'merit_list_updated_at'     => null,
    ];

    // ── Cache key ─────────────────────────────────────────────────────
    private const CACHE_KEY = 'app_settings_all';

    // ── Get all settings as associative array (cached) ────────────────
    public static function all_settings(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            $rows = static::all()->pluck('value', 'key')->toArray();
            return array_merge(static::$defaults, $rows);
        });
    }

    // ── Get a single setting value ────────────────────────────────────
    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = static::all_settings();
        return $settings[$key] ?? $default ?? static::$defaults[$key] ?? null;
    }

    // ── Set a single setting ──────────────────────────────────────────
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget(self::CACHE_KEY);
    }

    // ── Set many settings at once ─────────────────────────────────────
    public static function setMany(array $data): void
    {
        foreach ($data as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        Cache::forget(self::CACHE_KEY);
    }

    // ── Clear cache (call after any update) ───────────────────────────
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
