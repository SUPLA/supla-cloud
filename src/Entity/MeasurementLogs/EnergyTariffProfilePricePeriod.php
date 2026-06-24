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
 * @ORM\Table(name="supla_energy_tariff_profile_price_period", indexes={
 *     @ORM\Index(name="idx_tariff_profile_price_period_time", columns={"tariff_period_id", "valid_from", "valid_to"})
 * })
 */
class EnergyTariffProfilePricePeriod {
    /** @ORM\Id @ORM\Column(name="id", type="bigint") @ORM\GeneratedValue(strategy="AUTO") */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="EnergyTariffProfileTariffPeriod", inversedBy="pricePeriods")
     * @ORM\JoinColumn(name="tariff_period_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private ?EnergyTariffProfileTariffPeriod $tariffPeriod = null;

    /** @ORM\Column(name="name", type="string", length=255) */
    private string $name = '';

    /** @ORM\Column(name="billing_period_start_day", type="integer") */
    private int $billingPeriodStartDay = 1;

    /** @ORM\Column(name="currency", type="string", length=10) */
    private string $currency = '';

    /** @ORM\Column(name="valid_from", type="utcdatetime") */
    private \DateTime $validFrom;

    /** @ORM\Column(name="valid_to", type="utcdatetime", nullable=true) */
    private ?\DateTime $validTo = null;

    /**
     * @var Collection<int, EnergyTariffProfilePriceItem>
     * @ORM\OneToMany(targetEntity="EnergyTariffProfilePriceItem", mappedBy="pricePeriod", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private Collection $items;

    public function __construct() {
        $this->items = new ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function getTariffPeriod(): ?EnergyTariffProfileTariffPeriod {
        return $this->tariffPeriod;
    }

    public function setTariffPeriod(?EnergyTariffProfileTariffPeriod $tariffPeriod): void {
        $this->tariffPeriod = $tariffPeriod;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function getBillingPeriodStartDay(): int {
        return $this->billingPeriodStartDay;
    }

    public function setBillingPeriodStartDay(int $billingPeriodStartDay): void {
        $this->billingPeriodStartDay = $billingPeriodStartDay;
    }

    public function getCurrency(): string {
        return $this->currency;
    }

    public function setCurrency(string $currency): void {
        $this->currency = $currency;
    }

    public function getValidFrom(): \DateTime {
        return $this->validFrom;
    }

    public function setValidFrom(\DateTime $validFrom): void {
        $this->validFrom = $validFrom;
    }

    public function getValidTo(): ?\DateTime {
        return $this->validTo;
    }

    public function setValidTo(?\DateTime $validTo): void {
        $this->validTo = $validTo;
    }

    /**
     * @return Collection<int, EnergyTariffProfilePriceItem>
     */
    public function getItems(): Collection {
        return $this->items;
    }

    public function addItem(EnergyTariffProfilePriceItem $item): void {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setPricePeriod($this);
        }
    }

    public function removeItem(EnergyTariffProfilePriceItem $item): void {
        if ($this->items->removeElement($item) && $item->getPricePeriod() === $this) {
            $item->setPricePeriod(null);
        }
    }
}
