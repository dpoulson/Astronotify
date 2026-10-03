<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('weather_conditions', function (Blueprint $table) {
            $table->timestamp('notified_at')->nullable()->after('is_optimal');
            $table->json('hourly_clouds')->nullable()->after('forecast_data');
        });

        Schema::table('iss_transits', function (Blueprint $table) {
            $table->timestamp('notified_at')->nullable()->after('is_exact_transit');
            $table->integer('cloud_cover_percent')->nullable()->after('notified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weather_conditions', function (Blueprint $table) {
            $table->dropColumn(['notified_at', 'hourly_clouds']);
        });

        Schema::table('iss_transits', function (Blueprint $table) {
            $table->dropColumn(['notified_at', 'cloud_cover_percent']);
        });
    }
};
