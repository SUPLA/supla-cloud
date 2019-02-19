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
 * @ORM\Table(name="supla_thermostat_log",
 *     indexes={@ORM\Index(name="channel_id_idx", columns={"channel_id"}), @ORM\Index(name="date_idx", columns={"date"})})
 */
class ThermostatLogItem {
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="channel_id", type="integer")
     * @Groups({"basic"})
     */
    private $channel_id;

    /**
     * @ORM\Column(name="date", type="utcdatetime")
     * @Groups({"basic"})
     */
    private $date;

    /**
     * @ORM\Column(name="on", type="boolean", nullable=false)
     * @Groups({"basic"})
     */
    private $on = false;

    /**
     * @ORM\Column(name="temperature1", type="decimal", precision=8, scale=4, nullable=true)
     * @Groups({"basic"})
     */
    private $temperature1;

    /**
     * @ORM\Column(name="temperature2", type="decimal", precision=8, scale=4, nullable=true)
     * @Groups({"basic"})
     */
    private $temperature2;

    /**
     * @ORM\Column(name="temperature3", type="decimal", precision=8, scale=4, nullable=true)
     * @Groups({"basic"})
     */
    private $temperature3;

    /**
     * @ORM\Column(name="temperature4", type="decimal", precision=8, scale=4, nullable=true)
     * @Groups({"basic"})
     */
    private $temperature4;

    /**
     * @ORM\Column(name="temperature5", type="decimal", precision=8, scale=4, nullable=true)
     * @Groups({"basic"})
     */
    private $temperature5;

    /**
     * @ORM\Column(name="temperature6", type="decimal", precision=8, scale=4, nullable=true)
     * @Groups({"basic"})
     */
    private $temperature6;

    /**
     * @ORM\Column(name="temperature7", type="decimal", precision=8, scale=4, nullable=true)
     * @Groups({"basic"})
     */
    private $temperature7;

    /**
     * @ORM\Column(name="temperature8", type="decimal", precision=8, scale=4, nullable=true)
     * @Groups({"basic"})
     */
    private $temperature8;

    /**
     * @ORM\Column(name="temperature9", type="decimal", precision=8, scale=4, nullable=true)
     * @Groups({"basic"})
     */
    private $temperature9;

    /**
     * @ORM\Column(name="temperature10", type="decimal", precision=8, scale=4, nullable=true)
     * @Groups({"basic"})
     */
    private $temperature10;


    public function __construct() {
    }

    public function getId() {
        return $this->id;
    }

    public function getChannelId() {
        return $this->channel_id;
    }

    public function getDate() {
        return $this->date;
    }

    public function getOn() {
        return $this->on;
    }

    public function getTemperature1() {
        return $this->temperature1;
    }

    public function getTemperature2() {
        return $this->temperature2;
    }

    public function getTemperature3() {
        return $this->temperature3;
    }

    public function getTemperature4() {
        return $this->temperature4;
    }

    public function getTemperature5() {
        return $this->temperature5;
    }

    public function getTemperature6() {
        return $this->temperature6;
    }

    public function getTemperature7() {
        return $this->temperature7;
    }

    public function getTemperature8() {
        return $this->temperature8;
    }

    public function getTemperature9() {
        return $this->temperature9;
    }

    public function getTemperature10() {
        return $this->temperature10;
    }
}
