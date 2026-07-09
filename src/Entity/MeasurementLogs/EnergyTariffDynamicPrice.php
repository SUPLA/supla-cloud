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
 * @ORM\Table(name="supla_energy_tariff_dynamic_price", indexes={
 *     @ORM\Index(name="idx_tariff_dynamic_price_tariff_time", columns={"tariff_id", "date_from", "date_to"})
 * }, uniqueConstraints={
 *     @ORM\UniqueConstraint(name="uq_tariff_dynamic_price_slot", columns={"tariff_id", "component_code", "date_from"})
 * })
 */
class EnergyTariffDynamicPrice {
    /** @ORM\Id @ORM\Column(name="id", type="bigint") @ORM\GeneratedValue(strategy="AUTO") */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="EnergyTariff")
     * @ORM\JoinColumn(name="tariff_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private ?EnergyTariff $tariff = null;

    /** @ORM\Column(name="component_code", type="integer", enumType=EnergyPriceComponent::class) */
    private EnergyPriceComponent $componentCode;

    /** @ORM\Column(name="date_from", type="utcdatetime") */
    private \DateTime $dateFrom;

    /** @ORM\Column(name="date_to", type="utcdatetime") */
    private \DateTime $dateTo;

    /** @ORM\Column(name="currency", type="string", length=10) */
    private string $currency = '';

    /** @ORM\Column(name="amount", type="decimal", precision=12, scale=6) */
    private ?float $amount = null;

    public function getId() {
        return $this->id;
    }

    public function getTariff(): ?EnergyTariff {
        return $this->tariff;
    }

    public function setTariff(?EnergyTariff $tariff): void {
        $this->tariff = $tariff;
    }

    public function getComponentCode(): EnergyPriceComponent {
        return $this->componentCode;
    }

    public function setComponentCode(EnergyPriceComponent $componentCode): void {
        $this->componentCode = $componentCode;
    }

    public function getDateFrom(): \DateTime {
        return $this->dateFrom;
    }

    public function setDateFrom(\DateTime $dateFrom): void {
        $dateFrom->setTimezone(new \DateTimeZone('UTC'));
        $this->dateFrom = $dateFrom;
    }

    public function getDateTo(): \DateTime {
        return $this->dateTo;
    }

    public function setDateTo(\DateTime $dateTo): void {
        $dateTo->setTimezone(new \DateTimeZone('UTC'));
        $this->dateTo = $dateTo;
    }

    public function getCurrency(): string {
        return $this->currency;
    }

    public function setCurrency(string $currency): void {
        $this->currency = $currency;
    }

    public function getAmount(): ?float {
        return $this->amount;
    }

    public function setAmount(?float $amount): void {
        $this->amount = $amount;
    }
}
