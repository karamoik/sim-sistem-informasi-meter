<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kwh_meters', function (Blueprint $table) {
            $table->id();
            $table->string('meter_no', 50)->unique();
            $table->foreignId('gardu_induk_id')->constrained()->cascadeOnDelete();
            $table->foreignId('titik_ukur_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('jenis_kwh_meter_id')->nullable()->constrained()->nullOnDelete();
            $table->string('unit_name')->comment('Nama unit pembangkit/transaksi');
            $table->string('location')->comment('Lokasi pemasangan');
            $table->decimal('last_reading', 12, 2)->default(0);
            $table->timestamp('last_update')->nullable();
            $table->enum('status', ['online', 'offline'])->default('online');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kwh_meters');
    }
};
