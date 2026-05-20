<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class WidgetSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("widget_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("widget_setting_{$key}");
    }

    public static function getAll(): array
    {
        return Cache::remember('widget_settings_all', 3600, function () {
            return static::pluck('value', 'key')->toArray();
        });
    }

    // Default settings for the widget
    public static function defaults(): array
    {
        return [
            'primary_color'       => '#2563eb',
            'text_color'          => '#ffffff',
            'position'            => 'bottom-right',
            'border_radius'       => '16',
            'dark_mode'           => 'false',
            'welcome_message'     => 'Hi! How can we help you today? 👋',
            'offline_message'     => 'We are currently offline. Leave a message!',
            'widget_title'        => 'TravelBookingPanel Support',
            'widget_subtitle'     => 'Typically replies within minutes',
            'auto_popup'          => 'false',
            'popup_delay'         => '5',
            'sound_enabled'       => 'true',
            'show_online_status'  => 'true',
            'agent_name'          => 'Support Team',
            'agent_avatar'        => '',
            'show_branding'       => 'true',
            'system_name'         => 'TBP Chat',
            'system_logo'         => '',
            'company_image'       => '',
            'favicon'             => '',
            'whatsapp_contacts'   => '[]',
        ];
    }
}
