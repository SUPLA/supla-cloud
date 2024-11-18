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
use SuplaBundle\Entity\BelongsToUser;

/**
 * @ORM\Entity
 * @ORM\Table(name="supla_dev_channel_state")
 */
class ChannelState {
    use BelongsToUser;

    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="IODeviceChannel", mappedBy="lastKnownChannelState")
     * @ORM\JoinColumn(name="channel_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $channel;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\Column(name="update_time", type="utcdatetime", nullable=true)
     */
    private $updateTime;

    /**
     * @ORM\Column(name="state", type="text", length=1024, nullable=true, options={"charset"="utf8mb4", "collation"="utf8mb4_unicode_ci"})
     */
    private $state;

    public function __construct(IODeviceChannel $channel) {
        $this->channel = $channel;
        $this->user = $channel->getUser();
    }

    public function getId(): int {
        return $this->channel->getId();
    }

    public function getUser(): User {
        return $this->user;
    }

    public function getState(): array {
        return $this->state ? (json_decode($this->state, true) ?: []) : [];
    }
}
