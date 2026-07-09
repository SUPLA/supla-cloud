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

use App\Enums\EnergyTariffType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="supla_energy_tariff")
 * @ORM\HasLifecycleCallbacks
 */
class EnergyTariff {
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** @ORM\Column(name="code", type="string", length=100) */
    private string $code = '';

    /** @ORM\Column(name="name", type="string", length=255) */
    private string $name = '';

    /** @ORM\Column(name="config_json", type="json") */
    private array $config = [];

    /** @ORM\Column(name="created_at", type="utcdatetime") */
    private \DateTime $createdAt;

    /** @ORM\Column(name="updated_at", type="utcdatetime") */
    private \DateTime $updatedAt;

    /**
     * @var Collection<int, EnergyTariffProfileTariffPeriod>
     * @ORM\OneToMany(targetEntity="EnergyTariffProfileTariffPeriod", mappedBy="tariff", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private Collection $profileTariffPeriods;

    public function __construct() {
        $this->profileTariffPeriods = new ArrayCollection();
        $this->updateTimestamps();
    }

    public function getId() {
        return $this->id;
    }

    public function getCode(): string {
        return $this->code;
    }

    public function setCode(string $code): void {
        $this->code = $code;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function getConfig(): array {
        return $this->config;
    }

    public function setConfig(array $config): void {
        $this->config = $config;
    }

    public function getType(): EnergyTariffType {
        $type = $this->config['type'] ?? EnergyTariffType::ZONED_STATIC->value;
        return EnergyTariffType::tryFrom((string)$type) ?? EnergyTariffType::ZONED_STATIC;
    }

    public function isDynamic(): bool {
        return $this->getType() === EnergyTariffType::DYNAMIC_15M;
    }

    public function getDynamicPriceSourceConfig(): array {
        return is_array($this->config['dynamicPriceSource'] ?? null) ? $this->config['dynamicPriceSource'] : [];
    }

    public function getCreatedAt(): \DateTime {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTime {
        return $this->updatedAt;
    }

    /**
     * @return Collection<int, EnergyTariffProfileTariffPeriod>
     */
    public function getProfileTariffPeriods(): Collection {
        return $this->profileTariffPeriods;
    }

    public function addProfileTariffPeriod(EnergyTariffProfileTariffPeriod $profileTariffPeriod): void {
        if (!$this->profileTariffPeriods->contains($profileTariffPeriod)) {
            $this->profileTariffPeriods->add($profileTariffPeriod);
            $profileTariffPeriod->setTariff($this);
        }
    }

    public function removeProfileTariffPeriod(EnergyTariffProfileTariffPeriod $profileTariffPeriod): void {
        if ($this->profileTariffPeriods->removeElement($profileTariffPeriod) && $profileTariffPeriod->getTariff() === $this) {
            $profileTariffPeriod->setTariff(null);
        }
    }

    /** @ORM\PrePersist */
    public function onPrePersist(): void {
        $this->updateTimestamps();
    }

    /** @ORM\PreUpdate */
    public function onPreUpdate(): void {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    private function updateTimestamps(): void {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->createdAt = $this->createdAt ?? $now;
        $this->updatedAt = $now;
    }
}
