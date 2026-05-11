<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GlobalOffer;
use Illuminate\Http\Request;

class GlobalOfferController extends Controller
{
    public function index()
    {
        $offer = GlobalOffer::first();
        return view('admin.offers.index', compact('offer'));
    }

    public function toggle()
    {
        $offer = GlobalOffer::firstOrCreate(['id' => 1], [
            'original_price' => 0,
            'discount_price' => 0,
            'is_active'      => false,
        ]);
        $offer->update(['is_active' => !$offer->is_active]);

        $status = $offer->is_active ? 'activated' : 'deactivated';
        return redirect()->route('admin.offers.index')->with('success', "Global offer {$status}.");
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'label'          => 'nullable|string|max:255',
            'original_price' => 'required|numeric|min:0',
            'discount_price' => 'required|numeric|min:0',
            'is_active'      => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        GlobalOffer::updateOrCreate(['id' => 1], $data);

        return redirect()->route('admin.offers.index')->with('success', 'Global offer saved successfully.');
    }
}
