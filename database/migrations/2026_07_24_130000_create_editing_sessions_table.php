<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('editing_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('model');
            $table->unsignedBigInteger('record_id');
            $table->timestamp('last_heartbeat');
            $table->timestamps();

            $table->unique(['user_id', 'model', 'record_id']);
            $table->index(['model', 'record_id']);
            $table->index('last_heartbeat');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('editing_sessions');
    }
};
