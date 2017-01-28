<?php
/*
 src/AppBundle/Model/AccessIdManager.php

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

namespace AppBundle\Model;

use AppBundle\Entity\Schedule;
use AppBundle\Entity\ScheduledExecution;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class ScheduleManager
{
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var EntityRepository */
    private $scheduledExecutionsRepository;

    public function __construct($doctrine)
    {
        $this->entityManager = $doctrine->getManager();
        $this->scheduledExecutionsRepository = $doctrine->getRepository('AppBundle:IODevice');
    }

    public function generateScheduledExecutions(Schedule $schedule, $until = '+5days')
    {
        $latestPlanned = null;//$this->scheduledExecutionsRepository->findOneBy(['schedule_id' => $schedule->getId()], ['timestamp' => 'DESC']);
        $dateStart = $latestPlanned ? $latestPlanned->getTimestamp() : $schedule->getDateStart();
        $from = new \DateTime($latestPlanned ? $latestPlanned->getTimestamp() : 'now', new \DateTimeZone($schedule->getUser()->getTimezone()));
        $nextRunDates = $schedule->getRunDatesUntil($until, $from);
        foreach ($nextRunDates as $nextRunDate) {
            $this->entityManager->persist(new ScheduledExecution($schedule, $nextRunDate));
        }
        $schedule->setNextCalculationDate(end($nextRunDates));
        $this->entityManager->persist($schedule);
        $this->entityManager->flush();
    }
}
