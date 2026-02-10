<?php
// tests/Feature/FeatureTestCase.php

namespace Tests\Feature;

use Tests\TestCase as BaseTestCase;

abstract class FeatureTestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // 🔐 Finance gateway auth context (read-only)
        session([
            'finance_authenticated' => true,
            'finance_user_id'       => 1,
        ]);
    }
}
