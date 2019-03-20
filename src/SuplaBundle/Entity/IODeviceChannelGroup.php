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
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use SuplaBundle\Enums\ChannelFunction;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="SuplaBundle\Repository\ChannelGroupRepository")
 * @ORM\Table(name="supla_dev_channel_group")
 */
class IODeviceChannelGroup implements HasFunction, HasLocation {
    use BelongsToUser;

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
     * @Groups({"location"})
     */
    private $location;

    /**
     * @ORM\ManyToMany(targetEntity="IODeviceChannel", inversedBy="channels", cascade={"persist"})
     * @ORM\JoinTable(name="supla_rel_cg", joinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")},
     * inverseJoinColumns={@ORM\JoinColumn(name="channel_id", referencedColumnName="id")} )
     * @Groups({"channels"})
     * @var Collection|IODeviceChannel[]
     */
    private $channels;

    /**
     * @ORM\Column(name="hidden", type="boolean", nullable=false, options={"default"=0})
     * @Groups({"basic"})
     */
    private $hidden = false;

    /**
     * @ORM\Column(name="caption", type="string", length=255, nullable=true)
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

    /** @param IODeviceChannel[] $channels */
    public function __construct(User $user = null, Location $location = null, array $channels = []) {
        $this->channels = new ArrayCollection();
        if (count($channels)) {
            Assertion::notNull($user);
            Assertion::notNull($location);
            $this->user = $user;
            $this->location = $location;
            $this->setChannels($channels);
            $this->altIcon = 0;
        }
    }

    public function getId(): int {
        return $this->id;
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

    /** @return UserIcon|null */
    public function getUserIcon() {
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

    public function removeChannel(IODeviceChannel $channel, EntityManagerInterface $entityManager) {
        $this->getChannels()->removeElement($channel);
        if ($this->getChannels()->isEmpty()) {
            $entityManager->remove($this);
        } else {
            $entityManager->persist($this);
        }
    }

    public function buildServerSetCommand(string $type, array $actionParams): string {
        $params = array_merge([$this->getUser()->getId(), $this->getId()], $actionParams);
        $params = implode(',', $params);
        return "SET-CG-$type-VALUE:$params";
    }

    public function getDirectLinks(): Collection {
        return $this->directLinks;
    }

    public function getSchedules(): Collection {
        return $this->schedules;
    }
}
