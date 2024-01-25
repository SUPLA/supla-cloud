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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use SuplaBundle\Entity\ActiveHours;
use SuplaBundle\Entity\BelongsToUser;
use SuplaBundle\Entity\HasRelationsCount;
use SuplaBundle\Entity\HasRelationsCountTrait;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @ORM\Entity(repositoryClass="SuplaBundle\Repository\AccessIdRepository")
 * @ORM\Table(name="supla_accessid")
 */
class AccessID implements HasRelationsCount {
    use BelongsToUser;
    use HasRelationsCountTrait;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"basic"})
     */
    private $id;

    /**
     * @ORM\Column(name="password", type="string", length=32, nullable=false)
     * @Groups({"password"})
     */
    private $password;

    /**
     * @ORM\Column(name="caption", type="string", length=100, nullable=true, options={"charset"="utf8mb4", "collation"="utf8mb4_unicode_ci"})
     * @Groups({"basic"})
     */
    private $caption;

    /**
     * @ORM\Column(name="enabled", type="boolean",  nullable=false)
     * @Groups({"basic"})
     */
    private $enabled;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="accessids")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToMany(targetEntity="Location", mappedBy="accessIds", cascade={"persist"})
     * @Groups({"accessId.locations"})
     * @MaxDepth(1)
     */
    private $locations;

    /**
     * @ORM\ManyToMany(targetEntity="PushNotification", mappedBy="accessIds", cascade={"persist"})
     * @Groups({"accessId.notifications"})
     * @MaxDepth(1)
     */
    private $pushNotifications;

    /**
     * @ORM\OneToMany(targetEntity="ClientApp", mappedBy="accessId")
     * @Groups({"accessId.clientApps"})
     * @MaxDepth(1)
     **/
    private $clientApps;

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

    private $activeNow;

    /** @param User $user */
    public function __construct($user = null) {
        $this->enabled = true;
        $this->clientApps = new ArrayCollection();
        $this->locations = new ArrayCollection();

        if ($user) {
            $user->getAccessIDS()->add($this);
            $this->user = $user;
        }
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function setPassword(string $password) {
        $this->password = $password;
    }

    public function setCaption($caption) {
        $this->caption = $caption;
    }

    public function getCaption() {
        return $this->caption;
    }

    public function getUser(): User {
        return $this->user;
    }

    /** @return Location[]|Collection */
    public function getLocations(): Collection {
        return $this->locations;
    }

    /** @param Location[]|Collection $locations */
    public function updateLocations($locations) {
        foreach ($this->getLocations() as $location) {
            $location->getAccessIds()->removeElement($this);
        }
        $this->getLocations()->clear();
        foreach ($locations as $location) {
            $this->locations[] = $location;
            $location->getAccessIds()->add($this);
        }
    }

    public function getId() {
        return $this->id;
    }

    public function getEnabled() {
        return $this->enabled;
    }

    public function setEnabled($enabled) {
        $this->enabled = $enabled;
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

    /**
     * @Groups({"accessId.activeNow"})
     */
    public function isActiveNow(): ?bool {
        return $this->activeNow;
    }

    public function setActiveNow(bool $activeNow): void {
        $this->activeNow = $activeNow;
    }

    /** @return ClientApp[]|Collection */
    public function getClientApps(): Collection {
        return $this->clientApps;
    }
}
