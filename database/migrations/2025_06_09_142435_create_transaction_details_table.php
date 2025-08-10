<?php
// database/migrations/2025_06_09_142435_create_transaction_details_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('transaction_id')
                  ->constrained('transactions')
                  ->cascadeOnDelete();

            $table->unsignedSmallInteger('line_no')
                  ->default(1)
                  ->comment('Line order within a transaction');

            $table->foreignId('account_id')->constrained('accounts');

            $table->decimal('debit', 18, 2)->default(0);
            $table->decimal('credit', 18, 2)->default(0);

            $table->foreignId('cost_center_id')
                  ->nullable()
                  ->constrained('cost_centers')
                  ->nullOnDelete();

            $table->string('memo', 255)->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Unik per transaksi: tidak boleh ada line_no duplikat
            $table->unique(['transaction_id', 'line_no']);

            // Index yang sering dipakai untuk GL/ledger
            $table->index(['account_id', 'deleted_at']); // filter by akun
            $table->index(['transaction_id', 'id']);     // join & ordering stabil
            $table->index(['cost_center_id']);           // filter cost center
        });

        // CHECK constraints (diabaikan otomatis jika engine tidak mendukung)
        try {
            // Satu sisi per baris: tidak boleh debit & credit sekaligus,
            // dan minimal salah satu > 0, serta non-negatif.
            DB::statement("
                ALTER TABLE transaction_details
                ADD CONSTRAINT chk_trxdetails_one_sided
                CHECK (
                    (debit >= 0 AND credit >= 0)
                    AND NOT (debit > 0 AND credit > 0)
                    AND (debit > 0 OR credit > 0)
                )
            ");
        } catch (\Throwable $e) {
            // silently skip for engines without CHECK support
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_details');
    }
};
