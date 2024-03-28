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
use Doctrine\ORM\Mapping as ORM;
use SuplaBundle\Entity\BelongsToUser;
use SuplaBundle\Enums\ChannelFunction;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="SuplaBundle\Repository\UserIconRepository")
 * @ORM\Table(name="supla_user_icons")
 */
class UserIcon {
    use BelongsToUser;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"basic"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userIcons")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="IODeviceChannel", mappedBy="userIcon")
     */
    private $channels;

    /**
     * @ORM\OneToMany(targetEntity="IODeviceChannelGroup", mappedBy="userIcon")
     */
    private $channelGroups;

    /**
     * @ORM\OneToMany(targetEntity="Scene", mappedBy="userIcon")
     */
    private $scenes;

    /**
     * @ORM\Column(name="func", type="integer", nullable=false)
     * @Groups({"basic"})
     */
    private $function;

    /**
     * @ORM\Column(name="image1", type="blob", nullable=false)
     */
    private $image1;

    /**
     * @ORM\Column(name="image2", type="blob", nullable=true)
     */
    private $image2;

    /**
     * @ORM\Column(name="image3", type="blob", nullable=true)
     */
    private $image3;

    /**
     * @ORM\Column(name="image4", type="blob", nullable=true)
     */
    private $image4;

    /**
     * @ORM\Column(name="image_dark1", type="blob", nullable=true)
     */
    private $imageDark1;

    /**
     * @ORM\Column(name="image_dark2", type="blob", nullable=true)
     */
    private $imageDark2;

    /**
     * @ORM\Column(name="image_dark3", type="blob", nullable=true)
     */
    private $imageDark3;

    /**
     * @ORM\Column(name="image_dark4", type="blob", nullable=true)
     */
    private $imageDark4;

    private $fetchedImages;
    private $fetchedDarkImages;

    public function __construct(User $user, ChannelFunction $function) {
        $this->user = $user;
        $this->function = $function->getValue();
    }

    public function getId(): int {
        return $this->id;
    }

    public function getUser(): User {
        return $this->user;
    }

    public function getFunction(): ChannelFunction {
        return ChannelFunction::safeInstance($this->function);
    }

    /** @return IODeviceChannel[] */
    public function getChannels() {
        return $this->channels;
    }

    /** @return IODeviceChannelGroup[] */
    public function getChannelGroups() {
        return $this->channels;
    }

    /** @return Scene[] */
    public function getScenes() {
        return $this->scenes;
    }

    public function getImages(): array {
        if (!$this->fetchedImages) {
            $this->fetchedImages = [];
            for ($i = 1; $i <= 4; $i++) {
                $imageField = 'image' . $i;
                if ($this->{$imageField}) {
                    if (is_resource($this->{$imageField})) {
                        $this->fetchedImages[] = stream_get_contents($this->{$imageField});
                    } else {
                        $this->fetchedImages[] = $this->{$imageField};
                    }
                }
            }
        }
        return $this->fetchedImages;
    }

    public function getImagesDark(): array {
        if (!$this->fetchedDarkImages) {
            $this->fetchedDarkImages = $this->getImages();
            for ($i = 1; $i <= 4; $i++) {
                $imageField = 'imageDark' . $i;
                if ($this->{$imageField}) {
                    if (is_resource($this->{$imageField})) {
                        $this->fetchedDarkImages[$i - 1] = stream_get_contents($this->{$imageField});
                    } else {
                        $this->fetchedDarkImages[$i - 1] = $this->{$imageField};
                    }
                }
            }
        }
        return $this->fetchedDarkImages;
    }

    public function setImage($imageString, int $index): void {
        Assertion::between($index, 1, 4);
        $imageField = 'image' . $index;
        $this->{$imageField} = $imageString;
    }

    public function setImageDark($imageString, int $index): void {
        Assertion::between($index, 1, 4);
        $imageField = 'imageDark' . $index;
        $this->{$imageField} = $imageString;
    }
}
