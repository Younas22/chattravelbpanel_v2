<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

// Group chat (and customer <-> customer direct messages, which only existed
// between members of a shared group) has been removed. Customer <-> support
// direct messages, widget conversations and tickets are untouched.
return new class extends Migration
{
    public function up(): void
    {
        $attachments = public_path('attachments');

        // Customer <-> customer DMs: delete their attachment files, then the rows.
        if (Schema::hasTable('direct_messages')) {
            $userToUser = DB::table('direct_messages')
                ->where('sender_type', 'ticket_user')
                ->where('recipient_type', 'ticket_user');

            foreach ((clone $userToUser)->whereNotNull('attachment_path')->pluck('attachment_path') as $path) {
                File::delete($attachments . '/' . $path);
            }

            $userToUser->delete();
        }

        Schema::dropIfExists('group_messages');
        Schema::dropIfExists('group_user');
        Schema::dropIfExists('groups');

        // Group avatars and group message attachments.
        foreach (File::glob($attachments . '/group-*', GLOB_ONLYDIR) ?: [] as $dir) {
            File::deleteDirectory($dir);
        }
    }

    public function down(): void
    {
        // Irreversible: the group chat feature and its data were removed.
    }
};
