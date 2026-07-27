<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gardu_induks', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('kwh_meters', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('jenis_kwh_meters', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('titik_ukurs', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('konfigurasi_meters', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('gardu_induks', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('kwh_meters', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('jenis_kwh_meters', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('titik_ukurs', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('konfigurasi_meters', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
