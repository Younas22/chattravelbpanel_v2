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

    public function run(Request $request, string $step)
    {
        abort_unless(in_array($step, [...SystemMaintenance::STEPS, 'download-composer'], true), 404);

        $result = SystemMaintenance::run($step);
        logger()->info('System update step run', ['step' => $step, 'ok' => $result['ok'], 'by' => auth()->id()]);

        return response()->json($result);
    }
}
