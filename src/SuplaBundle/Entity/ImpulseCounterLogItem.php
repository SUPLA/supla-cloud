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

/**
 * @ORM\Entity
 * @ORM\Table(name="supla_ic_log",
 *     indexes={@ORM\Index(name="channel_id_idx", columns={"channel_id"}), @ORM\Index(name="date_idx", columns={"date"})})
 */
class ImpulseCounterLogItem {
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="channel_id", type="integer")
     */
    private $channelId;

    /**
     * @ORM\Column(name="date", type="utcdatetime")
     */
    private $date;

    /**
     * @ORM\Column(name="counter", type="bigint", nullable=false)
     */
    private $counter;
    
    /**
     * @ORM\Column(name="calculated_value", type="bigint", nullable=false)
     */
    private $calculatedValue;

    public function __construct() {
    }

    public function getId(): int {
        return $this->id;
    }

    public function getChannelId(): int {
        return $this->channelId;
    }

    /** @return \DateTime */
    public function getDate() {
        return $this->date;
    }

    public function getCounter(): int {
        return $this->counter;
    }

    public function getCalculatedValue(): int {
        return $this->calculatedValue;
    }
}
