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
        $this->scheduledExecutionsRepository = $doctrine->getRepository('AppBundle:ScheduledExecution');
    }

    public function generateScheduledExecutions(Schedule $schedule, $until = '+5days')
    {
        $nextRunDates = $this->getNextRunDates($schedule, $until);
        foreach ($nextRunDates as $nextRunDate) {
            $this->entityManager->persist(new ScheduledExecution($schedule, $nextRunDate));
        }
        $schedule->setNextCalculationDate(end($nextRunDates));
        $this->entityManager->persist($schedule);
        $this->entityManager->flush();
    }

    public function getNextRunDates(Schedule $schedule, $until = '+5days', $count = PHP_INT_MAX)
    {
        $userTimezone = new \DateTimeZone($schedule->getUser()->getTimezone());
        if ($schedule->getDateEnd()) {
            $schedule->getDateEnd()->setTimezone($userTimezone);
            $until = min($schedule->getDateEnd()->getTimestamp(), strtotime($until));
        }
        if ($schedule->getDateStart()->getTimestamp() < time()) {
            $schedule->getDateStart()->setTimestamp(time());
        }
        $schedule->getDateStart()->setTimezone($userTimezone);
        return $schedule->getRunDatesUntil($until, $schedule->getDateStart(), $count);
    }

    public function findClosestExecutions(Schedule $schedule, $contextSize = 3)
    {
        $criteria = new \Doctrine\Common\Collections\Criteria();
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $criteria
            ->where($criteria->expr()->gte('timestamp', $now))
            ->andWhere($criteria->expr()->eq('schedule', $schedule))
            ->orderBy(['timestamp' => 'ASC'])
            ->setMaxResults($contextSize + 1);
        $inFuture = $this->scheduledExecutionsRepository->matching($criteria)->toArray();
        $criteria = new \Doctrine\Common\Collections\Criteria();
        $criteria
            ->where($criteria->expr()->lt('timestamp', $now))
            ->andWhere($criteria->expr()->eq('schedule', $schedule))
            ->orderBy(['timestamp' => 'DESC'])
            ->setMaxResults($contextSize);
        $inPast = $this->scheduledExecutionsRepository->matching($criteria)->toArray();
        return [
            'past' => array_reverse($inPast),
            'future' => $inFuture
        ];
    }

    public function disable(Schedule $schedule)
    {
        $schedule->setEnabled(false);
        $this->deleteScheduledExecutions($schedule);
        $schedule->setNextCalculationDate(new \DateTime());
        $this->entityManager->persist($schedule);
        $this->entityManager->flush();
    }

    private function deleteScheduledExecutions(Schedule $schedule)
    {
        $this->entityManager->createQueryBuilder()
            ->delete('AppBundle:ScheduledExecution', 's')
            ->where('s.schedule = :schedule')
            ->setParameter('schedule', $schedule)
            ->getQuery()
            ->execute();
    }

    public function enable(Schedule $schedule)
    {
        $schedule->setEnabled(true);
        $this->generateScheduledExecutions($schedule);
    }

    public function delete(Schedule $schedule)
    {
        $this->deleteScheduledExecutions($schedule);
        $this->entityManager->remove($schedule);
        $this->entityManager->flush();
    }
}
