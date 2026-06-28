<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\CannedReplyController;
use App\Http\Controllers\Admin\ConversationController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\GlobalOfferController;
use App\Http\Controllers\Admin\GroupController as AdminGroupController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\Admin\VisitorController;
use App\Http\Controllers\Chat\GroupChatController;
use App\Http\Controllers\Chat\TicketController;
use App\Http\Controllers\WidgetScriptController;
use Illuminate\Support\Facades\Route;

// Widget scripts
Route::get('/widget.js', WidgetScriptController::class)->name('widget.js');
Route::get('/offer-popup.js', function () {
    return response()->file(public_path('offer-popup.js'), [
        'Content-Type'  => 'application/javascript',
        'Cache-Control' => 'public, max-age=300',
    ]);
})->name('offer-popup.js');

// Redirect root to admin
Route::get('/', fn() => redirect()->route('admin.dashboard'));

// =========================
// Admin Auth
// =========================
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// =========================
// Admin Panel (protected)
// =========================
Route::prefix('admin')->name('admin.')->middleware(['web', 'admin.auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/stats', [DashboardController::class, 'stats'])->name('stats');

    // Conversations
    Route::get('/conversations/list', [ConversationController::class, 'listJson'])->name('conversations.list');
    Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations.index');
    Route::get('/conversations/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');
    Route::get('/conversations/{conversation}/messages', [ConversationController::class, 'pollMessages'])->name('conversations.poll');
    Route::post('/conversations/{conversation}/message', [ConversationController::class, 'sendMessage'])->name('conversations.message');
    Route::post('/conversations/{conversation}/typing', [ConversationController::class, 'typing'])->name('conversations.typing');
    Route::patch('/conversations/{conversation}/close', [ConversationController::class, 'close'])->name('conversations.close');
    Route::patch('/conversations/{conversation}/reopen', [ConversationController::class, 'reopen'])->name('conversations.reopen');
    Route::delete('/conversations/{conversation}', [ConversationController::class, 'destroy'])->name('conversations.destroy');

    // Tickets
    Route::get('/tickets', [AdminTicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/{ticket}', [AdminTicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{ticket}/reply', [AdminTicketController::class, 'reply'])->name('tickets.reply');
    Route::patch('/tickets/{ticket}/status', [AdminTicketController::class, 'updateStatus'])->name('tickets.status');
    Route::patch('/tickets/{ticket}/priority', [AdminTicketController::class, 'updatePriority'])->name('tickets.priority');

    // Ticket Users
    Route::get('/ticket-users', [AdminTicketController::class, 'ticketUsers'])->name('ticket-users.index');
    Route::post('/ticket-users', [AdminTicketController::class, 'storeUser'])->name('ticket-users.store');

    // Groups
    Route::get('/groups', [AdminGroupController::class, 'index'])->name('groups.index');
    Route::post('/groups', [AdminGroupController::class, 'store'])->name('groups.store');
    Route::get('/groups/{group}', [AdminGroupController::class, 'show'])->name('groups.show');
    Route::get('/groups/{group}/messages', [AdminGroupController::class, 'pollMessages'])->name('groups.poll');
    Route::post('/groups/{group}/message', [AdminGroupController::class, 'sendMessage'])->name('groups.message');
    Route::post('/groups/{group}/members', [AdminGroupController::class, 'addMember'])->name('groups.members.add');
    Route::delete('/groups/{group}/members/{ticketUser}', [AdminGroupController::class, 'removeMember'])->name('groups.members.remove');
    Route::delete('/groups/{group}', [AdminGroupController::class, 'destroy'])->name('groups.destroy');

    // Visitors
    Route::get('/visitors', [VisitorController::class, 'index'])->name('visitors.index');
    Route::get('/visitors/live', [VisitorController::class, 'live'])->name('visitors.live');
    Route::get('/visitors/{visitor}', [VisitorController::class, 'show'])->name('visitors.show');
    Route::post('/visitors/cleanup', [VisitorController::class, 'cleanup'])->name('visitors.cleanup');

    // Canned Replies (search must be before parameterized routes)
    Route::get('/canned-replies/search', [CannedReplyController::class, 'search'])->name('canned-replies.search');
    Route::get('/canned-replies', [CannedReplyController::class, 'index'])->name('canned-replies.index');
    Route::post('/canned-replies', [CannedReplyController::class, 'store'])->name('canned-replies.store');
    Route::put('/canned-replies/{cannedReply}', [CannedReplyController::class, 'update'])->name('canned-replies.update');
    Route::delete('/canned-replies/{cannedReply}', [CannedReplyController::class, 'destroy'])->name('canned-replies.destroy');

    // FAQs
    Route::get('/faqs', [FaqController::class, 'index'])->name('faqs.index');
    Route::post('/faqs', [FaqController::class, 'store'])->name('faqs.store');
    Route::put('/faqs/{faq}', [FaqController::class, 'update'])->name('faqs.update');
    Route::post('/faqs/order', [FaqController::class, 'updateOrder'])->name('faqs.order');
    Route::delete('/faqs/{faq}', [FaqController::class, 'destroy'])->name('faqs.destroy');

    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    // Countries & National Holidays
    Route::get('/countries', [CountryController::class, 'index'])->name('countries.index');
    Route::post('/countries/{country}/fetch-holidays', [CountryController::class, 'fetchHolidays'])->name('countries.fetch-holidays');
    Route::get('/countries/{country}/holidays', [CountryController::class, 'holidays'])->name('countries.holidays');

    // Global Offer
    Route::get('/offers', [GlobalOfferController::class, 'index'])->name('offers.index');
    Route::post('/offers', [GlobalOfferController::class, 'save'])->name('offers.save');
    Route::post('/offers/toggle', [GlobalOfferController::class, 'toggle'])->name('offers.toggle');

    // Settings
    Route::get('/settings/widget', [SettingsController::class, 'widget'])->name('settings.widget');
    Route::post('/settings/widget', [SettingsController::class, 'updateWidget'])->name('settings.widget.update');
    Route::get('/settings/general', [SettingsController::class, 'general'])->name('settings.general');
    Route::post('/settings/general', [SettingsController::class, 'updateGeneral'])->name('settings.general.update');
    Route::get('/settings/pusher', [SettingsController::class, 'pusher'])->name('settings.pusher');
    Route::post('/settings/pusher', [SettingsController::class, 'updatePusher'])->name('settings.pusher.update');
    Route::post('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile.update');
});

// =========================
// Public Ticket System (clean URLs)
// =========================
Route::name('tickets.')->group(function () {
    Route::get('/login',    [TicketController::class, 'loginForm'])->name('login');
    Route::post('/login',   [TicketController::class, 'login'])->name('login.post');
    Route::get('/register', [TicketController::class, 'registerForm'])->name('register');
    Route::post('/register',[TicketController::class, 'register'])->name('register.post');
    Route::post('/logout',  [TicketController::class, 'logout'])->name('logout');

    Route::get('/tickets',         [TicketController::class, 'index'])->name('index');
    Route::get('/tickets/create',  [TicketController::class, 'create'])->name('create');
    Route::post('/tickets/create', [TicketController::class, 'store'])->name('store');
    Route::get('/profile',         [TicketController::class, 'profileForm'])->name('profile');
    Route::post('/profile',        [TicketController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/image',  [TicketController::class, 'updateProfileImage'])->name('profile.image');
    Route::get('/tickets/{ticket}',       [TicketController::class, 'show'])->name('show');
    Route::post('/tickets/{ticket}/reply',[TicketController::class, 'reply'])->name('reply');

    // Group chat
    Route::get('/chat',                  [GroupChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{group}',          [GroupChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{group}/message', [GroupChatController::class, 'sendMessage'])->name('chat.message');
    Route::get('/chat/{group}/messages', [GroupChatController::class, 'pollMessages'])->name('chat.poll');
});
