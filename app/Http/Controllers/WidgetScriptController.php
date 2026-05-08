<?php

namespace App\Http\Controllers;

use App\Models\WidgetSetting;

class WidgetScriptController extends Controller
{
    public function __invoke()
    {
        $settings = array_merge(WidgetSetting::defaults(), WidgetSetting::getAll());

        $response = response()->file(public_path('widget.js'), [
            'Content-Type'  => 'application/javascript',
            'Cache-Control' => 'public, max-age=300',
        ]);

        return $response;
    }
}
