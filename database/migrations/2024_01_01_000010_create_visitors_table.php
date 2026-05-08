<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 64)->unique();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('country_code', 5)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('browser', 100)->nullable();
            $table->string('os', 100)->nullable();
            $table->string('device', 50)->default('desktop');
            $table->text('current_page')->nullable();
            $table->text('landing_page')->nullable();
            $table->string('referrer')->nullable();
            $table->boolean('is_online')->default(false);
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();

            $table->index('is_online');
            $table->index('last_activity_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
