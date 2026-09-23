<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 */

namespace App\Tests\Integration\Model\EnergyCost;

use Supla\EnergyCostCalculator\Contract\ReferenceDataSource;
use Supla\EnergyCostCalculator\Model\ReferenceDataId;
use Supla\EnergyCostCalculator\Model\TimeRange;

class EmptyReferenceDataSource implements ReferenceDataSource {
    public function get(ReferenceDataId $id, TimeRange $range): iterable {
        return [];
    }
}
