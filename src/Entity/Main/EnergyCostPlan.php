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

namespace App\Entity\Main;

use App\Entity\BelongsToUser;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EnergyCostPlanRepository")
 * @ORM\Table(name="supla_energy_cost_plan", indexes={
 *     @ORM\Index(name="supla_energy_cost_plan_user_idx", columns={"user_id"})
 * })
 */
class EnergyCostPlan {
    use BelongsToUser;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected ?string $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private User $user;

    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private string $name;

    /**
     * @ORM\Column(name="configuration_json", type="json", nullable=false)
     * @var array<string, mixed>
     */
    private array $configuration;

    /**
     * @ORM\Column(name="created_at", type="utcdatetime", nullable=false)
     */
    private DateTime $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="utcdatetime", nullable=false)
     */
    private DateTime $updatedAt;

    /** @param array<string, mixed> $configuration */
    public function __construct(User $user, string $name, array $configuration, DateTime $createdAt) {
        $this->user = $user;
        $this->name = $name;
        $this->configuration = $configuration;
        $this->createdAt = $createdAt;
        $this->updatedAt = clone $createdAt;
    }

    public function getId(): ?string {
        return $this->id;
    }

    public function getUser(): User {
        return $this->user;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name, DateTime $updatedAt): void {
        $this->name = $name;
        $this->updatedAt = $updatedAt;
    }

    /** @return array<string, mixed> */
    public function getConfiguration(): array {
        return $this->configuration;
    }

    /** @param array<string, mixed> $configuration */
    public function setConfiguration(array $configuration, DateTime $updatedAt): void {
        $this->configuration = $configuration;
        $this->updatedAt = $updatedAt;
    }

    public function getCreatedAt(): DateTime {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime {
        return $this->updatedAt;
    }
}
