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

namespace App\Entity\Main;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EnergyCostPlanAssignmentRepository")
 * @ORM\Table(name="supla_energy_cost_plan_assignment", indexes={
 *     @ORM\Index(name="supla_energy_cost_plan_assignment_plan_idx", columns={"energy_cost_plan_id"})
 * })
 */
class EnergyCostPlanAssignment {
    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="IODeviceChannel")
     * @ORM\JoinColumn(name="channel_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private IODeviceChannel $channel;

    /**
     * @ORM\ManyToOne(targetEntity="EnergyCostPlan")
     * @ORM\JoinColumn(name="energy_cost_plan_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private EnergyCostPlan $energyCostPlan;

    public function __construct(IODeviceChannel $channel, EnergyCostPlan $energyCostPlan) {
        $this->channel = $channel;
        $this->energyCostPlan = $energyCostPlan;
    }

    public function getChannel(): IODeviceChannel {
        return $this->channel;
    }

    public function getChannelId(): int {
        return $this->channel->getId();
    }

    public function getEnergyCostPlan(): EnergyCostPlan {
        return $this->energyCostPlan;
    }

    public function setEnergyCostPlan(EnergyCostPlan $energyCostPlan): void {
        $this->energyCostPlan = $energyCostPlan;
    }
}
