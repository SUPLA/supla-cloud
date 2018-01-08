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
use SuplaBundle\Enums\ChannelFunction;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints;

/**
 * @ORM\Entity
 * @ORM\Table(name="supla_dev_channel_group", indexes={
 *     @ORM\Index(name="enabled_idx", columns={"enabled"})
 * })
 */
class IODeviceChannelGroup {
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"basic"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="channelGroups")
     * @Constraints\NotNull
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Location", inversedBy="ioDeviceChannelGroups")
     * @ORM\JoinColumn(name="location_id", referencedColumnName="id", nullable=false)
     * @Groups({"location"})
     */
    private $location;

    /**
     * @ORM\ManyToMany(targetEntity="IODeviceChannel", inversedBy="channelGroups", cascade={"persist"})
     * @ORM\JoinTable(name="supla_rel_cg", joinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")},
     * inverseJoinColumns={@ORM\JoinColumn(name="channel_id", referencedColumnName="id")} )
     * @Groups({"channels"})
     * @var Collection|IODeviceChannel[]
     */
    private $channels;

    /**
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     * @Groups({"basic"})
     */
    private $enabled = true;

    /**
     * @ORM\Column(name="hidden", type="boolean", nullable=false, options={"default"=0})
     * @Groups({"basic"})
     */
    private $hidden = false;

    /**
     * @ORM\Column(name="caption", type="string", length=255, nullable=true)
     * @Constraints\Length(max=255)
     * @Groups({"basic"})
     */
    private $caption;

    /**
     * @ORM\Column(name="func", type="integer", nullable=false)
     * @Groups({"function"})
     */
    private $function;

    /** @param IODeviceChannel[] $channels */
    public function __construct(User $user = null, Location $location = null, array $channels = []) {
        $this->channels = new ArrayCollection();
        if (count($channels)) {
            Assertion::notNull($user);
            Assertion::notNull($location);
            $this->user = $user;
            $this->location = $location;
            $this->setChannels($channels);
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

    public function getEnabled(): bool {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled) {
        $this->enabled = $enabled;
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
    public function getChannels() {
        return $this->channels;
    }

    public function getFunction(): ChannelFunction {
        return new ChannelFunction($this->function);
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
}
