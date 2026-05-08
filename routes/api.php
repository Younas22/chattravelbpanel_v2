<?php

use App\Http\Controllers\Chat\ConversationController;
use App\Http\Controllers\Chat\VisitorController;
use Illuminate\Support\Facades\Route;

// Widget/Chat API - CORS enabled, no auth required
Route::prefix('chat')->middleware(['throttle:60,1'])->group(function () {

    // Widget settings & FAQs
    Route::get('/settings', [ConversationController::class, 'settings']);
    Route::get('/faqs', [ConversationController::class, 'faqs']);

    // Visitor tracking
    Route::post('/visitor/identify', [VisitorController::class, 'identify']);
    Route::post('/visitor/heartbeat', [VisitorController::class, 'heartbeat']);
    Route::post('/visitor/offline', [VisitorController::class, 'offline']);

    // Conversations
    Route::post('/conversation/start', [ConversationController::class, 'start']);
    Route::get('/conversation/{conversationId}/messages', [ConversationController::class, 'messages']);
    Route::post('/conversation/{conversationId}/send', [ConversationController::class, 'send']);
    Route::post('/conversation/{conversationId}/typing', [ConversationController::class, 'typing']);
});
