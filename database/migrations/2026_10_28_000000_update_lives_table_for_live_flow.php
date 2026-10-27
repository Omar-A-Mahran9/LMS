<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The platform list grows (Google Meet, Facebook, other links): keep it a plain string.
        // Existing values (zoom / youtube / twitch) are kept as they are.
        $this->setPlatformColumn(
            "ALTER TABLE lives MODIFY platform VARCHAR(30) NOT NULL DEFAULT 'zoom'",
            null
        );

        Schema::table('lives', function (Blueprint $table) {
            // When the students were told about this live (sent once)
            $table->timestamp('notified_at')->nullable()->after('is_active');
            $table->index(['is_active', 'start_time', 'end_time']);
        });
    }

    public function down(): void
    {
        Schema::table('lives', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'start_time', 'end_time']);
            $table->dropColumn('notified_at');
        });

        // Platforms the old column can't hold become "zoom" (their link is kept)
        DB::table('lives')->whereNotIn('platform', ['zoom', 'youtube', 'twitch'])->update(['platform' => 'zoom']);

        $this->setPlatformColumn(
            "ALTER TABLE lives MODIFY platform ENUM('zoom', 'youtube', 'twitch') NOT NULL DEFAULT 'zoom'",
            "check (\"platform\" in ('zoom', 'youtube', 'twitch'))"
        );
    }

    /**
     * MySQL changes the column type. SQLite (the test database) can't alter a column: its enum is a
     * CHECK constraint in the table definition, removed / restored with SQLite's documented
     * "writable_schema" procedure (no data is rewritten).
     */
    private function setPlatformColumn(string $mysql, ?string $sqliteCheck): void
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'])) {
            DB::statement($mysql);
            return;
        }

        if ($driver !== 'sqlite') {
            return;
        }

        $sql = DB::selectOne("select sql from sqlite_master where type = 'table' and name = 'lives'")->sql;
        $column = '/("platform" varchar)\s*(check \("platform" in \([^)]*\)\))?/i';
        $sql = preg_replace($column, '$1' . ($sqliteCheck ? ' ' . $sqliteCheck : ''), $sql, 1);

        $version = DB::selectOne('pragma schema_version')->schema_version;
        DB::statement('pragma writable_schema = 1');
        DB::update("update sqlite_master set sql = ? where type = 'table' and name = 'lives'", [$sql]);
        DB::statement('pragma schema_version = ' . ($version + 1));
        DB::statement('pragma writable_schema = 0');
    }
};
