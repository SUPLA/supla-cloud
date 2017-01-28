<?php
/*
 src/AppBundle/Entity/Schedule.php

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

namespace AppBundle\Entity;


use Cron\CronExpression;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="supla_scheduled_executions")
 */
class ScheduledExecution
{
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
     * @ORM\Column(name="timestamp", type="utcdatetime", nullable=true)
     */
    protected $timestamp;

    /**
     * @ORM\Column(name="executed", type="boolean", nullable=false, options={"default"=false})
     */
    protected $executed = false;

    /**
     * @ORM\Column(name="attempts", type="integer", nullable=false, options={"default"=0})
     */
    protected $attempts = 0;

    public function __construct(Schedule $schedule, \DateTime $timestamp)
    {
        $this->schedule = $schedule;
        $this->timestamp = $timestamp;
    }

    /**
     * @return mixed
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }
}
