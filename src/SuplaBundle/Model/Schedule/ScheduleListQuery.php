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
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\QueryBuilder;
use SuplaBundle\Entity\Schedule;
use SuplaBundle\Entity\ScheduledExecution;
use SuplaBundle\Entity\User;

class ScheduleListQuery {
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(Registry $doctrine) {
        $this->entityManager = $doctrine->getManager();
    }

    public function getUserSchedules(User $user, array $sort) {
        $queryBuilder = $this->getQuery();
        $this->applySort($queryBuilder, $sort);
        $query = $queryBuilder->where('s.user = :user')
            ->setParameter('user', $user)
            ->getQuery();
        $schedules = $query->getResult();
        if ($schedules) {
            $this->fetchMostPastExecutions($schedules);
            $this->fetchMostFutureExecutions($schedules);
        }
        return $schedules;
    }

    private function getQuery(): QueryBuilder {
        return $this->entityManager->createQueryBuilder()
            ->select('s schedule')
            ->addSelect(['ch.caption channel_caption', 'ch.type channel_type', 'ch.function channel_function'])
            ->addSelect('dev.name device_name')
            ->addSelect('loc.caption location_caption')
            ->from(Schedule::class, 's')
            ->join('s.channel', 'ch')
            ->join('ch.iodevice', 'dev')
            ->join('dev.location', 'loc');
    }

    private function applySort(QueryBuilder $queryBuilder, array $sort) {
        if (count($sort) != 2) {
            $sort = ['s.caption', 'asc'];
        }
        Assertion::inArray($sort[0], ['s.caption', 's.dateStart', 'channel_caption', 'device_name', 'location_caption']);
        Assertion::inArray(strtolower($sort[1]), ['asc', 'desc']);
        $queryBuilder->orderBy($sort[0], $sort[1]);
    }

    private function fetchMostFutureExecutions(array &$schedules) {
        $query = <<<QUERY
            SELECT %COLUMNS%
            FROM supla_scheduled_executions e
            INNER JOIN (
               SELECT schedule_id, MIN(planned_timestamp) min_timestamp
               FROM supla_scheduled_executions
               WHERE schedule_id IN(%SCHEDULE_IDS%)
               AND result_timestamp IS NULL
               GROUP BY schedule_id
            ) AS t
            ON t.schedule_id = e.schedule_id AND t.min_timestamp = e.planned_timestamp;
QUERY;
        $this->fetchExecutions($schedules, $query, 'futureExecution');
    }

    private function fetchMostPastExecutions(array &$schedules) {
        $query = <<<QUERY
            SELECT %COLUMNS%
            FROM supla_scheduled_executions e
            INNER JOIN (
               SELECT schedule_id, MAX(result_timestamp) max_timestamp
               FROM supla_scheduled_executions
               WHERE schedule_id IN(%SCHEDULE_IDS%)
               AND result_timestamp IS NOT NULL
               GROUP BY schedule_id
            ) AS t
            ON t.schedule_id = e.schedule_id AND t.max_timestamp = e.result_timestamp;
QUERY;
        $this->fetchExecutions($schedules, $query, 'latestExecution');
    }

    private function fetchExecutions(array &$schedules, string $query, string $resultKey) {
        $scheduleIds = implode(',', array_map(function ($schedule) {
            return $schedule['schedule']->getId();
        }, $schedules));
        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata(ScheduledExecution::class, 'e');
        $executionsQuery = str_replace(['%COLUMNS%', '%SCHEDULE_IDS%'], [$rsm->generateSelectClause(), $scheduleIds], $query);
        $query = $this->entityManager->createNativeQuery($executionsQuery, $rsm);
        /** @var ScheduledExecution[] $latestExecutions */
        $executions = $query->getResult();
        $executionsMap = [];
        foreach ($executions as $execution) {
            $executionsMap[$execution->getSchedule()->getId()] = $execution;
        }
        foreach ($schedules as &$schedule) {
            $schedule[$resultKey] = $executionsMap[$schedule['schedule']->getId()] ?? null;
        }
    }
}
