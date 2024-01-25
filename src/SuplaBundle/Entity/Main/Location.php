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

use Assert\Assertion;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use SuplaBundle\Entity\BelongsToUser;
use SuplaBundle\Entity\HasRelationsCount;
use SuplaBundle\Entity\HasRelationsCountTrait;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @ORM\Entity(repositoryClass="SuplaBundle\Repository\LocationRepository")
 * @ORM\Table(name="supla_location")
 */
class Location implements HasRelationsCount {
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
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     * @Groups({"basic"})
     */
    private $enabled;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="locations")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToMany(targetEntity="AccessID", inversedBy="locations", cascade={"persist"})
     * @ORM\JoinTable(name="supla_rel_aidloc",
     * joinColumns={@ORM\JoinColumn(name="location_id", referencedColumnName="id")},
     * inverseJoinColumns={@ORM\JoinColumn(name="access_id", referencedColumnName="id")}
     * )
     * @Groups({"location.accessids"})
     * @MaxDepth(1)
     */
    private $accessIds;

    /**
     * @ORM\OneToMany(targetEntity="IODevice", mappedBy="location")
     * @Groups({"location.iodevices"})
     * @MaxDepth(1)
     */
    private $ioDevices;

    /**
     * @ORM\OneToMany(targetEntity="IODeviceChannelGroup", mappedBy="location")
     * @Groups({"location.channelGroups"})
     * @MaxDepth(1)
     */
    private $channelGroups;

    /**
     * @ORM\OneToMany(targetEntity="Scene", mappedBy="location")
     * @Groups({"scenes"})
     * @MaxDepth(1)
     */
    private $scenes;

    /**
     * @ORM\OneToMany(targetEntity="IODeviceChannel", mappedBy="location")
     * @Groups({"location.channels"})
     * @MaxDepth(1)
     */
    private $channels;

    /**
     * @ORM\OneToMany(targetEntity="IODevice", mappedBy="originalLocation")
     */
    private $ioDevices_ol;

    /**
     * @param User|null $user
     */
    public function __construct($user = null) {
        $this->enabled = true;
        $this->accessIds = new ArrayCollection();
        $this->ioDevices = new ArrayCollection();
        $this->ioDevices_ol = new ArrayCollection();
        $this->channelGroups = new ArrayCollection();
        $this->scenes = new ArrayCollection();
        $this->channels = new ArrayCollection();

        if ($user) {
            $this->user = $user;
            if ($user->getAccessIDS()->count() > 0) {
                $aid = $user->getAccessIDS()->get(0);

                if ($aid !== null) {
                    $this->accessIds->add($aid);
                    $aid->getLocations()->add($this);
                    $user->getLocations()->add($this);
                }
            }
        }
    }

    public function getCaption() {
        return $this->caption;
    }

    public function setCaption($caption) {
        $this->caption = $caption;
    }

    public function generatePassword(): string {
        Assertion::null($this->password);
        $this->setPassword(bin2hex(random_bytes(2)));
        return $this->getPassword();
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function getUser() {
        return $this->user;
    }

    public function getId() {
        return $this->id;
    }

    /** @return IODevice[]|Collection */
    public function getIoDevices(): Collection {
        return $this->ioDevices;
    }

    /** @return IODevice[]|Collection */
    public function getIoDevicesByOriginalLocation(): Collection {
        return $this->ioDevices_ol;
    }

    /** @return IODeviceChannelGroup[]|Collection */
    public function getChannelGroups(): Collection {
        return $this->channelGroups;
    }

    /** @return Scene[]|Collection */
    public function getScenes(): Collection {
        return $this->scenes;
    }

    /** @return IODeviceChannel[]|Collection */
    public function getChannels(): Collection {
        return $this->channels;
    }

    /** @return AccessID[]|Collection */
    public function getAccessIds(): Collection {
        return $this->accessIds;
    }

    public function getEnabled(): bool {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled) {
        $this->enabled = $enabled;
    }
}
