<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('national_holidays', function (Blueprint $table) {
            $table->id();
            $table->integer('country_id');
            $table->smallInteger('year');
            $table->date('date');
            $table->string('name');
            $table->string('local_name')->nullable();
            $table->string('type')->default('national');
            $table->date('observed_date')->nullable();
            $table->boolean('is_observed_shifted')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            // countries.id is int(11) signed, so country_id must also be signed integer
            $table->unique(['country_id', 'year', 'date', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('national_holidays');
    }
};
