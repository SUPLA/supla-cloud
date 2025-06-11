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

namespace SuplaBundle\Entity\MeasurementLogs;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="supla_energy_price_log")
 */
class EnergyPriceLogItem {
    /**
     * @ORM\Id
     * @ORM\Column(name="date_from", type="stringdatetime")
     */
    private $dateFrom;

    /**
     * @ORM\Column(name="date_to", type="stringdatetime")
     */
    private $dateTo;

    /**
     * @ORM\Column(name="rce", type="decimal", precision=8, scale=4, nullable=true)
     */
    private ?float $rce;

    /**
     * @ORM\Column(name="fixing1", type="decimal", precision=8, scale=4, nullable=true)
     */
    private ?float $fixing1;

    /**
     * @ORM\Column(name="fixing2", type="decimal", precision=8, scale=4, nullable=true)
     */
    private ?float $fixing2;

    public function __construct(\DateTime $dateFrom, \DateTime $dateTo, ?float $rce, ?float $fixing1, ?float $fixing2) {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->rce = $rce;
        $this->fixing1 = $fixing1;
        $this->fixing2 = $fixing2;
    }

    public function getDateFrom(): \DateTime {
        return $this->dateFrom instanceof \DateTime ? $this->dateFrom : new \DateTime($this->dateFrom, new \DateTimeZone('UTC'));
    }

    public function getDateTo(): \DateTime {
        return $this->dateTo instanceof \DateTime ? $this->dateTo : new \DateTime($this->dateTo, new \DateTimeZone('UTC'));
    }

    public function getRce(): ?float {
        return $this->rce;
    }

    public function getFixing1(): ?float {
        return $this->fixing1;
    }

    public function getFixing2(): ?float {
        return $this->fixing2;
    }
}
