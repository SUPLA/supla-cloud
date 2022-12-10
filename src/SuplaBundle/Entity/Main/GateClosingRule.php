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
use SuplaBundle\Entity\ActiveHours;
use SuplaBundle\Entity\BelongsToUser;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="SuplaBundle\Repository\GateClosingRuleRepository")
 * @ORM\Table(name="supla_auto_gate_closing")
 */
class GateClosingRule {
    use BelongsToUser;

    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="IODeviceChannel")
     * @ORM\JoinColumn(name="channel_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $channel;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="accessids")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(name="active_from", type="utcdatetime", nullable=true)
     * @Groups({"basic"})
     */
    private $activeFrom;

    /**
     * @ORM\Column(name="active_to", type="utcdatetime", nullable=true)
     * @Groups({"basic"})
     */
    private $activeTo;

    /**
     * @ORM\Column(name="active_hours", type="string", length=768, nullable=true)
     * @Groups({"basic"})
     */
    private $activeHours;

    /**
     * @ORM\Column(name="max_time_open", type="integer")
     * @Groups({"basic"})
     */
    private $maxTimeOpen = 0;

    /**
     * @ORM\Column(name="seconds_open", type="integer", nullable=true)
     */
    private $secondsOpen;

    /**
     * @ORM\Column(name="closing_attempt", type="utcdatetime", nullable=true)
     */
    private $closingAttempt;

    /**
     * @ORM\Column(name="last_seen_open", type="utcdatetime", nullable=true)
     */
    private $lastSeenOpen;

    /**
     * @ORM\Column(name="enabled", type="boolean", nullable=false, options={"default"=0})
     * @Groups({"basic"})
     */
    private $enabled = false;

    private $activeNow;

    public function __construct(IODeviceChannel $channel) {
        $this->channel = $channel;
        $this->user = $channel->getUser();
    }

    public function getMaxTimeOpen(): int {
        return $this->maxTimeOpen;
    }

    public function setMaxTimeOpen(int $maxTimeOpen): void {
        $this->maxTimeOpen = $maxTimeOpen;
    }

    public function getActiveFrom(): ?\DateTime {
        return $this->activeFrom;
    }

    public function setActiveFrom(?\DateTime $activeFrom): void {
        $this->activeFrom = $activeFrom;
    }

    public function getActiveTo(): ?\DateTime {
        return $this->activeTo;
    }

    public function setActiveTo(?\DateTime $activeTo): void {
        $this->activeTo = $activeTo;
    }

    public function getActiveHours(): ?array {
        return ActiveHours::fromString($this->activeHours)->toArray();
    }

    public function setActiveHours(?array $activeHours): void {
        $this->activeHours = ActiveHours::fromArray($activeHours)->toString();
    }

    /** @Groups({"basic"}) */
    public function isActiveNow(): ?bool {
        return $this->activeNow;
    }

    public function setActiveNow(bool $activeNow): void {
        $this->activeNow = $activeNow;
    }

    public function isEnabled(): bool {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled) {
        $this->enabled = $enabled;
    }
}
