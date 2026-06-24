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
 * @ORM\Table(name="supla_energy_tariff_profile_assignment", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="uniq_tariff_profile_assignment_channel", columns={"channel_id"})
 * }, indexes={
 *     @ORM\Index(name="idx_tariff_profile_assignment_profile", columns={"profile_id"})
 * })
 */
class EnergyTariffProfileAssignment {
    /** @ORM\Id @ORM\Column(name="id", type="bigint") @ORM\GeneratedValue(strategy="AUTO") */
    private $id;

    /** @ORM\Column(name="channel_id", type="integer") */
    private int $channelId;

    /**
     * @ORM\ManyToOne(targetEntity="EnergyTariffProfile", inversedBy="assignments")
     * @ORM\JoinColumn(name="profile_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private ?EnergyTariffProfile $profile = null;

    public function getId() {
        return $this->id;
    }

    public function getChannelId(): int {
        return $this->channelId;
    }

    public function setChannelId(int $channelId): void {
        $this->channelId = $channelId;
    }

    public function getProfile(): ?EnergyTariffProfile {
        return $this->profile;
    }

    public function setProfile(?EnergyTariffProfile $profile): void {
        $this->profile = $profile;
    }
}
