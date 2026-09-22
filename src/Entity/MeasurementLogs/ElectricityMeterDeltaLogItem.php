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
     * @ORM\Column(name="phase1_fre", type="integer", nullable=true)
     */
    private ?int $phase1_fre = null;

    /**
     * @ORM\Column(name="phase1_rre", type="integer", nullable=true)
     */
    private ?int $phase1_rre = null;

    /**
     * @ORM\Column(name="phase2_fre", type="integer", nullable=true)
     */
    private ?int $phase2_fre = null;

    /**
     * @ORM\Column(name="phase2_rre", type="integer", nullable=true)
     */
    private ?int $phase2_rre = null;

    /**
     * @ORM\Column(name="phase3_fae", type="integer", nullable=true)
     */
    private ?int $phase3_fae = null;

    /**
     * @ORM\Column(name="phase3_rae", type="integer", nullable=true)
     */
    private ?int $phase3_rae = null;

    /**
     * @ORM\Column(name="phase3_fre", type="integer", nullable=true)
     */
    private ?int $phase3_fre = null;

    /**
     * @ORM\Column(name="phase3_rre", type="integer", nullable=true)
     */
    private ?int $phase3_rre = null;

    /**
     * @ORM\Column(name="fae_balanced", type="integer", nullable=true)
     */
    private ?int $fae_balanced = null;

    /**
     * @ORM\Column(name="rae_balanced", type="integer", nullable=true)
     */
    private ?int $rae_balanced = null;

    public function __construct(
        int $channel_id,
        string $date,
        ?int $phase1_fae = null,
        ?int $phase1_rae = null,
        ?int $phase2_fae = null,
        ?int $phase2_rae = null,
        ?int $phase1_fre = null,
        ?int $phase1_rre = null,
        ?int $phase2_fre = null,
        ?int $phase2_rre = null,
        ?int $phase3_fae = null,
        ?int $phase3_rae = null,
        ?int $phase3_fre = null,
        ?int $phase3_rre = null,
        ?int $fae_balanced = null,
        ?int $rae_balanced = null,
    ) {
        $this->channel_id = $channel_id;
        $this->date = $date;
        $this->phase1_fae = $phase1_fae;
        $this->phase1_rae = $phase1_rae;
        $this->phase2_fae = $phase2_fae;
        $this->phase2_rae = $phase2_rae;
        $this->phase1_fre = $phase1_fre;
        $this->phase1_rre = $phase1_rre;
        $this->phase2_fre = $phase2_fre;
        $this->phase2_rre = $phase2_rre;
        $this->phase3_fae = $phase3_fae;
        $this->phase3_rae = $phase3_rae;
        $this->phase3_fre = $phase3_fre;
        $this->phase3_rre = $phase3_rre;
        $this->fae_balanced = $fae_balanced;
        $this->rae_balanced = $rae_balanced;
    }

    public function getChannelId(): int {
        return $this->channel_id;
    }

    public function getDate(): string {
        return $this->date;
    }

    public function getTotalForwardActiveEnergy($phase = 0): ?int {
        switch ($phase) {
            case 1:
                return $this->phase1_fae;
            case 2:
                return $this->phase2_fae;
            case 3:
                return $this->phase3_fae;
        }
        return ($this->phase1_fae ?: 0) + ($this->phase2_fae ?: 0) + ($this->phase3_fae ?: 0);
    }

    public function getTotalReverseActiveEnergy($phase = 0): ?int {
        switch ($phase) {
            case 1:
                return $this->phase1_rae;
            case 2:
                return $this->phase2_rae;
            case 3:
                return $this->phase3_rae;
        }
        return ($this->phase1_rae ?: 0) + ($this->phase2_rae ?: 0) + ($this->phase3_rae ?: 0);
    }

    public function getTotalForwardReactiveEnergy($phase = 0): ?int {
        switch ($phase) {
            case 1:
                return $this->phase1_fre;
            case 2:
                return $this->phase2_fre;
            case 3:
                return $this->phase3_fre;
        }
        return ($this->phase1_fre ?: 0) + ($this->phase2_fre ?: 0) + ($this->phase3_fre ?: 0);
    }

    public function getTotalReverseReactiveEnergy($phase = 0): ?int {
        switch ($phase) {
            case 1:
                return $this->phase1_rre;
            case 2:
                return $this->phase2_rre;
            case 3:
                return $this->phase3_rre;
        }
        return ($this->phase1_rre ?: 0) + ($this->phase2_rre ?: 0) + ($this->phase3_rre ?: 0);
    }

    public function getTotalForwardActiveEnergyBalanced(): ?int {
        return $this->fae_balanced;
    }

    public function getTotalReverseActiveEnergyBalanced(): ?int {
        return $this->rae_balanced;
    }
}
