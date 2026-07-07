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
        Schema::create('iss_transits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // 'sun' or 'moon'
            $table->dateTime('time');
            $table->decimal('separation_degrees', 6, 4);
            $table->decimal('altitude_degrees', 5, 2);
            $table->decimal('azimuth_degrees', 5, 2);
            $table->boolean('is_exact_transit')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iss_transits');
    }
};
