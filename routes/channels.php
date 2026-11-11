<?php

use App\Models\Conversation;
use App\Models\Visitor;
use Illuminate\Support\Facades\Broadcast;

// Admin can access any private conversation channel
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    if ($user && $user->is_admin) return true;

    // Visitor access via session (handled at API level, not here)
    return false;
});

// Admin visitors channel (public for admin only)
Broadcast::channel('admin-conversations', function ($user) {
    return $user && $user->is_admin;
});

Broadcast::channel('admin-visitors', function ($user) {
    return $user && $user->is_admin;
});
