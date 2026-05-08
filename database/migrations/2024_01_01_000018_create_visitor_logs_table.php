<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitor_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->constrained()->cascadeOnDelete();
            $table->text('page_url');
            $table->string('page_title')->nullable();
            $table->timestamp('visited_at');

            $table->index('visitor_id');
            $table->index('visited_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_logs');
    }
};
