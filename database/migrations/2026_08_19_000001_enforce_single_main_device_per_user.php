<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            UPDATE user_sessions s
            JOIN (
                SELECT user_id, MAX(last_active_at) AS latest
                FROM user_sessions
                WHERE is_main = 1
                GROUP BY user_id
            ) m ON m.user_id = s.user_id
            SET s.is_main = 0
            WHERE s.is_main = 1
              AND (s.last_active_at IS NULL OR s.last_active_at <> m.latest)
        SQL);

        DB::statement(<<<'SQL'
            CREATE UNIQUE INDEX uq_user_sessions_one_main
            ON user_sessions ((CASE WHEN is_main = 1 THEN user_id ELSE NULL END))
        SQL);
    }

    public function down(): void
    {
        DB::statement('DROP INDEX uq_user_sessions_one_main ON user_sessions');
    }
};