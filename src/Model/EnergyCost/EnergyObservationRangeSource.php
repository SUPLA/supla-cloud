<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 */

namespace App\Model\EnergyCost;

use Supla\EnergyCostCalculator\Model\TimeRange;

interface EnergyObservationRangeSource {
    /** Returns the entire meter history's half-open boundaries, or null for an empty history. */
    public function getObservationRange(string $meterId): ?TimeRange;
}
