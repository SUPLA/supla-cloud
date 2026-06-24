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
 * @ORM\Table(name="supla_energy_tariff_profile_tariff_period", indexes={
 *     @ORM\Index(name="idx_tariff_profile_period_profile_time", columns={"profile_id", "valid_from", "valid_to"}),
 *     @ORM\Index(name="idx_tariff_profile_period_tariff", columns={"tariff_id"})
 * })
 */
class EnergyTariffProfileTariffPeriod {
    /** @ORM\Id @ORM\Column(name="id", type="bigint") @ORM\GeneratedValue(strategy="AUTO") */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="EnergyTariffProfile", inversedBy="tariffPeriods")
     * @ORM\JoinColumn(name="profile_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private ?EnergyTariffProfile $profile = null;

    /**
     * @ORM\ManyToOne(targetEntity="EnergyTariff", inversedBy="profileTariffPeriods")
     * @ORM\JoinColumn(name="tariff_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private ?EnergyTariff $tariff = null;

    /** @ORM\Column(name="valid_from", type="utcdatetime") */
    private \DateTime $validFrom;

    /** @ORM\Column(name="valid_to", type="utcdatetime", nullable=true) */
    private ?\DateTime $validTo = null;

    /**
     * @var Collection<int, EnergyTariffProfilePricePeriod>
     * @ORM\OneToMany(targetEntity="EnergyTariffProfilePricePeriod", mappedBy="tariffPeriod", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private Collection $pricePeriods;

    public function __construct() {
        $this->pricePeriods = new ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function getProfile(): ?EnergyTariffProfile {
        return $this->profile;
    }

    public function setProfile(?EnergyTariffProfile $profile): void {
        $this->profile = $profile;
    }

    public function getTariff(): ?EnergyTariff {
        return $this->tariff;
    }

    public function setTariff(?EnergyTariff $tariff): void {
        $this->tariff = $tariff;
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
     * @return Collection<int, EnergyTariffProfilePricePeriod>
     */
    public function getPricePeriods(): Collection {
        return $this->pricePeriods;
    }

    public function addPricePeriod(EnergyTariffProfilePricePeriod $pricePeriod): void {
        if (!$this->pricePeriods->contains($pricePeriod)) {
            $this->pricePeriods->add($pricePeriod);
            $pricePeriod->setTariffPeriod($this);
        }
    }

    public function removePricePeriod(EnergyTariffProfilePricePeriod $pricePeriod): void {
        if ($this->pricePeriods->removeElement($pricePeriod) && $pricePeriod->getTariffPeriod() === $this) {
            $pricePeriod->setTariffPeriod(null);
        }
    }
}
