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


use Cron\CronExpression;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="supla_schedule")
 * @UniqueEntity(fields="id", message="IODevice already exists")
 */
class Schedule
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="schedules")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(name="cron_expression", type="string", length=100, nullable=false)
     * @Assert\Length(max=100)
     */
    private $cronExpression;

    /**
     * @ORM\ManyToOne(targetEntity="IODeviceChannel")
     * @ORM\JoinColumn(name="channel_id", referencedColumnName="id", nullable=false)
     */
    private $channel;

    /**
     * @ORM\Column(name="action", type="integer", nullable=false)
     */
    private $action;

    /**
     * @ORM\Column(name="action_param", type="string", nullable=true, length=255)
     */
    private $actionParam;

    /**
     * @ORM\Column(name="mode", type="string", length=15, nullable=false)
     * @Assert\Choice({"once", "minutely", "hourly", "daily"})
     */
    private $mode;

    /**
     * @ORM\Column(name="date_start", type="utcdatetime", nullable=false)
     * @Assert\NotNull()
     */
    private $dateStart;

    /**
     * @ORM\Column(name="date_end", type="utcdatetime", nullable=true)
     */
    private $dateEnd;

    /**
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     */
    protected $enabled = true;

    /**
     * @ORM\Column(name="next_calculation_date", type="utcdatetime", nullable=true)
     */
    private $nextCalculationDate;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getCronExpression()
    {
        return $this->cronExpression;
    }

    /**
     * @param mixed $cronExpression
     */
    public function setCronExpression($cronExpression)
    {
        $this->cronExpression = $cronExpression;
    }

    /**
     * @return mixed
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param mixed $channel
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return mixed
     */
    public function getActionParam()
    {
        return $this->actionParam;
    }

    /**
     * @param mixed $actionParam
     */
    public function setActionParam($actionParam)
    {
        $this->actionParam = $actionParam;
    }

    /**
     * @return mixed
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param mixed $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * @return mixed
     */
    public function getDateStart()
    {
        return $this->dateStart;
    }

    /**
     * @param mixed $dateStart
     */
    public function setDateStart(\DateTime $dateStart)
    {
        $this->dateStart = $dateStart;
    }

    /**
     * @return mixed
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    /**
     * @param mixed $dateEnd
     */
    public function setDateEnd($dateEnd)
    {
        $this->dateEnd = $dateEnd;
    }

    /**
     * @return mixed
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param mixed $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @return mixed
     */
    public function getNextCalculationDate()
    {
        return $this->nextCalculationDate;
    }

    public function setNextCalculationDate(\DateTime $nextCalculationDate)
    {
        $this->nextCalculationDate = $nextCalculationDate;
    }

    /**
     * @param string $currentDate
     * @return \DateTime
     * @throws \RuntimeException if the next run date could not be calculated
     */
    public function getNextRunDate($currentDate = 'now')
    {
        $cron = CronExpression::factory($this->getCronExpression());
        return $cron->getNextRunDate($currentDate);
    }

    /**
     * @param string $currentDate
     * @param string $until
     * @param int $maxCount
     * @return \DateTime[]
     */
    public function getRunDatesUntil($until = '+5days', $currentDate = 'now', $maxCount = PHP_INT_MAX)
    {
        $until = is_int($until) ? $until : strtotime($until) + 1; // +1 to make it inclusive
        $runDates = [];
        $nextRunDate = $currentDate;
        try {
            do {
                $nextRunDate = $this->getNextRunDate($nextRunDate);
                $runDates[] = $nextRunDate;
            } while ($nextRunDate->getTimestamp() < $until && count($runDates) < $maxCount);
        } catch (\RuntimeException $e) {
            // impossible cron expression
        }
        return $runDates;
    }
}
