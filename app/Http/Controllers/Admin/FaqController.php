<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuickFaq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = QuickFaq::orderBy('sort_order')->get();
        return view('admin.faqs.index', compact('faqs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'question'         => 'required|string|max:500',
            'answer'           => 'required|string|max:5000',
            'sort_order'       => 'integer|min:0',
            'is_active'        => 'boolean',
            'show_chat_button' => 'boolean',
        ]);

        QuickFaq::create($data);
        return response()->json(['success' => true]);
    }

    public function update(Request $request, QuickFaq $faq)
    {
        $data = $request->validate([
            'question'         => 'required|string|max:500',
            'answer'           => 'required|string|max:5000',
            'sort_order'       => 'integer|min:0',
            'is_active'        => 'boolean',
            'show_chat_button' => 'boolean',
        ]);

        $faq->update($data);
        return response()->json(['success' => true]);
    }

    public function updateOrder(Request $request)
    {
        $request->validate(['order' => 'required|array']);
        foreach ($request->order as $index => $id) {
            QuickFaq::where('id', $id)->update(['sort_order' => $index]);
        }
        return response()->json(['success' => true]);
    }

    public function destroy(QuickFaq $faq)
    {
        $faq->delete();
        return response()->json(['deleted' => true]);
    }
}
