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
use JMS\Serializer\Annotation as Serializer;
use SuplaBundle\Validator\Constraints as SuplaAssert;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="supla_dev_channel",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="UNIQUE_CHANNEL", columns={"iodevice_id","channel_number"})})
 * @SuplaAssert\Channel
 */
class IODeviceChannel {
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"basic"})
     */
    private $id;

    /**
     * @ORM\Column(name="channel_number", type="integer", nullable=false)
     * @Groups({"basic"})
     */
    private $channelNumber;

    /**
     * @ORM\ManyToOne(targetEntity="IODevice", inversedBy="channels")
     * @ORM\JoinColumn(name="iodevice_id", referencedColumnName="id", nullable=false)
     * @Serializer\Groups({"iodevice", "location"})
     */
    private $iodevice;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @var Schedule[]
     * @ORM\OneToMany(targetEntity="Schedule", mappedBy="channel", cascade={"remove"})
     */
    private $schedules;

    /**
     * @ORM\Column(name="caption", type="string", length=100, nullable=true)
     * @Assert\Length(max=100)
     * @Groups({"basic"})
     */
    private $caption;

    /**
     * @ORM\Column(name="type", type="integer", nullable=false)
     * @Groups({"basic"})
     */
    private $type;

    /**
     * @ORM\Column(name="func", type="integer", nullable=false)
     * @Groups({"basic"})
     */
    private $function;

    /**
     * @ORM\Column(name="flist", type="integer", nullable=true)
     */
    private $funcList;

    /**
     * @ORM\Column(name="param1", type="integer", nullable=false)
     */
    private $param1 = '';

    /**
     * @ORM\Column(name="param2", type="integer", nullable=false)
     */
    private $param2 = '';

    /**
     * @ORM\Column(name="param3", type="integer", nullable=false)
     */
    private $param3 = '';

    /**
     * @ORM\Column(name="alt_icon", type="integer", nullable=true)
     */
    private $altIcon = 0;

    /**
     * @ORM\Column(name="hidden", type="boolean", nullable=false)
     * @Groups({"basic"})
     */
    private $hidden = false;

    /**
     * @ORM\ManyToMany(targetEntity="IODeviceChannelGroup", inversedBy="channels", cascade={"persist"})
     * @ORM\JoinTable(name="supla_rel_cg",
     * joinColumns={@ORM\JoinColumn(name="channel_id", referencedColumnName="id")},
     * inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     */
    private $channelGroups;

    public function getId() {
        return $this->id;
    }

    public function getChannelNumber() {
        return $this->channelNumber;
    }

    public function getCaption() {
        return $this->caption;
    }

    public function setCaption($caption) {
        $this->caption = $caption;
    }

    public function getType() {
        return $this->type;
    }

    /** @return IODevice */
    public function getIoDevice() {
        return $this->iodevice;
    }

    public function getUser() {
        return $this->user;
    }

    /** @return Collection|Schedule[] */
    public function getSchedules(): Collection {
        return $this->schedules;
    }

    public function getFunction() {
        return $this->function;
    }

    public function setFunction($function) {
        $this->function = $function;
    }

    public function getFuncList() {
        return $this->funcList;
    }

    public function setFuncList($funcList) {
        $this->funcList = $funcList;
    }

    public function getChannel() {
        return $this;
    }

    public function getParam1() {
        return $this->param1;
    }

    public function setParam1($param1) {
        $this->param1 = $param1;
    }

    public function getParam2() {
        return $this->param2;
    }

    public function setParam2($param2) {
        $this->param2 = $param2;
    }

    public function getParam3() {
        return $this->param3;
    }

    public function setParam3($param3) {
        $this->param3 = $param3;
    }

    public function getAltIcon() {
        return intval($this->altIcon);
    }

    public function setAltIcon($altIcon) {
        $this->altIcon = $altIcon;
    }

    public function getIconSuffix() {
        return ($this->getAltIcon() > 0 ? '_'.$this->getAltIcon() : '') . '.svg';
    }

    public function getIconFilename() {
        return $this->getFunction().$this->getIconSuffix();
    }

    public function getHidden() {
        return $this->hidden;
    }

    public function setHidden($hidden) {
        $this->hidden = $hidden;
    }

    /** @return Collection|IODeviceChannelGroup[] */
    public function getChannelGroups(): Collection {
        return $this->channelGroups;
    }
}
