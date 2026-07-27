<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('konfigurasi_meters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('titik_ukur_id')->constrained()->cascadeOnDelete();
            $table->foreignId('jenis_kwh_meter_id')->constrained()->cascadeOnDelete();
            $table->string('period');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'expired'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('konfigurasi_meters');
    }
};
