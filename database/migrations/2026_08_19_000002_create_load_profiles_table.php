<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('load_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('titik_ukur_id')->constrained()->cascadeOnDelete();
            $table->dateTime('recorded_at');
            $table->decimal('kwh_out', 18, 6)->default(0);
            $table->decimal('kvarh_out', 18, 6)->default(0);
            $table->decimal('kwh_in', 18, 6)->default(0);
            $table->decimal('kvarh_in', 18, 6)->default(0);
            $table->decimal('kv_r', 12, 6)->default(0);
            $table->decimal('kv_s', 12, 6)->default(0);
            $table->decimal('kv_t', 12, 6)->default(0);
            $table->decimal('ir', 12, 6)->default(0);
            $table->decimal('is', 12, 6)->default(0);
            $table->decimal('it', 12, 6)->default(0);
            $table->decimal('pf', 10, 6)->default(0);
            $table->decimal('freq', 10, 6)->default(0);
            $table->decimal('mw_out', 18, 6)->default(0);
            $table->decimal('mvar_out', 18, 6)->default(0);
            $table->timestamps();

            $table->unique(['titik_ukur_id', 'recorded_at']);
            $table->index('recorded_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('load_profiles');
    }
};