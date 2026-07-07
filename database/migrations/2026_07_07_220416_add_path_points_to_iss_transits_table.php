<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('iss_transits', function (Blueprint $table) {
            // Stores JSON array of {dx, dy} path points relative to disk centre
            // dx/dy are in angular degrees offset from the Sun/Moon centre
            $table->json('path_points')->nullable()->after('is_exact_transit');
        });
    }

    public function down(): void
    {
        Schema::table('iss_transits', function (Blueprint $table) {
            $table->dropColumn('path_points');
        });
    }
};
