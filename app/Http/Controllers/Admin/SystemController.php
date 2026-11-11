<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SystemMaintenance;
use Illuminate\Http\Request;

/** "System Update" page: run server update steps from the admin panel. */
class SystemController extends Controller
{
    public function index()
    {
        return view('admin.system.index', ['status' => SystemMaintenance::status()]);
    }

    public function status()
    {
        return response()->json(SystemMaintenance::status());
    }

    /** Save the PHP CLI path the hosting company gave us (only if it really is php-cli). */
    public function savePhpPath(Request $request)
    {
        $request->validate(['path' => 'nullable|string|max:255']);
        $path = trim((string) $request->input('path', ''));

        if ($path === '') {
            \App\Models\WidgetSetting::set('php_cli_path', '');
            return response()->json(['ok' => true, 'message' => 'Cleared — using automatic detection.']);
        }

        $version = SystemMaintenance::verifyPhpCli($path);
        if (! $version) {
            return response()->json(['ok' => false, 'message' => 'That path is not a command-line PHP (php-cli) on this server.'], 422);
        }

        \App\Models\WidgetSetting::set('php_cli_path', $path);
        return response()->json(['ok' => true, 'message' => "Saved — PHP {$version} (cli)."]);
    }

    public function run(Request $request, string $step)
    {
        abort_unless(in_array($step, [...SystemMaintenance::STEPS, 'download-composer'], true), 404);

        $result = SystemMaintenance::run($step);
        logger()->info('System update step run', ['step' => $step, 'ok' => $result['ok'], 'by' => auth()->id()]);

        return response()->json($result);
    }
}
