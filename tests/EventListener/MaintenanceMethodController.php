<?php

namespace App\Tests\EventListener;

use App\EventListener\UnavailableInMaintenance;

class MaintenanceMethodController {
    #[UnavailableInMaintenance]
    public function blocked(): void {
    }

    public function allowed(): void {
    }
}
