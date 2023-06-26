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
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\BelongsToUser;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\HasIcon;
use SuplaBundle\Entity\HasLocation;
use SuplaBundle\Entity\HasRelationsCount;
use SuplaBundle\Entity\HasRelationsCountTrait;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Entity\HasUserConfigTrait;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Supla\SuplaServer;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @ORM\Entity(repositoryClass="SuplaBundle\Repository\SceneRepository")
 * @ORM\Table(name="supla_scene")
 */
class Scene implements HasLocation, ActionableSubject, HasRelationsCount, HasUserConfig, HasIcon {
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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="scenes")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Location", inversedBy="scenes")
     * @ORM\JoinColumn(name="location_id", referencedColumnName="id", nullable=false)
     * @Groups({"scene.location"})
     * @MaxDepth(1)
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
     * @ORM\Column(name="hidden", type="boolean", nullable=false, options={"default"=0})
     * @Groups({"basic"})
     */
    private $hidden = false;

    /** @ORM\Column(name="user_config", type="string", length=2048, nullable=true) */
    private $userConfig;

    /**
     * @ORM\Column(name="estimated_execution_time", type="integer", nullable=false, options={"default": 0})
     * @Groups({"basic"})
     */
    private $estimatedExecutionTime = 0;

    /**
     * @ORM\Column(name="alt_icon", type="tinyint", nullable=false, options={"unsigned"=true, "default": 0})
     * @Groups({"basic"})
     */
    private $altIcon = 0;

    /**
     * @ORM\ManyToOne(targetEntity="UserIcon", inversedBy="scenes")
     * @ORM\JoinColumn(name="user_icon_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $userIcon;

    /**
     * @ORM\OneToMany(targetEntity="SceneOperation", mappedBy="owningScene", cascade={"persist"})
     * @Groups({"scene.operations"})
     * @MaxDepth(1)
     * @var Collection|SceneOperation[]
     */
    private $operations;

    /**
     * @var SceneOperation[]
     * @ORM\OneToMany(targetEntity="SceneOperation", mappedBy="scene", cascade={"remove"})
     * @MaxDepth(1)
     */
    private $sceneOperations;

    /**
     * @var Schedule[]
     * @ORM\OneToMany(targetEntity="Schedule", mappedBy="scene", cascade={"remove"})
     */
    private $schedules;

    /**
     * @ORM\OneToMany(targetEntity="DirectLink", mappedBy="scene")
     */
    private $directLinks;

    /**
     * @var ValueBasedTrigger[]
     * @ORM\OneToMany(targetEntity="ValueBasedTrigger", mappedBy="scene", cascade={"remove"})
     * @MaxDepth(1)
     */
    private $reactions;

    private $commandExecutionsCount = 0;

    public function __construct(Location $location) {
        $this->user = $location->getUser();
        $this->location = $location;
        $this->operations = new ArrayCollection();
        $this->sceneOperations = new ArrayCollection();
    }

    public function getId(): int {
        return $this->id;
    }

    public function isNew(): bool {
        return !$this->id;
    }

    /** @Groups({"basic"}) */
    public function getOwnSubjectType(): string {
        return ActionableSubjectType::SCENE;
    }

    /**
     * @Groups({"basic"})
     * @return ChannelFunctionAction[]
     */
    public function getPossibleActions(): array {
        return $this->getFunction()->getDefaultPossibleActions();
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

    public function setEnabled(bool $enabled): void {
        $this->enabled = $enabled;
    }

    public function isHidden(): bool {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): void {
        $this->hidden = $hidden;
    }

    public function getEstimatedExecutionTime(): int {
        return $this->estimatedExecutionTime;
    }

    public function setEstimatedExecutionTime(int $estimatedExecutionTime): void {
        $this->estimatedExecutionTime = $estimatedExecutionTime;
    }

    /** @return SceneOperation[]|ArrayCollection */
    public function getOperations() {
        return $this->operations;
    }

    public function setOpeartions($operations) {
        $this->commandExecutionsCount = 0;
        $this->operations->clear();
        foreach ($operations as $operation) {
            EntityUtils::setField($operation, 'owningScene', $this);
            $this->operations->add($operation);
        }
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

    public function setUserIcon(?UserIcon $userIcon) {
        $this->userIcon = $userIcon;
    }

    public function getDirectLinks(): Collection {
        return $this->directLinks;
    }

    public function getSchedules(): Collection {
        return $this->schedules;
    }

    public function getReactions(): Collection {
        return $this->reactions;
    }

    /** @return SceneOperation[] */
    public function getOperationsThatReferToThisScene(): Collection {
        return $this->sceneOperations;
    }

    /** @Groups({"basic"}) */
    public function getFunction(): ChannelFunction {
        return ChannelFunction::SCENE();
    }

    public function buildServerActionCommand(string $command, array $actionParams = []): string {
        $params = array_merge([$this->getUser()->getId(), $this->getId()], $actionParams);
        $params = implode(',', $params);
        return "$command:$params";
    }

    public function removeOperation(SceneOperation $sceneOperation, EntityManagerInterface $entityManager, SuplaServer $suplaServer) {
        $this->getOperations()->removeElement($sceneOperation);
        $entityManager->remove($sceneOperation);
        if ($this->getOperations()->isEmpty()) {
            $entityManager->remove($this);
            $suplaServer->userAction('ON-SCENE-REMOVED', $this->getId(), $this->getUser());
        } else {
            $entityManager->persist($this);
            $suplaServer->userAction('ON-SCENE-CHANGED', $this->getId(), $this->getUser());
        }
    }

    public function getProperties(): array {
        return [];
    }

    public function getCommandExecutionsCount(): int {
        return $this->commandExecutionsCount;
    }

    public function setCommandExecutionsCount(int $commandExecutionsCount): void {
        $this->commandExecutionsCount = $commandExecutionsCount;
    }
}
