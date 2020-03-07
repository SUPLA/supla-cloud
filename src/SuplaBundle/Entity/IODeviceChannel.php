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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use SuplaBundle\Entity\Common\HasRelationsCount;
use SuplaBundle\Entity\Common\HasRelationsCountTrait;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionBitsFlist;
use SuplaBundle\Enums\ChannelType;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @ORM\Entity(repositoryClass="SuplaBundle\Repository\IODeviceChannelRepository")
 * @ORM\Table(name="supla_dev_channel",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="UNIQUE_CHANNEL", columns={"iodevice_id","channel_number"})})
 */
class IODeviceChannel implements HasFunction, HasLocation, HasRelationsCount {
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
     * @ORM\Column(name="channel_number", type="integer", nullable=false)
     * @Groups({"basic"})
     */
    private $channelNumber;

    /**
     * @ORM\ManyToOne(targetEntity="IODevice", inversedBy="channels")
     * @ORM\JoinColumn(name="iodevice_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @Groups({"channel.iodevice"})
     * @MaxDepth(1)
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
     * @MaxDepth(1)
     */
    private $schedules;

    /**
     * @var SceneOperation[]
     * @ORM\OneToMany(targetEntity="SceneOperation", mappedBy="channel", cascade={"remove"})
     * @MaxDepth(1)
     */
    private $sceneOperations;

    /**
     * @ORM\ManyToOne(targetEntity="Location", inversedBy="channels")
     * @ORM\JoinColumn(name="location_id", referencedColumnName="id", nullable=true)
     * @Groups({"channel.location"})
     * @MaxDepth(1)
     */
    private $location;

    /**
     * @ORM\Column(name="caption", type="string", length=100, nullable=true)
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
    private $param1 = 0;

    /**
     * @ORM\Column(name="param2", type="integer", nullable=false)
     */
    private $param2 = 0;

    /**
     * @ORM\Column(name="param3", type="integer", nullable=false)
     */
    private $param3 = 0;

    /**
     * @ORM\Column(name="text_param1", type="string", length=255, nullable=true)
     */
    private $textParam1;

    /**
     * @ORM\Column(name="text_param2", type="string", length=255, nullable=true)
     */
    private $textParam2;

    /**
     * @ORM\Column(name="text_param3", type="string", length=255, nullable=true)
     */
    private $textParam3;

    /**
     * @ORM\Column(name="alt_icon", type="integer", nullable=true)
     * @Groups({"basic"})
     */
    private $altIcon = 0;

    /**
     * @ORM\ManyToOne(targetEntity="UserIcon", inversedBy="channels")
     * @ORM\JoinColumn(name="user_icon_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $userIcon;

    /**
     * @ORM\Column(name="hidden", type="boolean", nullable=false, options={"default"=0})
     * @Groups({"basic"})
     */
    private $hidden = false;

    /**
     * @ORM\ManyToMany(targetEntity="IODeviceChannelGroup", mappedBy="channels", cascade={"persist"})
     */
    private $channelGroups;

    /**
     * @ORM\OneToMany(targetEntity="DirectLink", mappedBy="channel")
     */
    private $directLinks;

    /**
     * @ORM\Column(name="flags", type="integer", nullable=true)
     * @Groups({"basic"})
     */
    private $flags = 0;

    /** @ORM\Column(name="config", type="text", nullable=true) */
    private $config;

    public function __construct() {
        $this->directLinks = new ArrayCollection();
        $this->schedules = new ArrayCollection();
        $this->channelGroups = new ArrayCollection();
        $this->sceneOperations = new ArrayCollection();
    }

    public function getId(): int {
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
        return ChannelType::safeInstance($this->type);
    }

    /** @return IODevice */
    public function getIoDevice() {
        return $this->iodevice;
    }

    public function getUser(): User {
        return $this->user;
    }

    public function getLocation(): Location {
        return $this->location ?: $this->getIoDevice()->getLocation();
    }

    /**
     * @param Location|null $location
     */
    public function setLocation($location) {
        $this->location = $location;
    }

    /** @Groups({"basic"}) */
    public function hasInheritedLocation(): bool {
        return !$this->location;
    }

    /** @return Collection|Schedule[] */
    public function getSchedules(): Collection {
        return $this->schedules;
    }

    /** @return Collection|SceneOperation[] */
    public function getSceneOperations(): Collection {
        return $this->sceneOperations;
    }

    public function getFunction(): ChannelFunction {
        return ChannelFunction::safeInstance($this->function);
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
        $this->param1 = $this->param2 = $this->param3 = 0;
        $this->altIcon = 0;
        $this->userIcon = null;
    }

    /**
     * @return int
     * @see ChannelFunctionBitsFlist
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

    public function getParam(int $paramNo): int {
        Assertion::inArray($paramNo, [1, 2, 3], 'Invalid param number: ' . $paramNo);
        $getter = "getParam$paramNo";
        return $this->{$getter}();
    }

    public function setParam(int $paramNo, int $value) {
        Assertion::inArray($paramNo, [1, 2, 3], 'Invalid param number: ' . $paramNo);
        $setter = "setParam$paramNo";
        return $this->{$setter}($value);
    }

    public function getParam1(): int {
        return $this->param1;
    }

    public function setParam1(int $param1) {
        $this->param1 = $param1;
    }

    public function getParam2(): int {
        return $this->param2;
    }

    public function setParam2(int $param2) {
        $this->param2 = $param2;
    }

    public function getParam3(): int {
        return $this->param3;
    }

    public function setParam3(int $param3) {
        $this->param3 = $param3;
    }

    /**
     * @return string|null
     */
    public function getTextParam1() {
        return $this->textParam1;
    }

    /**
     * @param string|null $textParam1
     */
    public function setTextParam1($textParam1) {
        $this->textParam1 = $textParam1;
    }

    /**
     * @return string|null
     */
    public function getTextParam2() {
        return $this->textParam2;
    }

    /**
     * @param string|null $textParam2
     */
    public function setTextParam2($textParam2) {
        $this->textParam2 = $textParam2;
    }

    /**
     * @return string|null
     */
    public function getTextParam3() {
        return $this->textParam3;
    }

    /**
     * @param string|null $textParam3
     */
    public function setTextParam3($textParam3) {
        $this->textParam3 = $textParam3;
    }

    public function getAltIcon(): int {
        return intval($this->altIcon);
    }

    public function setAltIcon($altIcon) {
        Assertion::between($altIcon, 0, $this->getFunction()->getMaxAlternativeIconIndex(), 'Invalid alternative icon has been chosen.');
        $this->altIcon = intval($altIcon);
    }

    /** @return UserIcon|null */
    public function getUserIcon() {
        return $this->userIcon;
    }

    /** @param UserIcon|null $userIcon */
    public function setUserIcon($userIcon) {
        $this->userIcon = $userIcon;
    }

    public function getHidden() {
        return $this->function === ChannelFunction::ACTION_TRIGGER ? true : $this->hidden;
    }

    public function setHidden($hidden) {
        $this->hidden = $hidden;
    }

    /** @return Collection|IODeviceChannelGroup[] */
    public function getChannelGroups(): Collection {
        return $this->channelGroups;
    }

    /** @return Collection|DirectLink[] */
    public function getDirectLinks(): Collection {
        return $this->directLinks;
    }

    public function buildServerSetCommand(string $type, array $actionParams): string {
        $params = array_merge([$this->getUser()->getId(), $this->getIoDevice()->getId(), $this->getId()], $actionParams);
        $params = implode(',', $params);
        return "SET-$type-VALUE:$params";
    }

    public function getFlags(): int {
        return intval($this->flags);
    }

    public function setConfig(array $config): void {
        $this->config = json_encode($config);
    }

    public function getConfig(): array {
        return $this->config ? (json_decode($this->config, true) ?: []) : [];
    }
}
