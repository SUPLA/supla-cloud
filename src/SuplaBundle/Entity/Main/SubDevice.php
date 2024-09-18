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

namespace SuplaBundle\Entity\Main;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="supla_subdevice")
 */
class SubDevice {
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @Groups({"basic"})
     */
    private $id;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="IODevice")
     * @ORM\JoinColumn(name="iodevice_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $device;

    /**
     * @ORM\Column(name="reg_date", type="utcdatetime")
     * @Groups({"basic"})
     */
    private $regDate;

    /**
     * @ORM\Column(name="updated_at", type="utcdatetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(name="name", type="string", length=200, nullable=true, options={"charset"="utf8mb4", "collation"="utf8mb4_unicode_ci"})
     * @Groups({"basic"})
     */
    private $name;

    /**
     * @ORM\Column(name="software_version", type="string", length=20, nullable=true, options={"charset"="utf8mb4", "collation"="utf8mb4_unicode_ci"})
     * @Groups({"basic"})
     */
    private $softwareVersion;

    /**
     * @ORM\Column(name="product_code", type="string", length=50, nullable=true, options={"charset"="utf8mb4", "collation"="utf8mb4_unicode_ci"})
     * @Groups({"basic"})
     */
    private $productCode;

    /**
     * @ORM\Column(name="serial_number", type="string", length=50, nullable=true, options={"charset"="utf8mb4", "collation"="utf8mb4_unicode_ci"})
     * @Groups({"basic"})
     */
    private $serialNumber;

    public function getId(): ?int {
        return $this->id;
    }

    public function getDevice(): ?IODevice {
        return $this->device;
    }

    public function getRegDate(): \DateTime {
        return $this->regDate;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function getSoftwareVersion(): ?string {
        return $this->softwareVersion;
    }

    public function getProductCode(): ?string {
        return $this->productCode;
    }

    public function getSerialNumber(): ?string {
        return $this->serialNumber;
    }
}
