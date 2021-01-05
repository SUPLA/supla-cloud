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

namespace SuplaBundle\Controller\Api;

use Assert\Assert;
use Assert\Assertion;
use DateTime;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\IODeviceChannelGroup;
use SuplaBundle\Entity\Schedule;
use SuplaBundle\Entity\ScheduledExecution;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\EventListener\UnavailableInMaintenance;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaBundle\Model\Schedule\ScheduleManager;
use SuplaBundle\Repository\ChannelGroupRepository;
use SuplaBundle\Repository\IODeviceChannelRepository;
use SuplaBundle\Repository\ScheduleListQuery;
use SuplaBundle\Repository\ScheduleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ScheduleController extends RestController {
    /** @var ScheduleRepository */
    private $scheduleRepository;
    /** @var ChannelActionExecutor */
    private $channelActionExecutor;
    /** @var ChannelGroupRepository */
    private $channelGroupRepository;
    /** @var IODeviceChannelRepository */
    private $channelRepository;
    /** @var ScheduleManager */
    private $scheduleManager;
    /** @var ValidatorInterface */
    private $validator;

    public function __construct(
        ScheduleRepository $scheduleRepository,
        ChannelGroupRepository $channelGroupRepository,
        IODeviceChannelRepository $channelRepository,
        ChannelActionExecutor $channelActionExecutor,
        ScheduleManager $scheduleManager,
        ValidatorInterface $validator
    ) {
        $this->scheduleRepository = $scheduleRepository;
        $this->channelActionExecutor = $channelActionExecutor;
        $this->channelGroupRepository = $channelGroupRepository;
        $this->channelRepository = $channelRepository;
        $this->scheduleManager = $scheduleManager;
        $this->validator = $validator;
    }

    protected function getDefaultAllowedSerializationGroups(Request $request): array {
        if (ApiVersions::V2_3()->isRequestedEqualOrGreaterThan($request)) {
            return ['subject', 'closestExecutions', 'subject' => 'schedule.subject'];
        } else {
            return ['channel', 'iodevice', 'location', 'closestExecutions'];
        }
    }

    /** @Security("has_role('ROLE_SCHEDULES_R')") */
    public function getSchedulesAction(Request $request) {
        return $this->returnSchedules(ScheduleListQuery::create()->filterByUser($this->getUser()), $request);
    }

    /**
     * @Security("channel.belongsToUser(user) and has_role('ROLE_CHANNELS_R')")
     */
    public function getChannelSchedulesAction(IODeviceChannel $channel, Request $request) {
        return $this->returnSchedules(ScheduleListQuery::create()->filterByChannel($channel), $request);
    }

    /**
     * @Security("channelGroup.belongsToUser(user) and has_role('ROLE_CHANNELGROUPS_R')")
     * @Rest\Get("/channel-groups/{channelGroup}/schedules")
     */
    public function getChannelGroupSchedulesAction(IODeviceChannelGroup $channelGroup, Request $request) {
        return $this->returnSchedules(ScheduleListQuery::create()->filterByChannelGroup($channelGroup), $request);
    }

    private function returnSchedules(ScheduleListQuery $query, Request $request) {
        if (count($sort = explode('|', $request->get('sort', ''))) == 2) {
            $query->orderBy($sort[0], $sort[1]);
        }
        $schedules = $this->scheduleRepository->findByQuery($query);
        $view = $this->serializedView($schedules, $request);
        $view->setHeader('SUPLA-Total-Schedules', $this->getUser()->getSchedules()->count());
        return $view;
    }

    /**
     * @Security("schedule.belongsToUser(user) and has_role('ROLE_SCHEDULES_R')")
     */
    public function getScheduleAction(Request $request, Schedule $schedule) {
        return $this->serializedView($schedule, $request, ['subject.relationsCount']);
    }

    /**
     * @Security("has_role('ROLE_SCHEDULES_RW')")
     * @UnavailableInMaintenance
     */
    public function postScheduleAction(Request $request) {
        Assertion::false($this->getCurrentUser()->isLimitScheduleExceeded(), 'Schedule limit has been exceeded'); // i18n
        $data = $request->request->all();
        if (!ApiVersions::V2_3()->isRequestedEqualOrGreaterThan($request)) {
            $data['subjectId'] = $data['channelId'] ?? null;
            $data['subjectType'] = 'channel';
        }
        $schedule = $this->fillSchedule(new Schedule($this->getCurrentUser()), $data);
        $this->getDoctrine()->getManager()->persist($schedule);
        $this->getDoctrine()->getManager()->flush();
        if ($schedule->isSubjectEnabled()) {
            $this->scheduleManager->enable($schedule);
        }
        return $this->view($schedule, Response::HTTP_CREATED);
    }

    /**
     * @Security("schedule.belongsToUser(user) and has_role('ROLE_SCHEDULES_RW')")
     * @UnavailableInMaintenance
     */
    public function putScheduleAction(Request $request, Schedule $schedule) {
        $data = $request->request->all();
        $this->fillSchedule($schedule, $data);
        return $this->getDoctrine()->getManager()->transactional(function ($em) use ($schedule, $request, $data) {
            $this->scheduleManager->deleteScheduledExecutions($schedule);
            $em->persist($schedule);
            if (!$schedule->getEnabled() && ($request->get('enable') || ($data['enabled'] ?? false))) {
                $this->scheduleManager->enable($schedule);
            } elseif ($schedule->getEnabled() && (!($data['enabled'] ?? true) || !$schedule->isSubjectEnabled())) {
                $this->scheduleManager->disable($schedule);
            }
            if ($schedule->getEnabled()) {
                $this->scheduleManager->generateScheduledExecutions($schedule);
            }
            return $this->view($schedule, Response::HTTP_OK);
        });
    }

    /** @return Schedule */
    private function fillSchedule(Schedule $schedule, array $data) {
        Assert::that($data)
            ->notEmptyKey('subjectId')
            ->notEmptyKey('subjectType')
            ->notEmptyKey('actionId')
            ->notEmptyKey('mode')
            ->notEmptyKey('timeExpression');
        $subject = null;
        if ($data['subjectType'] == ActionableSubjectType::CHANNEL) {
            $subject = $this->channelRepository->findForUser($this->getUser(), $data['subjectId']);
        } elseif ($data['subjectType'] == ActionableSubjectType::CHANNEL_GROUP) {
            $subject = $this->channelGroupRepository->findForUser($this->getUser(), $data['subjectId']);
        }
        Assertion::notNull($subject, 'Invalid schedule subject.');
        $data['subject'] = $subject;
        if (isset($data['actionParam']) && $data['actionParam']) {
            $data['actionParam'] = $this->channelActionExecutor->validateActionParams(
                $subject,
                new ChannelFunctionAction($data['actionId'] ?? ChannelFunctionAction::TURN_ON),
                $data['actionParam']
            );
        }
        $schedule->fill($data);
        $errors = iterator_to_array($this->validator->validate($schedule));
        Assertion::count($errors, 0, implode(', ', $errors));
        $nextScheduleExecutions = $this->scheduleManager->getNextScheduleExecutions($schedule, '+5days', 1, true);
        Assertion::notEmpty($nextScheduleExecutions, 'Schedule cannot be enabled'); // i18n
        return $schedule;
    }

    /**
     * @Security("has_role('ROLE_SCHEDULES_RW')")
     * @UnavailableInMaintenance
     */
    public function patchSchedulesAction(Request $request) {
        $data = $request->request->all();
        $this->getDoctrine()->getManager()->transactional(function () use ($data) {
            if (isset($data['enable'])) {
                foreach ($this->getCurrentUser()->getSchedules() as $schedule) {
                    if (in_array($schedule->getId(), $data['enable']) && !$schedule->getEnabled()) {
                        $this->scheduleManager->enable($schedule);
                    }
                }
            }
        });
        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Security("schedule.belongsToUser(user) and has_role('ROLE_SCHEDULES_RW')")
     * @UnavailableInMaintenance
     */
    public function deleteScheduleAction(Schedule $schedule) {
        $this->scheduleManager->delete($schedule);
        return new Response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Post("/schedules/next-run-dates")
     * @Security("has_role('ROLE_SCHEDULES_R')")
     * @deprecated
     */
    public function getNextRunDatesAction(Request $request) {
        $data = $request->request->all();
        $temporarySchedule = new Schedule($this->getCurrentUser(), $data);
        $nextRunDates = $this->scheduleManager->getNextScheduleExecutions($temporarySchedule, '+7days', 3);
        return $this->view(array_map(function (ScheduledExecution $execution) {
            return $execution->getPlannedTimestamp()->format(DateTime::ATOM);
        }, $nextRunDates), Response::HTTP_OK);
    }

    /**
     * @Rest\Post("/schedules/next-schedule-executions")
     * @Security("has_role('ROLE_SCHEDULES_R')")
     * @deprecated
     */
    public function getNextScheduleExecutionsAction(Request $request) {
        $data = $request->request->all();
        $temporarySchedule = new Schedule($this->getCurrentUser(), $data);
        $scheduleExecutions = $this->scheduleManager->getNextScheduleExecutions($temporarySchedule, '+7days', 3);
        return $this->view($scheduleExecutions, Response::HTTP_OK);
    }
}
