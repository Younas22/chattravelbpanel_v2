<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WidgetTicketController extends Controller
{
    private function resolveUser(Request $request): ?TicketUser
    {
        $token = $request->header('X-Ticket-Token') ?: $request->input('ticket_token');
        if (!$token) return null;
        return TicketUser::where('widget_token', $token)->first();
    }

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'full_name' => 'required|string|max:200',
            'email'     => 'required|email|unique:ticket_users,email',
            'phone'     => 'required|string|max:30',
            'password'  => 'required|string|min:8',
        ]);

        $token = Str::random(60);

        $user = TicketUser::create([
            'full_name'    => $request->full_name,
            'email'        => $request->email,
            'phone'        => $request->phone,
            'password'     => Hash::make($request->password),
            'widget_token' => $token,
        ]);

        return response()->json([
            'token'     => $token,
            'id'        => $user->id,
            'full_name' => $user->full_name,
            'email'     => $user->email,
        ]);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = TicketUser::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid email or password.'], 401);
        }

        $token = Str::random(60);
        $user->update(['widget_token' => $token]);

        return response()->json([
            'token'     => $token,
            'id'        => $user->id,
            'full_name' => $user->full_name,
            'email'     => $user->email,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) return response()->json(['error' => 'Unauthenticated.'], 401);

        return response()->json([
            'id'           => $user->id,
            'full_name'    => $user->full_name,
            'email'        => $user->email,
            'phone'        => $user->phone,
            'profile_image'=> $user->profileImageUrl(),
            'social_links' => $user->social_links ?? [],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if ($user) {
            $user->update(['widget_token' => null]);
        }
        return response()->json(['ok' => true]);
    }

    public function createTicket(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) return response()->json(['error' => 'Unauthenticated.'], 401);

        $request->validate([
            'subject'     => 'required|string|max:200',
            'description' => 'required|string|max:5000',
            'priority'    => 'required|in:low,medium,high,urgent',
        ]);

        $ticket = Ticket::create([
            'ticket_user_id' => $user->id,
            'subject'        => $request->subject,
            'description'    => $request->description,
            'priority'       => $request->priority,
            'status'         => 'open',
        ]);

        TicketMessage::create([
            'ticket_id'   => $ticket->id,
            'sender_type' => 'user',
            'sender_id'   => $user->id,
            'body'        => $request->description,
        ]);

        return response()->json([
            'ok'            => true,
            'ticket_number' => $ticket->ticket_number,
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) return response()->json(['error' => 'Unauthenticated.'], 401);

        $request->validate([
            'full_name'    => 'sometimes|string|max:200',
            'phone'        => 'sometimes|nullable|string|max:30',
            'social_links' => 'sometimes|array',
            'social_links.twitter'   => 'nullable|url|max:200',
            'social_links.facebook'  => 'nullable|url|max:200',
            'social_links.instagram' => 'nullable|url|max:200',
            'social_links.linkedin'  => 'nullable|url|max:200',
        ]);

        $data = $request->only(['full_name', 'phone', 'social_links']);
        $user->update(array_filter($data, fn($v) => $v !== null));

        return response()->json(['ok' => true, 'full_name' => $user->fresh()->full_name]);
    }

    public function updateProfileImage(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) return response()->json(['error' => 'Unauthenticated.'], 401);

        $request->validate([
            'image' => 'required|image|max:2048|mimes:jpg,jpeg,png,gif,webp',
        ]);

        // Delete old image
        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $path = $request->file('image')->store('ticket-avatars', 'public');
        $user->update(['profile_image' => $path]);

        return response()->json([
            'ok'  => true,
            'url' => 'http://localhost/chattravelbpanel_v2/public/storage/' . $path,
        ]);
    }
}
