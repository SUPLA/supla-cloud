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

use Doctrine\ORM\Mapping as ORM;
use SuplaBundle\Enums\ChannelFunctionAction;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @ORM\Entity
 * @ORM\Table(name="supla_scene_operation")
 */
class SceneOperation implements HasSubject {
    use HasSubjectTrait;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"basic"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Scene", inversedBy="operations")
     * @ORM\JoinColumn(name="owning_scene_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @Groups({"scene"})
     * @MaxDepth(1)
     */
    private $owningScene;

    /**
     * @ORM\ManyToOne(targetEntity="IODeviceChannel")
     * @ORM\JoinColumn(name="channel_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $channel;

    /**
     * @ORM\ManyToOne(targetEntity="IODeviceChannelGroup")
     * @ORM\JoinColumn(name="channel_group_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $channelGroup;

    /**
     * @ORM\ManyToOne(targetEntity="Scene")
     * @ORM\JoinColumn(name="scene_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $scene;

    /**
     * @ORM\Column(name="action", type="integer", nullable=false)
     * @Groups({"basic"})
     */
    private $action;

    /**
     * @ORM\Column(name="action_param", type="string", nullable=true, length=255)
     * @Groups({"basic"})
     */
    private $actionParam;

    /**
     * @ORM\Column(name="delay_ms", type="integer", nullable=false, options={"default" : 0})
     * @Groups({"basic"})
     */
    private $delayMs = 0;

    public function __construct(HasFunction $subject, ChannelFunctionAction $action, array $actionParam = [], $delayMs = 0) {
        $this->initializeSubject($subject);
        $this->action = $action->getId();
        $this->setActionParam($actionParam);
        $this->delayMs = $delayMs;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getOwningScene(): Scene {
        return $this->owningScene;
    }

    public function getAction(): ChannelFunctionAction {
        return new ChannelFunctionAction($this->action);
    }

    /**
     * @Groups({"sceneOperation.subject"})
     * @MaxDepth(1)
     */
    public function getSubject(): ?HasFunction {
        return $this->getTheSubject();
    }

    /** @return array|null */
    public function getActionParam() {
        return $this->actionParam ? json_decode($this->actionParam, true) : $this->actionParam;
    }

    /** @param array|null */
    public function setActionParam($actionParam) {
        if ($actionParam) {
            $params = json_encode($actionParam);
        } else {
            $params = null;
        }
        $this->actionParam = $params;
    }

    public function getDelayMs(): int {
        return $this->delayMs ?? 0;
    }
}
