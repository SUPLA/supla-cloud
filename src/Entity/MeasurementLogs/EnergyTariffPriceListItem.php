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
 * @ORM\Table(name="supla_energy_tariff_price_list_item", indexes={
 *     @ORM\Index(name="idx_price_list_component_zone", columns={"price_list_id", "component_code", "zone_code"})
 * })
 */
class EnergyTariffPriceListItem {
    /** @ORM\Id @ORM\Column(name="id", type="bigint") @ORM\GeneratedValue(strategy="AUTO") */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="EnergyTariffPriceList", inversedBy="items")
     * @ORM\JoinColumn(name="price_list_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private ?EnergyTariffPriceList $priceList = null;

    /** @ORM\Column(name="component_code", type="string", length=100) */
    private string $componentCode = '';

    /** @ORM\Column(name="zone_code", type="string", length=100, nullable=true) */
    private ?string $zoneCode = null;

    /** @ORM\Column(name="amount", type="decimal", precision=12, scale=6) */
    private ?float $amount = null;

    /** @ORM\Column(name="unit", type="string", length=20) */
    private string $unit = '';

    /** @ORM\Column(name="currency", type="string", length=10) */
    private string $currency = '';

    public function getId() {
        return $this->id;
    }

    public function getPriceList(): ?EnergyTariffPriceList {
        return $this->priceList;
    }

    public function setPriceList(?EnergyTariffPriceList $priceList): void {
        $this->priceList = $priceList;
    }

    public function getComponentCode(): string {
        return $this->componentCode;
    }

    public function setComponentCode(string $componentCode): void {
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

    public function getCurrency(): string {
        return $this->currency;
    }

    public function setCurrency(string $currency): void {
        $this->currency = $currency;
    }
}
