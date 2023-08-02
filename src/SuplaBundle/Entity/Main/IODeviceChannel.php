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
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\BelongsToUser;
use SuplaBundle\Entity\HasIcon;
use SuplaBundle\Entity\HasLocation;
use SuplaBundle\Entity\HasRelationsCount;
use SuplaBundle\Entity\HasRelationsCountTrait;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Entity\HasUserConfigTrait;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ChannelFunctionBitsFlags;
use SuplaBundle\Enums\ChannelFunctionBitsFlist;
use SuplaBundle\Enums\ChannelType;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @ORM\Entity(repositoryClass="SuplaBundle\Repository\IODeviceChannelRepository")
 * @ORM\Table(name="supla_dev_channel",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="UNIQUE_CHANNEL", columns={"iodevice_id","channel_number"})},
 *     indexes={
 *       @ORM\Index(name="supla_dev_channel_param1_idx", columns={"param1"}),
 *       @ORM\Index(name="supla_dev_channel_param2_idx", columns={"param2"}),
 *       @ORM\Index(name="supla_dev_channel_param3_idx", columns={"param3"}),
 *       @ORM\Index(name="supla_dev_channel_param4_idx", columns={"param4"})
 *     }
 * )
 */
class IODeviceChannel implements ActionableSubject, HasLocation, HasRelationsCount, HasUserConfig, HasIcon {
    use BelongsToUser;
    use HasRelationsCountTrait;
    use HasUserConfigTrait;

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
     * @var ValueBasedTrigger[]
     * @ORM\OneToMany(targetEntity="ValueBasedTrigger", mappedBy="owningChannel", cascade={"remove"})
     * @MaxDepth(1)
     */
    private $ownReactions;

    /**
     * @var ValueBasedTrigger[]
     * @ORM\OneToMany(targetEntity="ValueBasedTrigger", mappedBy="channel", cascade={"remove"})
     * @MaxDepth(1)
     */
    private $reactions;

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
     * @ORM\Column(name="param4", type="integer", nullable=false, options={"default"=0})
     */
    private $param4 = 0;

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
     * @MaxDepth(1)
     * @Groups({"channel.userIcon"})
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

    /** @ORM\Column(name="user_config", type="string", length=2048, nullable=true) */
    private $userConfig;

    /** @ORM\Column(name="properties", type="string", length=2048, nullable=true) */
    private $properties;

    public function __construct() {
        $this->directLinks = new ArrayCollection();
        $this->schedules = new ArrayCollection();
        $this->channelGroups = new ArrayCollection();
        $this->sceneOperations = new ArrayCollection();
        $this->ownReactions = new ArrayCollection();
        $this->reactions = new ArrayCollection();
    }

    public function getId(): int {
        return $this->id;
    }

    /** @Groups({"basic"}) */
    public function getOwnSubjectType(): string {
        return ActionableSubjectType::CHANNEL;
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

    /** @return Collection|ValueBasedTrigger[] */
    public function getOwnReactions(): Collection {
        return $this->ownReactions;
    }

    /** @return Collection|ValueBasedTrigger[] */
    public function getReactions(): Collection {
        return $this->reactions;
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
        Assertion::true(ChannelFunction::isValid($function), 'Not valid channel function: ' . $function);
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

    /**
     * @Groups({"basic"})
     * @return ChannelFunctionAction[]
     */
    public function getPossibleActions(): array {
        $actions = $this->getFunction()->getDefaultPossibleActions();
        if (in_array($this->function, [ChannelFunction::CONTROLLINGTHEROLLERSHUTTER, ChannelFunction::CONTROLLINGTHEROOFWINDOW])
            && ChannelFunctionBitsFlags::ROLLER_SHUTTER_STEP_BY_STEP_ACTIONS()->isSupported($this->flags)) {
            $actions = array_merge($actions, [
                ChannelFunctionAction::UP_OR_STOP(),
                ChannelFunctionAction::DOWN_OR_STOP(),
                ChannelFunctionAction::STEP_BY_STEP(),
            ]);
        }
        return $actions;
    }

    public function getParam(int $paramNo): int {
        Assertion::inArray($paramNo, [1, 2, 3, 4], 'Invalid param number: ' . $paramNo);
        $getter = "getParam$paramNo";
        return $this->{$getter}();
    }

    public function setParam(int $paramNo, int $value) {
        Assertion::inArray($paramNo, [1, 2, 3, 4], 'Invalid param number: ' . $paramNo);
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

    public function getParam4(): int {
        return $this->param4;
    }

    public function setParam4(int $param4) {
        $this->param4 = $param4;
    }

    public function getTextParam1(): ?string {
        return $this->textParam1;
    }

    public function setTextParam1(?string $textParam1) {
        $this->textParam1 = $textParam1;
    }

    public function getTextParam2(): ?string {
        return $this->textParam2;
    }

    public function setTextParam2(?string $textParam2) {
        $this->textParam2 = $textParam2;
    }

    public function getTextParam3(): ?string {
        return $this->textParam3;
    }

    public function setTextParam3(?string $textParam3) {
        $this->textParam3 = $textParam3;
    }

    public function getAltIcon(): int {
        return intval($this->altIcon);
    }

    public function setAltIcon($altIcon) {
        Assertion::between($altIcon, 0, $this->getFunction()->getMaxAlternativeIconIndex(), 'Invalid alternative icon has been chosen.');
        $this->altIcon = intval($altIcon);
    }

    public function getUserIcon(): ?UserIcon {
        return $this->userIcon;
    }

    /** @param UserIcon|null $userIcon */
    public function setUserIcon($userIcon) {
        $this->userIcon = $userIcon;
    }

    public function getHidden(): bool {
        return $this->function === ChannelFunction::ACTION_TRIGGER ? true : $this->hidden;
    }

    public function setHidden(bool $hidden) {
        if ($this->function !== ChannelFunction::ACTION_TRIGGER) {
            $this->hidden = $hidden;
        }
    }

    /** @return Collection|IODeviceChannelGroup[] */
    public function getChannelGroups(): Collection {
        return $this->channelGroups;
    }

    /** @return Collection|DirectLink[] */
    public function getDirectLinks(): Collection {
        return $this->directLinks;
    }

    public function buildServerActionCommand(string $command, array $actionParams = []): string {
        $params = array_merge([$this->getUser()->getId(), $this->getIoDevice()->getId(), $this->getId()], $actionParams);
        $params = implode(',', $params);
        return "$command:$params";
    }

    public function getFlags(): int {
        return intval($this->flags);
    }

    public function getProperties(): array {
        return $this->properties ? (json_decode($this->properties, true) ?: []) : [];
    }
}
