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
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @ORM\Entity(repositoryClass="SuplaBundle\Repository\ChannelGroupRepository")
 * @ORM\Table(name="supla_dev_channel_group")
 */
class IODeviceChannelGroup implements ActionableSubject, HasLocation, HasRelationsCount, HasIcon {
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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="channelGroups")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Location", inversedBy="channelGroups")
     * @ORM\JoinColumn(name="location_id", referencedColumnName="id", nullable=false)
     * @Groups({"channelGroup.location"})
     * @MaxDepth(1)
     */
    private $location;

    /**
     * @ORM\ManyToMany(targetEntity="IODeviceChannel", inversedBy="channelGroups", cascade={"persist"})
     * @ORM\JoinTable(name="supla_rel_cg", joinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")},
     * inverseJoinColumns={@ORM\JoinColumn(name="channel_id", referencedColumnName="id")} )
     * @Groups({"channelGroup.channels"})
     * @MaxDepth(1)
     * @var Collection|IODeviceChannel[]
     */
    private $channels;

    /**
     * @ORM\Column(name="hidden", type="boolean", nullable=false, options={"default"=0})
     * @Groups({"basic"})
     */
    private $hidden = false;

    /**
     * @ORM\Column(name="caption", type="string", length=255, nullable=true, options={"charset"="utf8mb4", "collation"="utf8mb4_unicode_ci"})
     * @Groups({"basic"})
     */
    private $caption;

    /**
     * @ORM\Column(name="func", type="integer", nullable=false)
     * @Groups({"basic"})
     */
    private $function;

    /**
     * @ORM\ManyToOne(targetEntity="UserIcon", inversedBy="channelGroups")
     * @ORM\JoinColumn(name="user_icon_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     * @MaxDepth(1)
     * @Groups({"channelGroup.userIcon"})
     */
    private $userIcon;

    /**
     * @ORM\Column(name="alt_icon", type="integer", nullable=true)
     * @Groups({"basic"})
     */
    private $altIcon = 0;

    /**
     * @ORM\OneToMany(targetEntity="DirectLink", mappedBy="channelGroup")
     */
    private $directLinks;

    /**
     * @var Schedule[]
     * @ORM\OneToMany(targetEntity="Schedule", mappedBy="channelGroup", cascade={"remove"})
     */
    private $schedules;

    /**
     * @var SceneOperation[]
     * @ORM\OneToMany(targetEntity="SceneOperation", mappedBy="channelGroup", cascade={"remove"})
     * @MaxDepth(1)
     */
    private $sceneOperations;

    /**
     * @var ValueBasedTrigger[]
     * @ORM\OneToMany(targetEntity="ValueBasedTrigger", mappedBy="channelGroup", cascade={"remove"})
     * @MaxDepth(1)
     */
    private $reactions;

    /** @param IODeviceChannel[] $channels */
    public function __construct(User $user = null, Location $location = null, array $channels = []) {
        $this->channels = new ArrayCollection();
        $this->directLinks = new ArrayCollection();
        $this->schedules = new ArrayCollection();
        $this->sceneOperations = new ArrayCollection();
        $this->user = $user;
        $this->location = $location;
        $this->reactions = new ArrayCollection();
        if (count($channels)) {
            Assertion::notNull($user);
            Assertion::notNull($location);
            $this->setChannels($channels);
        }
    }

    public function getId() {
        return $this->id;
    }

    /** @Groups({"basic"}) */
    public function getOwnSubjectType(): string {
        return ActionableSubjectType::CHANNEL_GROUP;
    }

    /**
     * @Groups({"basic"})
     * @return ChannelFunctionAction[]
     */
    public function getPossibleActions(): array {
        return $this->getFunction()->getDefaultPossibleActions();
    }

    public function getUser(): User {
        return $this->user;
    }

    public function getLocation(): Location {
        return $this->location;
    }

    public function setLocation(Location $location) {
        $this->location = $location;
    }

    public function getHidden(): bool {
        return $this->hidden;
    }

    public function setHidden(bool $hidden) {
        $this->hidden = $hidden;
    }

    public function getCaption() {
        return $this->caption;
    }

    public function setCaption(string $caption) {
        $this->caption = $caption;
    }

    /** @return Collection|IODeviceChannel[] */
    public function getChannels(): Collection {
        return $this->channels;
    }

    public function getFunction(): ChannelFunction {
        return ChannelFunction::safeInstance($this->function);
    }

    public function getAltIcon(): int {
        return intval($this->altIcon);
    }

    public function setAltIcon($altIcon) {
        $this->altIcon = intval($altIcon);
    }

    public function getUserIcon(): ?UserIcon {
        return $this->userIcon;
    }

    /** @param UserIcon|null $userIcon */
    public function setUserIcon($userIcon) {
        $this->userIcon = $userIcon;
    }

    /** @param IODeviceChannel[] $channels */
    public function setChannels($channels) {
        Assertion::notEmpty($channels);
        Assertion::allIsInstanceOf($channels, IODeviceChannel::class);
        if (!$this->function) {
            $this->function = $channels[0]->getFunction()->getId();
        }
        Assertion::allSatisfy($channels, function (IODeviceChannel $channel) {
            return $channel->getFunction()->getId() === $this->function;
        }, 'All channels of this group must have function equal to ' . $this->function);
        $this->channels->clear();
        foreach ($channels as $channel) {
            $this->channels->add($channel);
        }
    }

    public function buildServerActionCommand(string $command, array $actionParams = []): string {
        $params = array_merge([$this->getUser()->getId(), $this->getId()], $actionParams);
        $params = implode(',', $params);
        $command = preg_replace('#^(ACTION-|SET-)#', '$1CG-', $command);
        return "$command:$params";
    }

    public function getDirectLinks(): Collection {
        return $this->directLinks;
    }

    public function getSchedules(): Collection {
        return $this->schedules;
    }

    /** @return Collection|SceneOperation[] */
    public function getSceneOperations(): Collection {
        return $this->sceneOperations;
    }

    /** @return Collection|ValueBasedTrigger[] */
    public function getReactions(): Collection {
        return $this->reactions;
    }
}
