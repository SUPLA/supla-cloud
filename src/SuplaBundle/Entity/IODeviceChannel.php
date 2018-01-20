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

use Assert\Assertion;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Enums\RelayFunctionBits;
use SuplaBundle\Validator\Constraints as SuplaAssert;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="SuplaBundle\Repository\IODeviceChannelRepository")
 * @ORM\Table(name="supla_dev_channel",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="UNIQUE_CHANNEL", columns={"iodevice_id","channel_number"})})
 * @SuplaAssert\Channel
 */
class IODeviceChannel {
    use BelongsToUser;

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
     * @Groups({"iodevice"})
     */
    private $iodevice;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="channels")
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
     * @Groups({"type"})
     */
    private $type;

    /**
     * @ORM\Column(name="func", type="integer", nullable=false)
     * @Groups({"function"})
     */
    private $function;

    /**
     * @ORM\Column(name="flist", type="integer", nullable=true)
     * @Groups({"basic"})
     */
    private $funcList;

    /**
     * @ORM\Column(name="param1", type="integer", nullable=false)
     * @Groups({"basic"})
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
     * @Groups({"basic"})
     */
    private $altIcon = 0;

    /**
     * @ORM\Column(name="hidden", type="boolean", nullable=false, options={"default"=0})
     * @Groups({"basic"})
     */
    private $hidden = false;

    /**
     * @ORM\ManyToMany(targetEntity="IODeviceChannelGroup", mappedBy="channels", cascade={"persist"})
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

    public function getType(): ChannelType {
        return new ChannelType($this->type);
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

    public function getFunction(): ChannelFunction {
        return new ChannelFunction($this->function);
    }

    /** @param $function ChannelFunction|int */
    public function setFunction($function) {
        if ($function instanceof ChannelFunction) {
            $function = $function->getValue();
        } else {
            $function = intval($function);
        }
        Assertion::true(ChannelFunction::isValid($function), "Not valid channel function: " . $function);
        $this->function = $function;
    }

    /**
     * @see RelayFunctionBits
     * @return int
     */
    public function getFuncList(): int {
        return $this->funcList ?: 0;
    }

    /**
     * @Groups({"supportedFunctions"})
     * @return ChannelFunction
     */
    public function getSupportedFunctions(): array {
        return ChannelFunction::forChannel($this);
    }

    /** @deprecated ridiculous */
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
        return ($this->getAltIcon() > 0 ? '_' . $this->getAltIcon() : '') . '.svg';
    }

    public function getIconFilename() {
        return $this->getFunction() . $this->getIconSuffix();
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
