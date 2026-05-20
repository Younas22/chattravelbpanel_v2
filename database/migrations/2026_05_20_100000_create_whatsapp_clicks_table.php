<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_clicks', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 64)->nullable()->index();
            $table->string('whatsapp_label', 100)->nullable();
            $table->string('whatsapp_number', 30);
            $table->string('page_url', 1000)->nullable();
            $table->string('page_title', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_clicks');
    }
};
