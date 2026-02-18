<?php
// database/migrations/2026_02_11_053132_create_financial_snapshots_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignId('fiscal_period_id')
                ->constrained('fiscal_periods')
                ->cascadeOnDelete();

            $table->unsignedInteger('version');

            $table->longText('payload_json');
            $table->char('payload_hash', 64);
            $table->char('source_hash', 64);

            $table->timestamp('created_at')->useCurrent();

            $table->unique(['fiscal_period_id', 'version']);
            $table->index('fiscal_period_id');
        });

        /*
        |--------------------------------------------------------------------------
        | 🔒 Enforce Snapshot Immutability (Layer 0 Freeze Guard)
        |--------------------------------------------------------------------------
        */

        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {

            DB::unprepared('
                CREATE TRIGGER prevent_snapshot_update
                BEFORE UPDATE ON financial_snapshots
                BEGIN
                    SELECT RAISE(FAIL, "Snapshots are immutable.");
                END;
            ');

            DB::unprepared('
                CREATE TRIGGER prevent_snapshot_delete
                BEFORE DELETE ON financial_snapshots
                BEGIN
                    SELECT RAISE(FAIL, "Snapshots cannot be deleted.");
                END;
            ');

        } elseif ($driver === 'mysql') {

            DB::unprepared('
                CREATE TRIGGER prevent_snapshot_update
                BEFORE UPDATE ON financial_snapshots
                FOR EACH ROW
                BEGIN
                    SIGNAL SQLSTATE "45000"
                    SET MESSAGE_TEXT = "Snapshots are immutable.";
                END;
            ');

            DB::unprepared('
                CREATE TRIGGER prevent_snapshot_delete
                BEFORE DELETE ON financial_snapshots
                FOR EACH ROW
                BEGIN
                    SIGNAL SQLSTATE "45000"
                    SET MESSAGE_TEXT = "Snapshots cannot be deleted.";
                END;
            ');
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            DB::unprepared('DROP TRIGGER IF EXISTS prevent_snapshot_update;');
            DB::unprepared('DROP TRIGGER IF EXISTS prevent_snapshot_delete;');
        }

        if ($driver === 'mysql') {
            DB::unprepared('DROP TRIGGER IF EXISTS prevent_snapshot_update;');
            DB::unprepared('DROP TRIGGER IF EXISTS prevent_snapshot_delete;');
        }

        Schema::dropIfExists('financial_snapshots');
    }
};
