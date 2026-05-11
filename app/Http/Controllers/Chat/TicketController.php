<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    protected string $guard = 'ticket_user';

    public function index()
    {
        if (!auth('ticket_user')->check()) {
            return redirect()->route('tickets.login');
        }
        $tickets = auth('ticket_user')->user()->tickets()->latest()->paginate(10);
        return view('tickets.index', compact('tickets'));
    }

    public function create()
    {
        if (!auth('ticket_user')->check()) {
            return redirect()->route('tickets.login');
        }
        return view('tickets.create');
    }

    public function store(Request $request)
    {
        if (!auth('ticket_user')->check()) {
            return redirect()->route('tickets.login');
        }

        $request->validate([
            'subject'     => 'required|string|max:200',
            'description' => 'required|string|max:5000',
            'priority'    => 'required|in:low,medium,high,urgent',
            'attachment'  => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,pdf,zip,txt',
        ]);

        $ticket = Ticket::create([
            'ticket_user_id' => auth('ticket_user')->id(),
            'subject'        => $request->subject,
            'description'    => $request->description,
            'priority'       => $request->priority,
            'status'         => 'open',
        ]);

        $msgData = [
            'ticket_id'   => $ticket->id,
            'sender_type' => 'user',
            'sender_id'   => auth('ticket_user')->id(),
            'body'        => $request->description,
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $msgData['attachment_path'] = $file->store('ticket-attachments/' . $ticket->id, 'public');
            $msgData['attachment_name'] = $file->getClientOriginalName();
            $msgData['attachment_mime'] = $file->getMimeType();
        }

        TicketMessage::create($msgData);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket created successfully!');
    }

    public function show(Ticket $ticket)
    {
        if (!auth('ticket_user')->check()) {
            return redirect()->route('tickets.login');
        }

        if ((int)$ticket->ticket_user_id !== (int)auth('ticket_user')->id()) {
            return redirect()->route('tickets.index')->with('error', 'You do not have access to this ticket.');
        }

        $ticket->load('messages');
        $ticket->messages()->where('sender_type', 'admin')->update(['is_read' => true]);
        $ticket->update(['unread_user' => 0]);

        return view('tickets.show', compact('ticket'));
    }

    public function reply(Request $request, Ticket $ticket)
    {
        if (!auth('ticket_user')->check()) {
            return redirect()->route('tickets.login');
        }

        if ((int)$ticket->ticket_user_id !== (int)auth('ticket_user')->id()) {
            return redirect()->route('tickets.index')->with('error', 'You do not have access to this ticket.');
        }

        $request->validate([
            'body'       => 'required|string|max:5000',
            'attachment' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,pdf,zip,txt',
        ]);

        $data = [
            'ticket_id'   => $ticket->id,
            'sender_type' => 'user',
            'sender_id'   => auth('ticket_user')->id(),
            'body'        => $request->body,
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $data['attachment_path'] = $file->store('ticket-attachments/' . $ticket->id, 'public');
            $data['attachment_name'] = $file->getClientOriginalName();
            $data['attachment_mime'] = $file->getMimeType();
        }

        TicketMessage::create($data);
        $ticket->increment('unread_admin');
        $ticket->update(['status' => 'open']);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Reply sent.');
    }

    // Auth for ticket users
    public function loginForm()
    {
        if (auth('ticket_user')->check()) return redirect()->route('tickets.index');
        return view('tickets.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (auth('ticket_user')->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return redirect()->route('tickets.index');
        }

        return back()->withErrors(['email' => 'Invalid credentials.']);
    }

    public function registerForm()
    {
        if (auth('ticket_user')->check()) return redirect()->route('tickets.index');
        return view('tickets.auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'full_name'    => 'required|string|max:200',
            'email'        => 'required|email|unique:ticket_users,email',
            'phone'        => 'nullable|string|max:30',
            'company_name' => 'nullable|string|max:200',
            'password'     => 'required|string|min:8|confirmed',
        ]);

        $user = TicketUser::create([
            'full_name'    => $request->full_name,
            'email'        => $request->email,
            'phone'        => $request->phone,
            'company_name' => $request->company_name,
            'password'     => Hash::make($request->password),
        ]);

        auth('ticket_user')->login($user);

        return redirect()->route('tickets.index');
    }

    public function logout()
    {
        auth('ticket_user')->logout();
        return redirect()->route('tickets.login');
    }

    public function profileForm()
    {
        if (!auth('ticket_user')->check()) {
            return redirect()->route('tickets.login');
        }
        $user = auth('ticket_user')->user();
        return view('tickets.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        if (!auth('ticket_user')->check()) {
            return redirect()->route('tickets.login');
        }

        $request->validate([
            'full_name'              => 'required|string|max:200',
            'phone'                  => 'nullable|string|max:30',
            'social_links.twitter'   => 'nullable|url|max:200',
            'social_links.facebook'  => 'nullable|url|max:200',
            'social_links.instagram' => 'nullable|url|max:200',
            'social_links.linkedin'  => 'nullable|url|max:200',
        ]);

        auth('ticket_user')->user()->update([
            'full_name'    => $request->full_name,
            'phone'        => $request->phone,
            'social_links' => $request->input('social_links', []),
        ]);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updateProfileImage(Request $request)
    {
        if (!auth('ticket_user')->check()) {
            return redirect()->route('tickets.login');
        }

        $request->validate([
            'image' => 'required|image|max:2048|mimes:jpg,jpeg,png,gif,webp',
        ]);

        $user = auth('ticket_user')->user();

        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $path = $request->file('image')->store('ticket-avatars', 'public');
        $user->update(['profile_image' => $path]);

        return back()->with('success', 'Profile image updated.');
    }
}
