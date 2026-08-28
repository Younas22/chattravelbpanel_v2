<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            $table->string('lead_status', 20)->default('pending')->after('whatsapp');
            $table->timestamp('lead_status_updated_at')->nullable()->after('lead_status');

            $table->string('utm_source', 150)->nullable()->after('referrer');
            $table->string('utm_medium', 150)->nullable()->after('utm_source');
            $table->string('utm_campaign', 150)->nullable()->after('utm_medium');
            $table->string('utm_term', 150)->nullable()->after('utm_campaign');
            $table->string('utm_content', 150)->nullable()->after('utm_term');

            $table->index('lead_status');
            $table->index('utm_source');
        });
    }

    public function down(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            $table->dropIndex(['lead_status']);
            $table->dropIndex(['utm_source']);
            $table->dropColumn([
                'lead_status', 'lead_status_updated_at',
                'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
            ]);
        });
    }
};
