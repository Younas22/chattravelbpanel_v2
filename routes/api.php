<?php

use App\Http\Controllers\Chat\ConversationController;
use App\Http\Controllers\Chat\OfferPopupController;
use App\Http\Controllers\Chat\VisitorController;
use App\Http\Controllers\Chat\WidgetTicketController;
use Illuminate\Support\Facades\Route;

// Widget/Chat API - CORS enabled, no auth required
Route::prefix('chat')->middleware(['throttle:60,1'])->group(function () {

    // Widget settings & FAQs
    Route::get('/settings', [ConversationController::class, 'settings']);
    Route::get('/faqs', [ConversationController::class, 'faqs']);

    // Offer popup
    Route::get('/offer', [OfferPopupController::class, 'popup']);

    // Visitor tracking
    Route::post('/visitor/identify', [VisitorController::class, 'identify']);
    Route::post('/visitor/heartbeat', [VisitorController::class, 'heartbeat']);
    Route::post('/visitor/offline', [VisitorController::class, 'offline']);

    // Widget Ticket System
    Route::post('/ticket/register',       [WidgetTicketController::class, 'register']);
    Route::post('/ticket/login',          [WidgetTicketController::class, 'login']);
    Route::post('/ticket/logout',         [WidgetTicketController::class, 'logout']);
    Route::get('/ticket/me',              [WidgetTicketController::class, 'me']);
    Route::post('/ticket/create',         [WidgetTicketController::class, 'createTicket']);
    Route::post('/ticket/profile',        [WidgetTicketController::class, 'updateProfile']);
    Route::post('/ticket/profile-image',  [WidgetTicketController::class, 'updateProfileImage']);

    // Conversations
    Route::post('/conversation/start', [ConversationController::class, 'start']);
    Route::get('/conversation/{conversationId}/messages', [ConversationController::class, 'messages']);
    Route::post('/conversation/{conversationId}/send', [ConversationController::class, 'send']);
    Route::post('/conversation/{conversationId}/typing', [ConversationController::class, 'typing']);
});
