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

namespace App\Entity\MeasurementLogs;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="supla_em_delta_log")
 */
class ElectricityMeterDeltaLogItem {
    /**
     * @ORM\Id
     * @ORM\Column(name="channel_id", type="integer")
     */
    private int $channel_id;

    /**
     * @ORM\Id
     * @ORM\Column(name="date", type="stringdatetime")
     */
    private string $date;

    /**
     * @ORM\Column(name="phase1_fae", type="integer", nullable=true)
     */
    private ?int $phase1_fae = null;

    /**
     * @ORM\Column(name="phase1_rae", type="integer", nullable=true)
     */
    private ?int $phase1_rae = null;

    /**
     * @ORM\Column(name="phase2_fae", type="integer", nullable=true)
     */
    private ?int $phase2_fae = null;

    /**
     * @ORM\Column(name="phase2_rae", type="integer", nullable=true)
     */
    private ?int $phase2_rae = null;

    /**
     * @ORM\Column(name="phase3_fae", type="integer", nullable=true)
     */
    private ?int $phase3_fae = null;

    /**
     * @ORM\Column(name="phase3_rae", type="integer", nullable=true)
     */
    private ?int $phase3_rae = null;

    public function __construct(int $channel_id, string $date) {
        $this->channel_id = $channel_id;
        $this->date = $date;
    }

    public function getChannelId() {
        return $this->channel_id;
    }

    public function getDate() {
        return $this->date;
    }

    public function getTotalForwardActiveEnergy($phase = 0) {
        switch ($phase) {
            case 1:
                return $this->phase1_fae;
            case 2:
                return $this->phase2_fae;
            case 3:
                return $this->phase3_fae;
        }
        return $this->phase1_fae + $this->phase2_fae + $this->phase3_fae;
    }

    public function getTotalReverseActiveEnergy($phase = 0) {
        switch ($phase) {
            case 1:
                return $this->phase1_rae;
            case 2:
                return $this->phase2_rae;
            case 3:
                return $this->phase3_rae;
        }
        return $this->phase1_rae + $this->phase2_rae + $this->phase3_rae;
    }
}
