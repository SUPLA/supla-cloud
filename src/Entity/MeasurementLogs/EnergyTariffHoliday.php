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
 * @ORM\Table(name="supla_energy_tariff_holidays", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="uq_timezone_date", columns={"timezone", "date"})
 * }, indexes={
 *     @ORM\Index(name="idx_timezone_date", columns={"timezone", "date"})
 * })
 */
class EnergyTariffHoliday {
    /** @ORM\Id @ORM\Column(name="id", type="bigint") @ORM\GeneratedValue(strategy="AUTO") */
    private $id;

    /** @ORM\Column(name="timezone", type="string", length=100) */
    private string $timezone;

    /** @ORM\Column(name="date", type="date_immutable") */
    private \DateTimeImmutable $date;

    public function __construct(string $timezone, \DateTimeImmutable $date) {
        $this->timezone = $timezone;
        $this->date = $date;
    }

    public function getId() {
        return $this->id;
    }

    public function getTimezone(): string {
        return $this->timezone;
    }

    public function getDate(): \DateTimeImmutable {
        return $this->date;
    }
}
