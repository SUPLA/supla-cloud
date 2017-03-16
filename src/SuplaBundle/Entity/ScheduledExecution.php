<?php
/*
 src/SuplaBundle/Entity/Schedule.php

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
use SuplaBundle\Enums\ScheduleActionExecutionResult;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="supla_scheduled_executions", indexes={@ORM\Index(name="result_idx", columns={"result"}), @ORM\Index(name="planned_timestamp_idx", columns={"planned_timestamp"}), @ORM\Index(name="retry_timestamp_idx", columns={"retry_timestamp"})})
 */
class ScheduledExecution {
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Schedule")
     * @ORM\JoinColumn(name="schedule_id", referencedColumnName="id", nullable=false)
     */
    private $schedule;

    /**
     * @ORM\Column(name="planned_timestamp", type="utcdatetime", nullable=true)
     */
    private $plannedTimestamp;

    /**
     * @ORM\Column(name="fetched_timestamp", type="utcdatetime", nullable=true)
     */
    private $fetchedTimestamp;

    /**
     * @ORM\Column(name="retry_timestamp", type="utcdatetime", nullable=true)
     */
    private $retryTimestamp;

    /**
     * @ORM\Column(name="retry_count", type="integer", nullable=true)
     */
    private $retryCount;

    /**
     * @ORM\Column(name="result_timestamp", type="utcdatetime", nullable=true)
     */
    private $resultTimestamp;

    /**
     * @ORM\Column(name="result", type="integer", nullable=true)
     */
    private $result;

    public function __construct(Schedule $schedule, \DateTime $plannedTimestamp) {
        $this->schedule = $schedule;
        $this->plannedTimestamp = $plannedTimestamp;
    }

    public function getPlannedTimestamp() {
        return $this->plannedTimestamp;
    }

    public function isFailed() {
        return !$this->getResult()->isSuccessful();
    }

    public function getResult(): ScheduleActionExecutionResult {
        return new ScheduleActionExecutionResult($this->result);
    }
}
