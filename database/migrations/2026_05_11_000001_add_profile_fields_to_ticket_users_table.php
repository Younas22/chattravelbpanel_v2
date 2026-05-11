<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_users', function (Blueprint $table) {
            $table->string('widget_token', 80)->nullable()->unique()->after('password');
            $table->string('profile_image')->nullable()->after('widget_token');
            $table->json('social_links')->nullable()->after('profile_image');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_users', function (Blueprint $table) {
            $table->dropColumn(['widget_token', 'profile_image', 'social_links']);
        });
    }
};
