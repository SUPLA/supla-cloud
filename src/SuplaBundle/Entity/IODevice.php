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

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use SuplaBundle\Entity\Loaction;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="supla_iodevice")
 * @UniqueEntity(fields="id", message="IODevice already exists")
 */
class IODevice {

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"basic"})
     */
    private $id;

    /**
     * @ORM\Column(name="guid", type="binary", length=16, nullable=false, unique=true)
     */
    private $guid;

    /**
     * @ORM\Column(name="name", type="string", length=100, nullable=true)
     * @Assert\Length(max=100)
     * @Groups({"basic"})
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="Location", inversedBy="ioDevices")
     * @ORM\JoinColumn(name="location_id", referencedColumnName="id", nullable=false)
     * @Groups({"location"})
     */
    private $location;

    /**
     * @ORM\ManyToOne(targetEntity="Location", inversedBy="ioDevices_ol")
     * @ORM\JoinColumn(name="original_location_id", referencedColumnName="id", nullable=true)
     * @Groups({"originalLocation"})
     */
    private $originalLocation;

    /**
     * @var IODeviceChannel[]
     * @ORM\OneToMany(targetEntity="IODeviceChannel", mappedBy="iodevice")
     * @Groups({"channels"})
     */
    private $channels;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="iodevices")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     * @Groups({"basic"})
     */
    private $enabled = true;

    /**
     * @ORM\Column(name="comment", type="string", length=200, nullable=true)
     * @Assert\Length(max=200)
     * @Groups({"basic"})
     */
    private $comment;

    /**
     * @ORM\Column(name="reg_date", type="utcdatetime")
     * @Assert\NotBlank()
     * @Groups({"basic"})
     */
    private $regDate;

    /**
     * @ORM\Column(name="reg_ipv4", type="integer", nullable=true, options={"unsigned"=true})
     * @Groups({"basic"})
     */
    private $regIpv4;

    /**
     * @ORM\Column(name="last_connected", type="utcdatetime", nullable=true)
     * @Groups({"basic"})
     */
    private $lastConnected;

    /**
     * @ORM\Column(name="last_ipv4", type="integer", nullable=true)
     * @Groups({"basic"})
     */
    private $lastIpv4;

    /**
     * @ORM\Column(name="software_version", type="string", length=10, nullable=false, options={"unsigned"=true})
     * @Groups({"basic"})
     */
    private $softwareVersion;

    /**
     * @ORM\Column(name="protocol_version", type="integer", nullable=false)
     */
    private $protocolVersion;

    /**
     * @ORM\Column(name="auth_key", type="string", length=64, nullable=true)
     */
    private $authKey;

    public function getEnabled() {
        return $this->enabled;
    }

    public function setEnabled($enabled) {
        $this->enabled = $enabled;
    }

    public function getComment() {
        return $this->comment;
    }

    public function setComment($comment) {
        $this->comment = $comment;
    }

    public function getName() {
        return $this->name;
    }

    /** @return Location */
    public function getLocation() {
        return $this->location;
    }

    public function setLocation(Location $location) {
        $this->location = $location;
    }

    /** @return Location */
    public function getOriginalLocation() {
        return $this->originalLocation;
    }

    /** @return Collection|IODeviceChannel[] */
    public function getChannels(): Collection {
        return $this->channels;
    }

    /** @return User */
    public function getUser() {
        return $this->user;
    }

    public function getRegDate() {
        return $this->regDate;
    }

    public function getRegIpv4() {
        return $this->regIpv4;
    }

    public function getLastConnected() {
        return $this->lastConnected;
    }

    public function getLastIpv4() {
        return $this->lastIpv4;
    }

    public function getId() {
        return $this->id;
    }

    public function getGUID() {
        $guid = $this->guid;

        if (get_resource_type($guid) == 'stream') {
            $guid = bin2hex(stream_get_contents($guid, -1, 0));
        };

        return $guid;
    }

    /**
     * @Groups({"basic"})
     */
    public function getGUIDString(): string {
        $guid = $this->getGUID();

        return strtoupper(substr($guid, 0, 8) . '-'
            . substr($guid, 8, 4) . '-'
            . substr($guid, 12, 4) . '-'
            . substr($guid, 16, 4) . '-'
            . substr($guid, 20, 12));
    }

    public function getSoftwareVersion() {
        return $this->softwareVersion;
    }

    public function getProtocolVersion() {
        return $this->protocolVersion;
    }
}
