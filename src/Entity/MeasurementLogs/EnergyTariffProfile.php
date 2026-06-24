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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="supla_energy_tariff_profile", indexes={
 *     @ORM\Index(name="idx_energy_tariff_profile_user", columns={"user_id"})
 * })
 * @ORM\HasLifecycleCallbacks
 */
class EnergyTariffProfile {
    /** @ORM\Id @ORM\Column(name="id", type="bigint") @ORM\GeneratedValue(strategy="AUTO") */
    private $id;

    /** @ORM\Column(name="user_id", type="integer") */
    private int $userId;

    /** @ORM\Column(name="name", type="string", length=255) */
    private string $name = '';

    /** @ORM\Column(name="created_at", type="utcdatetime") */
    private \DateTime $createdAt;

    /** @ORM\Column(name="updated_at", type="utcdatetime") */
    private \DateTime $updatedAt;

    /**
     * @var Collection<int, EnergyTariffProfileTariffPeriod>
     * @ORM\OneToMany(targetEntity="EnergyTariffProfileTariffPeriod", mappedBy="profile", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private Collection $tariffPeriods;

    /**
     * @var Collection<int, EnergyTariffProfileAssignment>
     * @ORM\OneToMany(targetEntity="EnergyTariffProfileAssignment", mappedBy="profile", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private Collection $assignments;

    public function __construct() {
        $this->tariffPeriods = new ArrayCollection();
        $this->assignments = new ArrayCollection();
        $this->updateTimestamps();
    }

    public function getId() {
        return $this->id;
    }

    public function getUserId(): int {
        return $this->userId;
    }

    public function setUserId(int $userId): void {
        $this->userId = $userId;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function getCreatedAt(): \DateTime {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTime {
        return $this->updatedAt;
    }

    /**
     * @return Collection<int, EnergyTariffProfileTariffPeriod>
     */
    public function getTariffPeriods(): Collection {
        return $this->tariffPeriods;
    }

    public function addTariffPeriod(EnergyTariffProfileTariffPeriod $tariffPeriod): void {
        if (!$this->tariffPeriods->contains($tariffPeriod)) {
            $this->tariffPeriods->add($tariffPeriod);
            $tariffPeriod->setProfile($this);
        }
    }

    public function removeTariffPeriod(EnergyTariffProfileTariffPeriod $tariffPeriod): void {
        if ($this->tariffPeriods->removeElement($tariffPeriod) && $tariffPeriod->getProfile() === $this) {
            $tariffPeriod->setProfile(null);
        }
    }

    /**
     * @return Collection<int, EnergyTariffProfileAssignment>
     */
    public function getAssignments(): Collection {
        return $this->assignments;
    }

    public function addAssignment(EnergyTariffProfileAssignment $assignment): void {
        if (!$this->assignments->contains($assignment)) {
            $this->assignments->add($assignment);
            $assignment->setProfile($this);
        }
    }

    public function removeAssignment(EnergyTariffProfileAssignment $assignment): void {
        if ($this->assignments->removeElement($assignment) && $assignment->getProfile() === $this) {
            $assignment->setProfile(null);
        }
    }

    /** @ORM\PrePersist */
    public function onPrePersist(): void {
        $this->updateTimestamps();
    }

    /** @ORM\PreUpdate */
    public function onPreUpdate(): void {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    private function updateTimestamps(): void {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->createdAt = $this->createdAt ?? $now;
        $this->updatedAt = $now;
    }
}
