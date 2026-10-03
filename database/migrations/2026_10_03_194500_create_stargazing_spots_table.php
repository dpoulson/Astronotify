<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stargazing_spots', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('country');
            $table->string('region')->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->integer('elevation')->default(0);
            $table->unsignedTinyInteger('bortle_class')->default(3);
            $table->string('dark_sky_status')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['country', 'is_active']);
            $table->index('bortle_class');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stargazing_spots');
    }
};
