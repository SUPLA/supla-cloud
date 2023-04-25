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
 * @ORM\Table(name="supla_push_notification")
 */
class PushNotification {
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
     *     joinColumns={ @ORM\JoinColumn(name="push_notification_id", referencedColumnName="id") },
     *     inverseJoinColumns={ @ORM\JoinColumn(name="access_id", referencedColumnName="id") }
     * )
     */
    private $accessId;

    /** @ORM\Column(name="trigger", type="string", length=2048, nullable=true) */
    private $trigger;

    /** @ORM\Column(name="managed_by_device", type="boolean", options={"default": 0}) */
    private $managedByDevice;

    /** @ORM\Column(name="title", type="string", length=100, nullable=true) */
    private $title;

    /** @ORM\Column(name="body", type="string", length=255, nullable=true) */
    private $body;

    /** @ORM\Column(name="sound", type="integer", nullable=true) */
    private $sound;
}
