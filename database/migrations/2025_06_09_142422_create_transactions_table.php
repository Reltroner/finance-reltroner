<?php
// database/migrations/2025_06_09_142422_create_transactions_table.php

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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            // Nomor jurnal (wajib, unik). Bisa di-generate per periode via service/sequence.
            $table->string('journal_no', 40)->unique()->comment('GL journal number/sequence');

            // Reference eksternal (opsional): nomor invoice, bill, dsb.
            $table->string('reference')->nullable()->index();

            $table->text('description')->nullable();

            // Tanggal transaksi (document date)
            $table->date('date')->index();

            // Periode akuntansi (untuk laporan & penomoran)
            $table->smallInteger('fiscal_year')->index();      // contoh: 2025
            $table->tinyInteger('fiscal_period')->index();     // 1..12 (atau 13 jika pakai period 13)

            // Mata uang transaksi + kurs ke base currency
            $table->foreignId('currency_id')->constrained('currencies');
            $table->decimal('exchange_rate', 24, 10)->default(1)->comment('to base currency');

            // Total dalam mata uang transaksi
            $table->decimal('total_debit', 18, 2)->default(0);
            $table->decimal('total_credit', 18, 2)->default(0);

            // Total terkonversi ke base currency (untuk konsolidasi laporan)
            $table->decimal('total_debit_base', 18, 2)->default(0);
            $table->decimal('total_credit_base', 18, 2)->default(0);

            // Status daur hidup jurnal
            $table->enum('status', ['draft', 'posted', 'voided'])->default('draft')->index();
            $table->timestamp('posted_at')->nullable()->index();
            $table->unsignedBigInteger('posted_by')->nullable();

            // Pembatalan/void & jurnal pembalik
            $table->timestamp('voided_at')->nullable();
            $table->unsignedBigInteger('voided_by')->nullable();
            $table->foreignId('reversal_of_id')->nullable()
                  ->constrained('transactions')->nullOnDelete();

            // Metadata
            $table->unsignedBigInteger('created_by')->nullable()->index();

            $table->timestamps();
            $table->softDeletes();

            // Indeks komposit yang sering dipakai untuk GL
            $table->index(['fiscal_year', 'fiscal_period', 'date']);
            $table->index(['date', 'journal_no']);
        });

        // CHECK constraints (akan dieksekusi jika DB mendukung; aman diabaikan jika tidak)
        try {
            // total_debit = total_credit dan keduanya tidak negatif
            DB::statement("
                ALTER TABLE transactions
                ADD CONSTRAINT chk_transactions_balanced
                CHECK (
                    total_debit >= 0 AND total_credit >= 0
                    AND total_debit = total_credit
                )
            ");
            DB::statement("
                ALTER TABLE transactions
                ADD CONSTRAINT chk_transactions_balanced_base
                CHECK (
                    total_debit_base >= 0 AND total_credit_base >= 0
                    AND total_debit_base = total_credit_base
                )
            ");
            // fiscal_period 1..13 (kalau tidak pakai period 13, ubah jadi 1..12)
            DB::statement("
                ALTER TABLE transactions
                ADD CONSTRAINT chk_transactions_period
                CHECK (fiscal_period BETWEEN 1 AND 13)
            ");
            // exchange_rate > 0
            DB::statement("
                ALTER TABLE transactions
                ADD CONSTRAINT chk_transactions_exrate
                CHECK (exchange_rate > 0)
            ");
        } catch (\Throwable $e) {
            // silently skip if the database engine ignores CHECK (mis. MySQL lama)
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
