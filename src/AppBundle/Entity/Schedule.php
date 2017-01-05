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
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     * @Assert\Length(max=100)
     */
    private $name;

    /**
     * @ORM\Column(name="cron_expression", type="string", length=100, nullable=false)
     * @Assert\Length(max=100)
     */
    private $cronExpression;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getCronExpression()
    {
        return $this->cronExpression;
    }

    /**
     * @param string $cronExpression
     */
    public function setCronExpression($cronExpression)
    {
        $this->cronExpression = $cronExpression;
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
     * @return \DateTime[]
     */
    public function getRunDatesUntil($until = '+5days', $currentDate = 'now')
    {
        $until = strtotime($until);
        $runDates = [];
        $nextRunDate = $currentDate;
        try {
            do {
                $nextRunDate = $this->getNextRunDate($nextRunDate);
                $runDates[] = $nextRunDate;
            } while ($nextRunDate->getTimestamp() < $until);
        } catch (\RuntimeException $e) {
            // impossible cron expression
        }
        return $runDates;
    }
}