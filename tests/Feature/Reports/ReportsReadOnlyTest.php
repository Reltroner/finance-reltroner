<?php
// tests/Feature/Reports/ReportsReadOnlyTest.php
namespace Tests\Feature\Reports;

use Tests\Feature\FeatureTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReportsReadOnlyTest extends FeatureTestCase
{
    use RefreshDatabase;

    /**
     * STEP 5.3 — Global Read-Only Guard
     *
     * Financial reports MUST NEVER accept write operations.
     * Any write-capable HTTP method on /reports/* is a regression.
     */
    public function test_reports_routes_are_read_only(): void
    {
        $this->post('/reports/profit-loss/1')
            ->assertStatus(405);

        $this->put('/reports/balance-sheet/1')
            ->assertStatus(405);

        $this->delete('/reports/trial-balance/1')
            ->assertStatus(405);
    }
}
