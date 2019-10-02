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
use SuplaBundle\Enums\ChannelFunction;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="supla_scene")
 */
class Scene implements HasLocation, HasFunction {
    use BelongsToUser;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"basic"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="scenes")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Location", inversedBy="scenes")
     * @ORM\JoinColumn(name="location_id", referencedColumnName="id", nullable=false)
     * @Groups({"location"})
     */
    private $location;

    /**
     * @ORM\Column(name="caption", type="string", length=255, nullable=true)
     * @Groups({"basic"})
     */
    private $caption;

    /**
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     * @Groups({"basic"})
     */
    private $enabled = true;

    /**
     * @ORM\OneToMany(targetEntity="SceneOperation", mappedBy="owningScene", cascade={"persist"})
     * @Groups({"operations"})
     */
    private $operations;

    public function __construct(Location $location) {
        $this->user = $location->getUser();
        $this->location = $location;
        $this->operations = new ArrayCollection();
    }

    public function getId(): int {
        return $this->id;
    }

    public function getCaption(): string {
        return $this->caption ?? '';
    }

    public function setCaption(string $caption) {
        $this->caption = $caption;
    }

    public function getUser(): User {
        return $this->user;
    }

    public function getLocation(): Location {
        return $this->location;
    }

    public function setLocation(Location $location): void {
        $this->location = $location;
    }

    public function isEnabled(): bool {
        return $this->enabled ?? false;
    }

    public function setEnabled(bool $enabled) {
        $this->enabled = $enabled;
    }

    /** @return SceneOperation[]|ArrayCollection */
    public function getOperations() {
        return $this->operations;
    }

    public function setOpeartions($operations) {
        $this->operations->clear();
        foreach ($operations as $operation) {
            EntityUtils::setField($operation, 'owningScene', $this);
            $this->operations->add($operation);
        }
    }

    public function getFunction(): ChannelFunction {
        return ChannelFunction::SCENE();
    }

    /**
     * Returns a footprint of this functionable item for identification in SUPLA Server commands.
     * See SuplaServer#setValue for more details.
     * @return int[]
     */
    public function buildServerSetCommand(string $type, array $actionParams): string {
        $params = array_merge([$this->getUser()->getId(), $this->getId()], $actionParams);
        $params = implode(',', $params);
        return "EXECUTE-SCENE:$params";
    }
}
