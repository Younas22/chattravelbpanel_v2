<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('pending'); // pending, active, closed
            $table->unsignedInteger('unread_admin')->default(0);
            $table->unsignedInteger('unread_visitor')->default(0);
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index('visitor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
