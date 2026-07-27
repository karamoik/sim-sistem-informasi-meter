<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'operator', 'viewer'])->default('viewer')->after('email');
            $table->boolean('is_active')->default(true)->after('role');
        });

        Schema::create('user_gardu_induks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('gardu_induk_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'gardu_induk_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_gardu_induks');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'is_active']);
        });
    }
};
