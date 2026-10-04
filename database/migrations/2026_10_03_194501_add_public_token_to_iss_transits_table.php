<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('iss_transits', function (Blueprint $table) {
            $table->string('public_token', 32)->nullable()->unique()->after('id');
        });

        $transits = DB::table('iss_transits')->whereNull('public_token')->get(['id']);
        foreach ($transits as $t) {
            DB::table('iss_transits')->where('id', $t->id)->update([
                'public_token' => Str::random(16),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('iss_transits', function (Blueprint $table) {
            $table->dropColumn('public_token');
        });
    }
};
