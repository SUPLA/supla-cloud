<?php

namespace App\Tests\EventListener;

use App\EventListener\UnavailableInMaintenance;

#[UnavailableInMaintenance]
class MaintenanceClassController {
    public function action(): void {
    }
}
