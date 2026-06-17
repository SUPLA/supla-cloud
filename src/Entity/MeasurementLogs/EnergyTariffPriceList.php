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
 * @ORM\Table(name="supla_energy_tariff_price_list")
 * @ORM\HasLifecycleCallbacks
 */
class EnergyTariffPriceList {
    /** @ORM\Id @ORM\Column(name="id", type="bigint") @ORM\GeneratedValue(strategy="AUTO") */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="EnergyTariff", inversedBy="priceLists")
     * @ORM\JoinColumn(name="tariff_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private ?EnergyTariff $tariff = null;

    /** @ORM\Column(name="name", type="string", length=255) */
    private string $name = '';

    /** @ORM\Column(name="created_at", type="utcdatetime") */
    private \DateTime $createdAt;

    /** @ORM\Column(name="updated_at", type="utcdatetime") */
    private \DateTime $updatedAt;

    /**
     * @var Collection<int, EnergyTariffPriceListItem>
     * @ORM\OneToMany(targetEntity="EnergyTariffPriceListItem", mappedBy="priceList", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private Collection $items;

    /**
     * @var Collection<int, EnergyTariffPriceListAssignment>
     * @ORM\OneToMany(targetEntity="EnergyTariffPriceListAssignment", mappedBy="priceList", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private Collection $assignments;

    public function __construct() {
        $this->items = new ArrayCollection();
        $this->assignments = new ArrayCollection();
        $this->updateTimestamps();
    }

    public function getId() {
        return $this->id;
    }

    public function getTariff(): ?EnergyTariff {
        return $this->tariff;
    }

    public function setTariff(?EnergyTariff $tariff): void {
        $this->tariff = $tariff;
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
     * @return Collection<int, EnergyTariffPriceListItem>
     */
    public function getItems(): Collection {
        return $this->items;
    }

    public function addItem(EnergyTariffPriceListItem $item): void {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setPriceList($this);
        }
    }

    public function removeItem(EnergyTariffPriceListItem $item): void {
        if ($this->items->removeElement($item) && $item->getPriceList() === $this) {
            $item->setPriceList(null);
        }
    }

    /**
     * @return Collection<int, EnergyTariffPriceListAssignment>
     */
    public function getAssignments(): Collection {
        return $this->assignments;
    }

    public function addAssignment(EnergyTariffPriceListAssignment $assignment): void {
        if (!$this->assignments->contains($assignment)) {
            $this->assignments->add($assignment);
            $assignment->setPriceList($this);
        }
    }

    public function removeAssignment(EnergyTariffPriceListAssignment $assignment): void {
        if ($this->assignments->removeElement($assignment) && $assignment->getPriceList() === $this) {
            $assignment->setPriceList(null);
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
