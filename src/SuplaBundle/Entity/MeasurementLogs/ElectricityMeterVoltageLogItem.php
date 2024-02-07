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

namespace SuplaBundle\Entity\MeasurementLogs;

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
     * @ORM\Column(name="min", type="decimal", precision=5, scale=2)
     */
    private $min;

    /**
     * @ORM\Column(name="max", type="decimal", precision=5, scale=2)
     */
    private $max;

    /**
     * @ORM\Column(name="avg", type="decimal", precision=5, scale=2)
     */
    private $avg;


    public function getChannelId() {
        return $this->channel_id;
    }

    public function getDate() {
        return $this->date;
    }
}
