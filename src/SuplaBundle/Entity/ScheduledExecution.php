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
use SuplaBundle\Enums\ScheduleActionExecutionResult;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="supla_scheduled_executions", indexes={
 *     @ORM\Index(name="result_idx", columns={"result"}),
 *     @ORM\Index(name="result_timestamp_idx", columns={"result_timestamp"}),
 *     @ORM\Index(name="planned_timestamp_idx", columns={"planned_timestamp"}),
 *     @ORM\Index(name="retry_timestamp_idx", columns={"retry_timestamp"}),
 *     @ORM\Index(name="fetched_timestamp_idx", columns={"fetched_timestamp"}),
 *     @ORM\Index(name="consumed_idx", columns={"consumed"})
 * })
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
     * @ORM\JoinColumn(name="schedule_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $schedule;

    /**
     * @ORM\Column(name="action", type="integer", nullable=false)
     * @Groups({"basic"})
     */
    private $action;

    /**
     * @ORM\Column(name="action_param", type="string", nullable=true, length=255)
     * @Groups({"basic"})
     */
    private $actionParam;

    /**
     * @ORM\Column(name="planned_timestamp", type="utcdatetime", nullable=true)
     * @Groups({"basic"})
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
     * @Groups({"basic"})
     */
    private $resultTimestamp;

    /**
     * @ORM\Column(name="consumed", type="boolean", nullable=false)
     */
    private $consumed = false;

    /**
     * @ORM\Column(name="result", type="integer", nullable=true)
     * @Groups({"basic"})
     */
    private $result;

    public function __construct(Schedule $schedule, \DateTime $plannedTimestamp) {
        $this->schedule = $schedule;
        $this->plannedTimestamp = $plannedTimestamp;
    }

    public function getPlannedTimestamp(): \DateTime {
        return $this->plannedTimestamp;
    }

    public function getResultTimestamp() {
        return $this->resultTimestamp;
    }

    /**
     * @Groups({"basic"})
     */
    public function isFailed(): bool {
        return !$this->getResult()->isSuccessful();
    }

    public function getResult(): ScheduleActionExecutionResult {
        return new ScheduleActionExecutionResult($this->result);
    }

    public function getSchedule(): Schedule {
        return $this->schedule;
    }
}
