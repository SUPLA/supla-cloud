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
 * @ORM\Entity(repositoryClass="SuplaBundle\Repository\ChannelValueRepository")
 * @ORM\Table(name="supla_dev_channel_value")
 */
class ChannelValue {
    use BelongsToUser;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="IODeviceChannel")
     * @ORM\JoinColumn(name="channel_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $channel;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(name="update_time", type="utcdatetime", nullable=true)
     */
    private $updateTime;

    /**
     * @ORM\Column(name="valid_to", type="utcdatetime", nullable=true)
     */
    private $validTo;

    /**
     * @ORM\Column(name="value", type="binary", length=8, nullable=false, unique=false)
     */
    private $value;

    public function getUser(): User {
        return $this->user;
    }

    public function getValue() {
        return stream_get_contents($this->value);
    }

    public function unpackOneValue() {
        $value = unpack('lval', $this->getValue());
        return $value['val'] / 1000;
    }

    public function unpackTwoValues(): array {
        $value = unpack('lone/ltwo', $this->getValue());
        return [$value['one'] / 1000, $value['two'] / 1000];
    }

    public static function packOneValue($value): string {
        return pack('l', round($value * 1000));
    }

    public static function packTwoValues($value1, $value2): string {
        return pack('ll', round($value1 * 1000), round($value2 * 1000));
    }
}
