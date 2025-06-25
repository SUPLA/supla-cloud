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
use SuplaBundle\Utils\DateUtils;

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
    private ?float $rce = null;

    /**
     * @ORM\Column(name="pdgsz", type="tinyint", nullable=true)
     */
    private ?int $pdgsz = null;

    /**
     * @ORM\Column(name="fixing1", type="decimal", precision=8, scale=4, nullable=true)
     */
    private ?float $fixing1 = null;

    /**
     * @ORM\Column(name="fixing2", type="decimal", precision=8, scale=4, nullable=true)
     */
    private ?float $fixing2 = null;

    public function __construct(\DateTime $dateFrom, \DateTime $dateTo) {
        $dateFrom->setTimezone(new \DateTimeZone('UTC'));
        $dateTo->setTimezone(new \DateTimeZone('UTC'));
        $this->dateFrom = DateUtils::timestampToMysqlUtc($dateFrom->getTimestamp());
        $this->dateTo = DateUtils::timestampToMysqlUtc($dateTo->getTimestamp());
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

    public function getPdgsz(): ?int {
        return $this->pdgsz;
    }

    public function getFixing1(): ?float {
        return $this->fixing1;
    }

    public function getFixing2(): ?float {
        return $this->fixing2;
    }

    public function setRce(?float $rce): void {
        $this->rce = $rce;
    }

    public function setPdgsz(?int $pdgsz): void {
        $this->pdgsz = $pdgsz;
    }

    public function setFixing1(?float $fixing1): void {
        $this->fixing1 = $fixing1;
    }

    public function setFixing2(?float $fixing2): void {
        $this->fixing2 = $fixing2;
    }
}
