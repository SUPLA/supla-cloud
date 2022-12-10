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
 * @ORM\Table(name="supla_em_log")
 */
class ElectricityMeterLogItem {
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
     * @ORM\Column(name="phase1_fae", type="bigint", nullable=true)
     */
    private $phase1_fae;

    /**
     * @ORM\Column(name="phase1_rae", type="bigint", nullable=true)
     */
    private $phase1_rae;

    /**
     * @ORM\Column(name="phase1_fre", type="bigint", nullable=true)
     */
    private $phase1_fre;

    /**
     * @ORM\Column(name="phase1_rre", type="bigint", nullable=true)
     */
    private $phase1_rre;

    /**
     * @ORM\Column(name="phase2_fae", type="bigint", nullable=true)
     */
    private $phase2_fae;

    /**
     * @ORM\Column(name="phase2_rae", type="bigint", nullable=true)
     */
    private $phase2_rae;

    /**
     * @ORM\Column(name="phase2_fre", type="bigint", nullable=true)
     */
    private $phase2_fre;

    /**
     * @ORM\Column(name="phase2_rre", type="bigint", nullable=true)
     */
    private $phase2_rre;

    /**
     * @ORM\Column(name="phase3_fae", type="bigint", nullable=true)
     */
    private $phase3_fae;

    /**
     * @ORM\Column(name="phase3_rae", type="bigint", nullable=true)
     */
    private $phase3_rae;

    /**
     * @ORM\Column(name="phase3_fre", type="bigint", nullable=true)
     */
    private $phase3_fre;

    /**
     * @ORM\Column(name="phase3_rre", type="bigint", nullable=true)
     */
    private $phase3_rre;

    /**
     * @ORM\Column(name="fae_balanced", type="bigint", nullable=true)
     */
    private $fae_balanced;

    /**
     * @ORM\Column(name="rae_balanced", type="bigint", nullable=true)
     */
    private $rae_balanced;

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

    public function getTotalForwardReactiveEnergy($phase = 0) {
        switch ($phase) {
            case 1:
                return $this->phase1_fre;
            case 2:
                return $this->phase2_fre;
            case 3:
                return $this->phase3_fre;
        }
        return $this->phase1_fre + $this->phase2_fre + $this->phase3_fre;
    }

    public function getTotalReverseRectiveEnergy($phase = 0) {
        switch ($phase) {
            case 1:
                return $this->phase1_rre;
            case 2:
                return $this->phase2_rre;
            case 3:
                return $this->phase3_rre;
        }
        return $this->phase1_rre + $this->phase2_rre + $this->phase3_rre;
    }

    public function getTotalForwardActiveEnergyBalanced() {
        return $this->fae_balanced;
    }

    public function getTotalReverseActiveEnergyBalanced() {
        return $this->fae_balanced;
    }
}
