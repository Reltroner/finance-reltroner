<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL: ubah kolom tanpa DEFAULT
        DB::statement("
            ALTER TABLE transaction_details
            MODIFY line_no SMALLINT UNSIGNED NOT NULL COMMENT 'Line order within a transaction'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE transaction_details
            MODIFY line_no SMALLINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Line order within a transaction'
        ");
    }
};
