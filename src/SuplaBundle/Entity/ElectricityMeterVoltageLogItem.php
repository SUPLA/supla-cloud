<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace SuplaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="supla_em_voltage_log")
 */
class ElectricityMeterVoltageLogItem {
    /**
     * @ORM\Id
     * @ORM\Column(name="channel_id", type="integer")
     */
    private $channel_id;

    /**
     * @ORM\Id
     * @ORM\Column(name="date", type="stringdatetime")
     */
    private $date;

    /**
     * @ORM\Id
     * @ORM\Column(name="phase_no", type="tinyint")
     */
    private $phaseNo;

    /**
     * @ORM\Column(name="count_total", type="integer")
     */
    private $countTotal;

    /**
     * @ORM\Column(name="count_above", type="integer")
     */
    private $countAbove;

    /**
     * @ORM\Column(name="count_below", type="integer")
     */
    private $countBelow;

    /**
     * @ORM\Column(name="total_sec", type="integer")
     */
    private $totalSec;

    /**
     * @ORM\Column(name="total_sec_above", type="integer")
     */
    private $totalSecAbove;

    /**
     * @ORM\Column(name="total_sec_below", type="integer")
     */
    private $totalSecBelow;

    /**
     * @ORM\Column(name="max_sec_above", type="integer")
     */
    private $maxSecAbove;

    /**
     * @ORM\Column(name="max_sec_below", type="integer")
     */
    private $maxSecBelow;

    /**
     * @ORM\Column(name="min_volate", type="decimal", precision=7, scale=2)
     */
    private $minVoltage;

    /**
     * @ORM\Column(name="max_voltage", type="decimal", precision=7, scale=2)
     */
    private $maxVoltage;

    /**
     * @ORM\Column(name="avg_voltage", type="decimal", precision=7, scale=2)
     */
    private $avgVoltage;

    /**
     * @ORM\Column(name="measurement_time_sec", type="integer")
     */
    private $measurementTimeSec;

    public function getChannelId() {
        return $this->channel_id;
    }

    public function getDate() {
        return $this->date;
    }
}
