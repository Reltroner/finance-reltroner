<?php
// database/migrations/2026_02_01_043625_create_fiscal_periods_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fiscal_periods', function (Blueprint $table) {
            $table->id();

            // Natural accounting key
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('period'); // 1..12 (enforced by domain)

            // Lifecycle state
            $table->enum('status', ['open', 'closed', 'locked'])
                  ->default('open');

            // Audit timestamps
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('locked_at')->nullable();

            // Audit actors
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->unsignedBigInteger('locked_by')->nullable();

            $table->timestamps();

            // Uniqueness constraint
            $table->unique(['year', 'period']);

            // Optional audit-grade FK (safe)
            $table->foreign('closed_by')
                  ->references('id')->on('users')
                  ->nullOnDelete();

            $table->foreign('locked_by')
                  ->references('id')->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiscal_periods');
    }
};
