<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CannedReply;
use Illuminate\Http\Request;

class CannedReplyController extends Controller
{
    public function index()
    {
        $replies = CannedReply::orderBy('title')->get();
        return view('admin.canned-replies.index', compact('replies'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'    => 'required|string|max:200',
            'body'     => 'required|string|max:5000',
            'shortcut' => 'nullable|string|max:50|unique:canned_replies,shortcut',
        ]);

        CannedReply::create($data);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, CannedReply $cannedReply)
    {
        $data = $request->validate([
            'title'    => 'required|string|max:200',
            'body'     => 'required|string|max:5000',
            'shortcut' => 'nullable|string|max:50|unique:canned_replies,shortcut,' . $cannedReply->id,
        ]);

        $cannedReply->update($data);

        return response()->json(['success' => true]);
    }

    public function destroy(CannedReply $cannedReply)
    {
        $cannedReply->delete();
        return response()->json(['deleted' => true]);
    }

    public function search(Request $request)
    {
        $replies = CannedReply::when($request->q, fn($q) =>
            $q->where('title', 'like', "%{$request->q}%")
              ->orWhere('shortcut', 'like', "%{$request->q}%")
        )->get(['id', 'title', 'body', 'shortcut']);

        return response()->json($replies);
    }
}
