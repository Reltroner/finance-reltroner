<?php
// database/migrations/2026_02_20_081922_create_financial_budgets_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_budgets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('snapshot_version');
            $table->unsignedInteger('budget_version');
            $table->string('metric');
            $table->string('period');
            $table->decimal('planned_value', 18, 4);
            $table->timestamp('created_at');

            $table->index(['snapshot_version', 'budget_version']);
            $table->index(['metric', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_budgets');
    }
};