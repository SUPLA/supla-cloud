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

namespace SuplaBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="SuplaBundle\Repository\LocationRepository")
 * @ORM\Table(name="supla_location")
 */
class Location {
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"basic"})
     */
    private $id;

    /**
     * @ORM\Column(name="password", type="string", length=32, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Length(min=4, max=10)
     */
    private $password;

    /**
     * @ORM\Column(name="caption", type="string", length=100, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Length(min=1, max=100)
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
     */
    private $accessIds;

    /**
     * @ORM\OneToMany(targetEntity="IODevice", mappedBy="location")
     */
    private $ioDevices;

    /**
     * @ORM\OneToMany(targetEntity="IODeviceChannelGroup", mappedBy="location")
     */
    private $ioDeviceChannelGroups;

    /**
     * @ORM\OneToMany(targetEntity="IODeviceChannel", mappedBy="location")
     */
    private $ioDeviceChannels;

    /**
     * @ORM\OneToMany(targetEntity="IODevice", mappedBy="originalLocation")
     */
    private $ioDevices_ol;

    public function __construct(User $user) {

        $this->enabled = true;
        $this->accessIds = new ArrayCollection();
        $this->ioDevices = new ArrayCollection();
        $this->ioDevices_ol = new ArrayCollection();

        if ($user !== null) {
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

    public function getIoDevices() {
        return $this->ioDevices;
    }

    public function getIoDevicesByOriginalLocation() {
        return $this->ioDevices_ol;
    }

    public function getAccessIds() {
        return $this->accessIds;
    }

    public function getEnabled() {
        return $this->enabled;
    }

    public function setEnabled($enabled) {
        $this->enabled = $enabled;
    }
}
