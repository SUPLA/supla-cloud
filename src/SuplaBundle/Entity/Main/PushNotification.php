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
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Repository\AccessIdRepository;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @ORM\Entity(repositoryClass="SuplaBundle\Repository\PushNotificationRepository")
 * @ORM\Table(name="supla_push_notification")
 */
class PushNotification implements ActionableSubject {
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
     * @ORM\ManyToOne(targetEntity="IODeviceChannel", inversedBy="pushNotifications")
     * @ORM\JoinColumn(name="channel_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $channel;

    /**
     * @ORM\ManyToOne(targetEntity="IODevice", inversedBy="pushNotifications")
     * @ORM\JoinColumn(name="iodevice_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $device;

    /**
     * @ORM\ManyToMany(targetEntity="AccessID", inversedBy="pushNotifications", cascade={"persist"})
     * @ORM\JoinTable(name="supla_rel_aid_pushnotification",
     *     joinColumns={ @ORM\JoinColumn(name="push_notification_id", referencedColumnName="id", onDelete="CASCADE") },
     *     inverseJoinColumns={ @ORM\JoinColumn(name="access_id", referencedColumnName="id", onDelete="CASCADE") }
     * )
     * @Groups({"notification.accessIds"})
     * @MaxDepth(1)
     */
    private $accessIds;

    /** @ORM\Column(name="managed_by_device", type="boolean", options={"default": 0}) */
    private $managedByDevice = false;

    /**
     * @ORM\Column(name="title", type="string", length=100, nullable=true, options={"charset"="utf8mb4", "collation"="utf8mb4_unicode_ci"})
     * @Groups({"basic"})
     */
    private $title;

    /**
     * @ORM\Column(name="body", type="string", length=255, nullable=true, options={"charset"="utf8mb4", "collation"="utf8mb4_unicode_ci"})
     * @Groups({"basic"})
     */
    private $body;

    /** @ORM\Column(name="sound", type="integer", nullable=true) */
    private $sound;

    public function __construct(User $user) {
        $this->user = $user;
        $this->accessIds = new ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function getUser(): User {
        return $this->user;
    }

    public function getFunction(): ChannelFunction {
        return ChannelFunction::NOTIFICATION();
    }

    /** @Groups({"basic"}) */
    public function getPossibleActions(): array {
        return $this->getFunction()->getDefaultPossibleActions();
    }

    public function buildServerActionCommand(string $command, array $actionParams = []): string {
        $payload = base64_encode(json_encode($actionParams));
        $params = implode(',', [$this->getUser()->getId(), $payload]);
        return "$command:$params";
    }

    /** @Groups({"basic"}) */
    public function getOwnSubjectType(): string {
        return ActionableSubjectType::NOTIFICATION;
    }

    public function getTitle(): ?string {
        return $this->title;
    }

    public function setTitle(string $title): void {
        Assertion::maxLength($title, 100);
        $this->title = $title;
    }

    public function getBody(): ?string {
        return $this->body;
    }

    public function setBody(string $body): void {
        Assertion::maxLength($body, 255);
        $this->body = $body;
    }

    /** @return Collection|AccessID[] */
    public function getAccessIds(): Collection {
        return $this->accessIds;
    }

    /** @param AccessID[]|Collection $locations */
    public function setAccessIds($accessIds): void {
        $this->accessIds = $accessIds;
    }

    public function getChannel(): ?IODeviceChannel {
        return $this->channel;
    }

    public function setChannel(IODeviceChannel $channel): void {
        $this->channel = $channel;
    }

    public function initFromValidatedActionParams(array $actionParam, AccessIdRepository $aidRepository) {
        $this->setTitle($actionParam['title'] ?? '');
        $this->setBody($actionParam['body'] ?? '');
        $accessIds = array_map(function (int $aid) use ($aidRepository) {
            return $aidRepository->findForUser($this->getUser(), $aid);
        }, $actionParam['accessIds']);
        $this->setAccessIds($accessIds);
    }

    public function isManagedByDevice(): bool {
        return $this->managedByDevice;
    }
}
