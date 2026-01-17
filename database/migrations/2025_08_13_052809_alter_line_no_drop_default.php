<?php
// database/migrations/2025_08_13_052809_alter_line_no_drop_default.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // SQLite does not support MODIFY COLUMN
            // Do nothing – column already exists and is usable
            return;
        }

        DB::statement("
            ALTER TABLE transaction_details
            MODIFY line_no SMALLINT UNSIGNED NOT NULL
            COMMENT 'Line order within a transaction'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
    }
};
