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
use SuplaBundle\Entity\Main\Common\HasRelationsCount;
use SuplaBundle\Entity\Main\Common\HasRelationsCountTrait;
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
     * @ORM\Column(name="caption", type="string", length=100, nullable=true)
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
     * @ORM\OneToMany(targetEntity="ClientApp", mappedBy="accessId")
     * @Groups({"accessId.clientApps"})
     * @MaxDepth(1)
     **/
    private $clientApps;

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

    public function getUser() {
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

    /** @return ClientApp[]|Collection */
    public function getClientApps(): Collection {
        return $this->clientApps;
    }
}
