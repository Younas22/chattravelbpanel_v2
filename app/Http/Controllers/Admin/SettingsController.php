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
            'system_name'        => 'required|string|max:100',
            'system_logo'        => 'nullable|image|max:2048',
            'company_image'      => 'nullable|image|max:2048',
            'favicon'            => 'nullable|mimes:ico,png,jpg,jpeg,svg,gif|max:512',
            'whatsapp_contacts'              => 'nullable|array',
            'whatsapp_contacts.*.label'      => 'nullable|string|max:100',
            'whatsapp_contacts.*.number'     => 'nullable|string|max:30',
        ]);

        // Convert checkboxes
        foreach (['dark_mode', 'auto_popup', 'sound_enabled', 'show_online_status', 'show_branding'] as $key) {
            $data[$key] = $request->has($key) ? 'true' : 'false';
        }

        // Convert whatsapp_contacts array to JSON, filtering empty entries
        $contacts = [];
        foreach ($request->input('whatsapp_contacts', []) as $c) {
            $num = trim($c['number'] ?? '');
            if ($num !== '') {
                $contacts[] = [
                    'label'  => trim($c['label'] ?? ''),
                    'number' => preg_replace('/[^0-9]/', '', $num),
                ];
            }
        }
        $data['whatsapp_contacts'] = json_encode($contacts);

        $uploadDir = public_path('uploads/branding');
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        // Handle system logo upload — stored in public/uploads/branding/
        if ($request->hasFile('system_logo')) {
            $old = WidgetSetting::get('system_logo');
            if ($old && file_exists(base_path($old))) @unlink(base_path($old));
            $file = $request->file('system_logo');
            $filename = 'system_logo_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $data['system_logo'] = 'public/uploads/branding/' . $filename;
        } else {
            unset($data['system_logo']);
        }

        // Handle company/widget image upload — stored in public/uploads/branding/
        if ($request->hasFile('company_image')) {
            $old = WidgetSetting::get('company_image');
            if ($old && file_exists(base_path($old))) @unlink(base_path($old));
            $file = $request->file('company_image');
            $filename = 'company_image_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $data['company_image'] = 'public/uploads/branding/' . $filename;
        } else {
            unset($data['company_image']);
        }

        // Handle favicon upload — stored in public/uploads/branding/
        if ($request->hasFile('favicon')) {
            $old = WidgetSetting::get('favicon');
            if ($old && file_exists(base_path($old))) @unlink(base_path($old));
            $file = $request->file('favicon');
            $filename = 'favicon_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $data['favicon'] = 'public/uploads/branding/' . $filename;
        } else {
            unset($data['favicon']);
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

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:100',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $user = auth()->user();
        $user->name = $request->name;

        if ($request->hasFile('avatar')) {
            $uploadDir = public_path('uploads/avatars');
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            // Delete old avatar if it's in our uploads folder
            if ($user->avatar && str_starts_with($user->avatar, 'public/uploads/')) {
                $oldPath = base_path($user->avatar);
                if (file_exists($oldPath)) @unlink($oldPath);
            }

            $file     = $request->file('avatar');
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $user->avatar = 'public/uploads/avatars/' . $filename;
        }

        $user->save();

        return back()->with('success', 'Profile updated successfully.');
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
