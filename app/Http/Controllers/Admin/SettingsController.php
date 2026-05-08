<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WidgetSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

class SettingsController extends Controller
{
    public function widget()
    {
        $defaults  = WidgetSetting::defaults();
        $saved     = WidgetSetting::getAll();
        $settings  = array_merge($defaults, $saved);
        return view('admin.settings.widget', compact('settings'));
    }

    public function updateWidget(Request $request)
    {
        $data = $request->validate([
            'primary_color'      => 'required|string|max:20',
            'text_color'         => 'required|string|max:20',
            'position'           => 'required|in:bottom-right,bottom-left',
            'border_radius'      => 'required|integer|min:0|max:50',
            'dark_mode'          => 'nullable',
            'welcome_message'    => 'required|string|max:500',
            'offline_message'    => 'required|string|max:500',
            'widget_title'       => 'required|string|max:100',
            'widget_subtitle'    => 'required|string|max:200',
            'auto_popup'         => 'nullable',
            'popup_delay'        => 'required|integer|min:0|max:60',
            'sound_enabled'      => 'nullable',
            'show_online_status' => 'nullable',
            'agent_name'         => 'required|string|max:100',
            'show_branding'      => 'nullable',
        ]);

        // Convert checkboxes
        foreach (['dark_mode', 'auto_popup', 'sound_enabled', 'show_online_status', 'show_branding'] as $key) {
            $data[$key] = $request->has($key) ? 'true' : 'false';
        }

        foreach ($data as $key => $value) {
            WidgetSetting::set($key, $value);
        }

        Cache::forget('widget_settings_all');

        return back()->with('success', 'Widget settings updated successfully.');
    }

    public function general()
    {
        return view('admin.settings.general');
    }

    public function updateGeneral(Request $request)
    {
        $request->validate([
            'smtp_host'     => 'nullable|string|max:255',
            'smtp_port'     => 'nullable|integer',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_from'     => 'nullable|email',
            'smtp_name'     => 'nullable|string|max:100',
        ]);

        // Update .env values
        $this->updateEnv([
            'MAIL_HOST'              => $request->smtp_host,
            'MAIL_PORT'              => $request->smtp_port,
            'MAIL_USERNAME'          => $request->smtp_username,
            'MAIL_FROM_ADDRESS'      => $request->smtp_from,
            'MAIL_FROM_NAME'         => $request->smtp_name,
        ]);

        if ($request->smtp_password) {
            $this->updateEnv(['MAIL_PASSWORD' => $request->smtp_password]);
        }

        Artisan::call('config:clear');

        return back()->with('success', 'General settings updated.');
    }

    public function pusher()
    {
        return view('admin.settings.pusher');
    }

    public function updatePusher(Request $request)
    {
        $request->validate([
            'pusher_app_id'      => 'required|string',
            'pusher_app_key'     => 'required|string',
            'pusher_app_secret'  => 'required|string',
            'pusher_app_cluster' => 'required|string',
        ]);

        $this->updateEnv([
            'PUSHER_APP_ID'      => $request->pusher_app_id,
            'PUSHER_APP_KEY'     => $request->pusher_app_key,
            'PUSHER_APP_SECRET'  => $request->pusher_app_secret,
            'PUSHER_APP_CLUSTER' => $request->pusher_app_cluster,
        ]);

        Artisan::call('config:clear');

        return back()->with('success', 'Pusher settings updated.');
    }

    private function updateEnv(array $data): void
    {
        $envFile = base_path('.env');
        $content = file_get_contents($envFile);

        foreach ($data as $key => $value) {
            $value = str_contains($value, ' ') ? "\"$value\"" : $value;
            if (preg_match("/^{$key}=/m", $content)) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
            } else {
                $content .= "\n{$key}={$value}";
            }
        }

        file_put_contents($envFile, $content);
    }
}
