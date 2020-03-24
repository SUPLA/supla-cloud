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
use SuplaBundle\EventListener\ApiRateLimit\ApiRateLimitStatus;

/**
 * @ORM\Entity()
 * @ORM\Table(name="supla_api_rate_limit_excess")
 */
class ApiRateLimitExcess {
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="rule_name", type="string", length=50)
     */
    private $ruleName;

    /**
     * @ORM\Column(name="excess", type="smallint", options={"unsigned"=true})
     */
    private $excess;

    /**
     * @ORM\Column(name="reset_time", type="utcdatetime")
     */
    private $resetTime;

    public function __construct(string $ruleName, ApiRateLimitStatus $status) {
        $this->ruleName = $ruleName;
        $this->excess = $status->getExcess();
        $this->resetTime = new \DateTime('@' . $status->getReset());
    }
}
