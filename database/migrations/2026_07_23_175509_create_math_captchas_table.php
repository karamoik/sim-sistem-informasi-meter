<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('math_captchas', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->string('question');
            $table->integer('answer');
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('math_captchas');
    }
};
