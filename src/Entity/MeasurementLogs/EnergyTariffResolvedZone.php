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
 * @ORM\Table(name="supla_energy_tariff_resolved_zone", indexes={
 *     @ORM\Index(name="idx_tariff_period", columns={"tariff_id", "period_start", "period_end"})
 * }, uniqueConstraints={
 *     @ORM\UniqueConstraint(name="uq_tariff_zone_start", columns={"tariff_id", "zone_code", "period_start"})
 * })
 */
class EnergyTariffResolvedZone {
    /** @ORM\Id @ORM\Column(name="id", type="bigint") @ORM\GeneratedValue(strategy="AUTO") */
    private $id;

    /** @ORM\Column(name="tariff_id", type="bigint") */
    private $tariffId;

    /** @ORM\Column(name="zone_code", type="string", length=100) */
    private string $zoneCode;

    /** @ORM\Column(name="period_start", type="utcdatetime") */
    private $periodStart;

    /** @ORM\Column(name="period_end", type="utcdatetime") */
    private $periodEnd;

    public function __construct($tariffId, string $zoneCode, \DateTime $periodStart, \DateTime $periodEnd) {
        $this->tariffId = $tariffId;
        $this->zoneCode = $zoneCode;
        $periodStart->setTimezone(new \DateTimeZone('UTC'));
        $periodEnd->setTimezone(new \DateTimeZone('UTC'));
        $this->periodStart = $periodStart;
        $this->periodEnd = $periodEnd;
    }

    public function getId() {
        return $this->id;
    }

    public function getTariffId() {
        return $this->tariffId;
    }

    public function getZoneCode(): string {
        return $this->zoneCode;
    }

    public function getPeriodStart(): \DateTime {
        return $this->periodStart;
    }

    public function getPeriodEnd(): \DateTime {
        return $this->periodEnd;
    }
}
