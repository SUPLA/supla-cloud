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

use App\Enums\EnergyPriceComponent;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="supla_energy_tariff_profile_price_item", indexes={
 *     @ORM\Index(name="idx_tariff_profile_price_item_component_zone", columns={"price_period_id", "component_code", "zone_code"})
 * })
 */
class EnergyTariffProfilePriceItem {
    /** @ORM\Id @ORM\Column(name="id", type="bigint") @ORM\GeneratedValue(strategy="AUTO") */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="EnergyTariffProfilePricePeriod", inversedBy="items")
     * @ORM\JoinColumn(name="price_period_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private ?EnergyTariffProfilePricePeriod $pricePeriod = null;

    /** @ORM\Column(name="component_code", type="integer", enumType=EnergyPriceComponent::class) */
    private EnergyPriceComponent $componentCode;

    /** @ORM\Column(name="zone_code", type="string", length=100, nullable=true) */
    private ?string $zoneCode = null;

    /** @ORM\Column(name="amount", type="decimal", precision=12, scale=6) */
    private ?float $amount = null;

    /** @ORM\Column(name="unit", type="string", length=20) */
    private string $unit = '';

    public function getId() {
        return $this->id;
    }

    public function getPricePeriod(): ?EnergyTariffProfilePricePeriod {
        return $this->pricePeriod;
    }

    public function setPricePeriod(?EnergyTariffProfilePricePeriod $pricePeriod): void {
        $this->pricePeriod = $pricePeriod;
    }

    public function getComponentCode(): EnergyPriceComponent {
        return $this->componentCode;
    }

    public function setComponentCode(EnergyPriceComponent $componentCode): void {
        $this->componentCode = $componentCode;
    }

    public function getZoneCode(): ?string {
        return $this->zoneCode;
    }

    public function setZoneCode(?string $zoneCode): void {
        $this->zoneCode = $zoneCode;
    }

    public function getAmount(): ?float {
        return $this->amount;
    }

    public function setAmount(?float $amount): void {
        $this->amount = $amount;
    }

    public function getUnit(): string {
        return $this->unit;
    }

    public function setUnit(string $unit): void {
        $this->unit = $unit;
    }
}
