<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('konfigurasi_meters', function (Blueprint $table) {
            $table->foreignId('kwh_meter_id')
                ->nullable()
                ->after('jenis_kwh_meter_id')
                ->constrained('kwh_meters')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('konfigurasi_meters', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kwh_meter_id');
        });
    }
};