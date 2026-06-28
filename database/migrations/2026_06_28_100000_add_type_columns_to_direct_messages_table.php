<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->dropForeignKeysOn('direct_messages', ['sender_id', 'recipient_id']);

        Schema::table('direct_messages', function (Blueprint $table) {
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

    private function dropForeignKeysOn(string $table, array $columns): void
    {
        $constraints = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->whereIn('COLUMN_NAME', $columns)
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->pluck('CONSTRAINT_NAME');

        foreach ($constraints as $constraint) {
            Schema::table($table, fn (Blueprint $t) => $t->dropForeign($constraint));
        }
    }
};
