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

use Doctrine\ORM\Mapping as ORM;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\ActiveHours;
use SuplaBundle\Entity\BelongsToUser;
use SuplaBundle\Entity\HasActivityConditions;
use SuplaBundle\Entity\HasSubject;
use SuplaBundle\Entity\HasSubjectTrait;
use SuplaBundle\Entity\Main\Listeners\ValueBasedTriggerEntityListener;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Utils\JsonArrayObject;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @ORM\Entity
 * @ORM\EntityListeners({ValueBasedTriggerEntityListener::class})
 * @ORM\Table(name="supla_value_based_trigger")
 */
class ValueBasedTrigger implements HasSubject, HasActivityConditions {
    use HasSubjectTrait;
    use BelongsToUser;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"basic"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="pushNotifications")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="IODeviceChannel", inversedBy="ownReactions")
     * @ORM\JoinColumn(name="owning_channel_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @Groups({"reaction.owningChannel"})
     */
    private $owningChannel;

    /**
     * @ORM\ManyToOne(targetEntity="IODeviceChannel", inversedBy="reactions")
     * @ORM\JoinColumn(name="channel_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $channel;

    /**
     * @ORM\ManyToOne(targetEntity="IODeviceChannelGroup", inversedBy="reactions")
     * @ORM\JoinColumn(name="channel_group_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $channelGroup;

    /**
     * @ORM\ManyToOne(targetEntity="Scene", inversedBy="reactions")
     * @ORM\JoinColumn(name="scene_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $scene;

    /**
     * @ORM\ManyToOne(targetEntity="Schedule", inversedBy="reactions")
     * @ORM\JoinColumn(name="schedule_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $schedule;

    /**
     * @ORM\ManyToOne(targetEntity="PushNotification", inversedBy="reactions", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="push_notification_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $pushNotification;

    /**
     * @ORM\Column(name="`trigger`", type="string", length=2048, nullable=true, options={"charset"="utf8mb4", "collation"="utf8mb4_unicode_ci"})
     * @Groups({"basic"})
     */
    private $trigger;

    /**
     * @ORM\Column(name="action", type="integer", nullable=false)
     * @Groups({"basic"})
     */
    private $action;

    /**
     * @ORM\Column(name="action_param", type="string", nullable=true, length=255, options={"charset"="utf8mb4", "collation"="utf8mb4_unicode_ci"})
     * @Groups({"basic"})
     */
    private $actionParam;

    /**
     * @ORM\Column(name="enabled", type="boolean", nullable=false, options={"default": 1})
     * @Groups({"basic"})
     */
    protected $enabled = true;

    /**
     * @ORM\Column(name="active_from", type="utcdatetime", nullable=true)
     * @Groups({"basic"})
     */
    private $activeFrom;

    /**
     * @ORM\Column(name="active_to", type="utcdatetime", nullable=true)
     * @Groups({"basic"})
     */
    private $activeTo;

    /**
     * @ORM\Column(name="active_hours", type="string", length=768, nullable=true, options={"charset"="utf8mb4", "collation"="utf8mb4_unicode_ci"})
     * @Groups({"basic"})
     */
    private $activeHours;

    /**
     * @ORM\Column(name="activity_conditions", type="string", length=1024, nullable=true, options={"charset"="utf8mb4", "collation"="utf8mb4_unicode_ci"})
     * @Groups({"basic"})
     */
    private $activityConditions;

    public function __construct(IODeviceChannel $channel) {
        $this->user = $channel->getUser();
        $this->owningChannel = $channel;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getUser(): User {
        return $this->user;
    }

    public function getOwningChannel(): IODeviceChannel {
        return $this->owningChannel;
    }

    /**
     * @Groups({"reaction.subject"})
     * @MaxDepth(1)
     */
    public function getSubject(): ?ActionableSubject {
        return $this->getTheSubject();
    }

    public function setSubject(ActionableSubject $subject) {
        $this->initializeSubject($subject);
    }

    public function getTrigger(): array {
        $trigger = $this->trigger ? json_decode($this->trigger, true) : [];
        if (isset($trigger['on_change']) && !$trigger['on_change']) {
            $trigger['on_change'] = new JsonArrayObject([]);
        }
        return $trigger;
    }

    public function setTrigger(array $trigger): void {
        $this->trigger = json_encode($trigger, JSON_UNESCAPED_UNICODE);
    }

    public function getAction(): ChannelFunctionAction {
        return new ChannelFunctionAction($this->action);
    }

    public function setAction(ChannelFunctionAction $action): void {
        $this->action = $action->getId();
    }

    public function getActionParam(): ?array {
        return $this->actionParam ? json_decode($this->actionParam, true) : $this->actionParam;
    }

    public function setActionParam(?array $actionParam): void {
        if ($actionParam) {
            $this->actionParam = json_encode($actionParam, JSON_UNESCAPED_UNICODE);
        } else {
            $this->actionParam = null;
        }
    }

    public function isEnabled(): bool {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void {
        $this->enabled = $enabled;
    }

    public function getActiveFrom(): ?\DateTime {
        return $this->activeFrom;
    }

    public function setActiveFrom(?\DateTime $activeFrom): void {
        $this->activeFrom = $activeFrom;
    }

    public function getActiveTo(): ?\DateTime {
        return $this->activeTo;
    }

    public function setActiveTo(?\DateTime $activeTo): void {
        $this->activeTo = $activeTo;
    }

    public function getActiveHours(): ?array {
        return ActiveHours::fromString($this->activeHours)->toArray();
    }

    public function setActiveHours(?array $activeHours): void {
        $this->activeHours = ActiveHours::fromArray($activeHours)->toString();
    }

    public function getActivityConditions(): array {
        return $this->activityConditions ? json_decode($this->activityConditions, true) : [];
    }

    public function setActivityConditions(?array $activityConditions): void {
        $this->activityConditions = $activityConditions ? json_encode($activityConditions) : null;
    }
}
