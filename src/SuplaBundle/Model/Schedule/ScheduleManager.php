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

namespace SuplaBundle\Model\Schedule;

use Assert\Assertion;
use Cocur\Slugify\Slugify;
use DateInterval;
use DateTime;
use DateTimeZone;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\Schedule;
use SuplaBundle\Entity\ScheduledExecution;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Model\IODeviceManager;
use SuplaBundle\Model\Schedule\SchedulePlanners\CompositeSchedulePlanner;
use SuplaBundle\Model\TimeProvider;

class ScheduleManager {
    /** @var Registry */
    private $doctrine;
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var EntityRepository */
    private $scheduledExecutionsRepository;
    /** @var IODeviceManager */
    private $ioDeviceManager;
    /** @var CompositeSchedulePlanner */
    private $schedulePlanner;
    /** @var TimeProvider */
    private $timeProvider;

    public function __construct(
        ManagerRegistry $doctrine,
        IODeviceManager $ioDeviceManager,
        CompositeSchedulePlanner $schedulePlanner,
        TimeProvider $timeProvider
    ) {
        $this->doctrine = $doctrine;
        $this->entityManager = $doctrine->getManager();
        $this->scheduledExecutionsRepository = $doctrine->getRepository('SuplaBundle:ScheduledExecution');
        $this->ioDeviceManager = $ioDeviceManager;
        $this->schedulePlanner = $schedulePlanner;
        $this->timeProvider = $timeProvider;
    }

    /** @return IODeviceChannel[] */
    public function getSchedulableChannels(User $user) {
        $schedulableFunctions = $this->getFunctionsThatCanBeScheduled();
        $channels = $this->doctrine->getRepository('SuplaBundle:IODeviceChannel')->findBy(['user' => $user]);
        $schedulableChannels = array_filter($channels, function (IODeviceChannel $channel) use ($schedulableFunctions) {
            return in_array($channel->getFunction()->getId(), $schedulableFunctions);
        });
        return $this->sortByFunctionNameAndCaption($schedulableChannels);
    }

    /** @return IODeviceChannel[] */
    private function sortByFunctionNameAndCaption(array $schedulableChannels) {
        $slugify = new Slugify();
        $channelsList = [];
        foreach ($schedulableChannels as $channel) {
            $sortKey = $slugify->slugify(implode(' ', [
                $this->ioDeviceManager->channelFunctionToString($channel->getFunction()),
                // Default zzzzz caption places the items without caption at the end. Lame, but works :-D
                $channel->getCaption() ? $channel->getCaption() : 'zzzzzz',
                $channel->getId(),
            ]));
            $channelsList[$sortKey] = $channel;
        }
        ksort($channelsList);
        return array_values($channelsList);
    }

    private function getFunctionsThatCanBeScheduled() {
        return array_keys(ChannelFunction::actions());
    }

    public function generateScheduledExecutions(Schedule $schedule, $until = '+5days') {
        $nextScheduleExecutions = $this->getNextScheduleExecutions($schedule, $until);
        foreach ($nextScheduleExecutions as $scheduledExecution) {
            $this->entityManager->persist($scheduledExecution);
        }
        if (count($nextScheduleExecutions)) {
            /** @var DateTime $nextCalculationDate */
            $nextCalculationDate = clone (end($nextScheduleExecutions)->getPlannedTimestamp());
            $nextCalculationDate->sub(new DateInterval('P2D')); // the oldest scheduled execution minus 2 days
            $schedule->setNextCalculationDate($nextCalculationDate);
        } else {
            $latestExecution = $this->findLatestExecution($schedule);
            if (!$latestExecution || $latestExecution->getPlannedTimestamp()->getTimestamp() < $this->timeProvider->getTimestamp()) {
                $this->disable($schedule);
            }
        }
        $this->entityManager->persist($schedule);
        $this->entityManager->flush();
    }

    public function getNextScheduleExecutions(Schedule $schedule, $until = '+5days', $count = PHP_INT_MAX, $ignoreExisting = false) {
        $userTimezone = $schedule->getUserTimezone();
        if ($schedule->getDateEnd()) {
            $schedule->getDateEnd()->setTimezone($userTimezone);
            $until = min($schedule->getDateEnd()->getTimestamp(), strtotime($until));
        }
        $closestTime = $this->timeProvider->getTimestamp() + 300;
        if ($schedule->getDateStart()->getTimestamp() < $closestTime) {
            $schedule->getDateStart()->setTimestamp($closestTime);
        }
        $dateStart = $schedule->getDateStart();
        $latestExecution = $this->findLatestExecution($schedule);
        if ($latestExecution && !$ignoreExisting) {
            $dateStart = clone $latestExecution->getPlannedTimestamp();
        }
        if ($dateStart->getTimestamp() < $closestTime) {
            $dateStart->setTimestamp($closestTime);
        }
        $dateStart->setTimezone($userTimezone);
        $scheduleExecutions = $this->schedulePlanner->calculateScheduleExecutionsUntil($schedule, $until, $dateStart, $count);
        if ($schedule->getDateEnd()) {
            return array_filter($scheduleExecutions, function (ScheduledExecution $execution) use ($schedule) {
                return $execution->getPlannedTimestamp()->getTimestamp() <= $schedule->getDateEnd()->getTimestamp();
            });
        } else {
            return $scheduleExecutions;
        }
    }

    /** @return ScheduledExecution|null */
    private function findLatestExecution(Schedule $schedule) {
        return current($this->scheduledExecutionsRepository->findBy(['schedule' => $schedule], ['plannedTimestamp' => 'DESC'], 1));
    }

    public function findClosestExecutions(Schedule $schedule, $contextSize = 3): array {
        $criteria = new Criteria();
        $now = $this->getNow();
        $criteria
            ->where($criteria->expr()->gte('plannedTimestamp', $now))
            ->andWhere($criteria->expr()->eq('schedule', $schedule))
            ->orderBy(['plannedTimestamp' => 'ASC'])
            ->setMaxResults($contextSize + 1);
        $inFuture = $this->scheduledExecutionsRepository->matching($criteria)->toArray();
        $criteria = new Criteria();
        $criteria
            ->where($criteria->expr()->lt('plannedTimestamp', $now))
            ->andWhere($criteria->expr()->eq('schedule', $schedule))
            ->orderBy(['plannedTimestamp' => 'DESC'])
            ->setMaxResults($contextSize);
        $inPast = $this->scheduledExecutionsRepository->matching($criteria)->toArray();
        return [
            'past' => array_reverse($inPast),
            'future' => $inFuture,
        ];
    }

    public function disable(Schedule $schedule) {
        $schedule->setEnabled(false);
        $this->deleteScheduledExecutions($schedule);
        $schedule->setNextCalculationDate($this->getNow());
        $this->entityManager->persist($schedule);
        $this->entityManager->flush();
    }

    public function deleteScheduledExecutions(Schedule $schedule) {
        $this->entityManager->createQueryBuilder()
            ->delete('SuplaBundle:ScheduledExecution', 's')
            ->where('s.schedule = :schedule')
            ->andWhere('s.consumed = 0')
            ->setParameter('schedule', $schedule)
            ->getQuery()
            ->execute();
    }

    public function enable(Schedule $schedule) {
        Assertion::true($schedule->isSubjectEnabled(), 'The device is disabled'); // i18n
        $schedule->setEnabled(true);
        $this->generateScheduledExecutions($schedule, '+2days');
    }

    public function recalculateScheduledExecutions(Schedule $schedule) {
        $this->disable($schedule);
        $this->enable($schedule);
    }

    public function delete(Schedule $schedule) {
        $this->deleteScheduledExecutions($schedule);
        $this->entityManager->remove($schedule);
        $this->entityManager->flush();
    }

    /** @return Schedule[] */
    public function findSchedulesForDevice(IODevice $device): array {
        $schedules = [];
        foreach ($device->getChannels() as $channel) {
            $schedules = array_merge($schedules, $channel->getSchedules()->toArray());
        }
        return $schedules;
    }

    /** @return Schedule[] */
    public function onlyEnabled(array $schedules): array {
        return array_filter($schedules, function (Schedule $schedule) {
            return $schedule->getEnabled();
        });
    }

    public function disableSchedulesForDevice(IODevice $device) {
        foreach ($this->onlyEnabled($this->findSchedulesForDevice($device)) as $schedule) {
            $this->disable($schedule);
        }
    }

    private function getNow(): DateTime {
        return new DateTime('now', new DateTimeZone('UTC'));
    }
}
