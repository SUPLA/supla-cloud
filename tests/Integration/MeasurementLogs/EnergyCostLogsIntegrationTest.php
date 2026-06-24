<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.
 */

namespace App\Tests\Integration\MeasurementLogs;

use App\Tests\Integration\IntegrationTestCase;

/** @small */
class EnergyCostLogsIntegrationTest extends IntegrationTestCase {
    protected function initializeDatabaseForTests() {
    }

    public function testProfileBasedCostCalculationNeedsRewrite(): void {
        $this->markTestSkipped('Pending rewrite to profile-based cost calculation model.');
    }
}
