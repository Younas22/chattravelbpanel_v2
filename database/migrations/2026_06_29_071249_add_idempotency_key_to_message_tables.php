<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Lets the desktop client's retry queue safely replay a send after a dropped
// connection: a request that actually reached the server but lost its
// response is recognized by this key and returns the original message
// instead of creating a duplicate. Nullable + unique - MySQL allows multiple
// NULLs in a unique index, so existing web/Alpine sends (which never send
// this field) are completely unaffected.
return new class extends Migration
{
    public function up(): void
    {
        foreach (['messages', 'group_messages', 'direct_messages'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->string('idempotency_key', 64)->nullable()->unique()->after('id');
            });
        }
    }

    public function down(): void
    {
        foreach (['messages', 'group_messages', 'direct_messages'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn('idempotency_key');
            });
        }
    }
};
