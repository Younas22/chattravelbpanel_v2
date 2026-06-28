<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('direct_messages', function (Blueprint $table) {
            $table->dropForeign(['sender_id']);
            $table->dropForeign(['recipient_id']);
            $table->enum('sender_type', ['admin', 'ticket_user'])->default('ticket_user')->after('id');
            $table->enum('recipient_type', ['admin', 'ticket_user'])->default('ticket_user')->after('sender_id');
        });
    }

    public function down(): void
    {
        Schema::table('direct_messages', function (Blueprint $table) {
            $table->dropColumn(['sender_type', 'recipient_type']);
            $table->foreign('sender_id')->references('id')->on('ticket_users')->cascadeOnDelete();
            $table->foreign('recipient_id')->references('id')->on('ticket_users')->cascadeOnDelete();
        });
    }
};
