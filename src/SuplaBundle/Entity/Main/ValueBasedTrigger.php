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
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="supla_value_based_trigger")
 */
class ValueBasedTrigger {
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
     * @ORM\ManyToOne(targetEntity="IODevice", inversedBy="channels")
     * @ORM\JoinColumn(name="owning_iodevice_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $owningIoDevice;

    /**
     * @ORM\ManyToOne(targetEntity="IODeviceChannel", inversedBy="pushNotifications")
     * @ORM\JoinColumn(name="owning_channel_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $owningChannel;

    /**
     * @ORM\ManyToOne(targetEntity="IODeviceChannel", inversedBy="sceneOperations")
     * @ORM\JoinColumn(name="channel_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $channel;

    /**
     * @ORM\ManyToOne(targetEntity="IODeviceChannelGroup", inversedBy="sceneOperations")
     * @ORM\JoinColumn(name="channel_group_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $channelGroup;

    /**
     * @ORM\ManyToOne(targetEntity="Scene", inversedBy="sceneOperations")
     * @ORM\JoinColumn(name="scene_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $scene;

    /**
     * @ORM\ManyToOne(targetEntity="Schedule", inversedBy="sceneOperations")
     * @ORM\JoinColumn(name="schedule_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $schedule;

    /**
     * @ORM\ManyToOne(targetEntity="PushNotification", inversedBy="sceneOperations")
     * @ORM\JoinColumn(name="push_notification_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $pushNotification;

    /** @ORM\Column(name="trigger", type="string", length=2048, nullable=true) */
    private $trigger;

    /**
     * @ORM\Column(name="action", type="integer", nullable=false)
     */
    private $action;

    /**
     * @ORM\Column(name="action_param", type="string", nullable=true, length=255)
     * @Groups({"basic"})
     */
    private $actionParam;
}
