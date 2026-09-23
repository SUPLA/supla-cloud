<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 */

namespace App\Tests\Integration\Model\EnergyCost;

use Supla\EnergyCostCalculator\Contract\EnergyDeltaSource;
use Supla\EnergyCostCalculator\Model\EnergyDelta;
use Supla\EnergyCostCalculator\Model\QuantityType;
use Supla\EnergyCostCalculator\Model\TimeRange;

class RecordingEnergyDeltaSource implements EnergyDeltaSource {
    public ?string $meterId = null;
    public ?TimeRange $range = null;

    public function getDeltas(string $meterId, TimeRange $range): iterable {
        $this->meterId = $meterId;
        $this->range = $range;
        yield new EnergyDelta($range->from, $range->to, [QuantityType::ACTIVE_ENERGY_IMPORT->value => '1']);
    }
}
