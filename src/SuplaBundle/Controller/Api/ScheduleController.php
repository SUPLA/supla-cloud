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
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\IODeviceChannelGroup;
use SuplaBundle\Entity\Main\Scene;
use SuplaBundle\Entity\Main\Schedule;
use SuplaBundle\Entity\Main\ScheduledExecution;
use SuplaBundle\EventListener\UnavailableInMaintenance;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\Schedule\ScheduleManager;
use SuplaBundle\Repository\ActionableSubjectRepository;
use SuplaBundle\Repository\ScheduleListQuery;
use SuplaBundle\Repository\ScheduleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Schema(
 *   schema="ScheduleConfigEntry", type="object",
 *   @OA\Property(property="crontab", type="string"),
 *   @OA\Property(property="action", type="object",
 *     @OA\Property(property="id", ref="#/components/schemas/ChannelFunctionActionIds"),
 *     @OA\Property(property="param", nullable=true, ref="#/components/schemas/ChannelActionParams"),
 *   )
 * )
 * @OA\Schema(
 *   schema="ScheduleScheduledExecution", type="object",
 *   @OA\Property(property="actionId", ref="#/components/schemas/ChannelFunctionActionIds"),
 *   @OA\Property(property="actionParam", nullable=true, ref="#/components/schemas/ChannelActionParams"),
 *   @OA\Property(property="plannedTimestamp", type="string", format="date-time"),
 *   @OA\Property(property="resultTimestamp", type="string", format="date-time"),
 *   @OA\Property(property="failed", type="boolean"),
 *   @OA\Property(property="result", type="object",
 *     @OA\Property(property="id", type="integer", enum={0,1,2,3,4,5,6,7,8,9,10}),
 *     @OA\Property(property="caption", type="string"),
 *   ),
 * )
 * @OA\Schema(
 *   schema="Schedule", type="object",
 *   @OA\Property(property="id", type="integer", description="Identifier"),
 *   @OA\Property(property="caption", type="string", description="Caption"),
 *   @OA\Property(property="subjectType", ref="#/components/schemas/ActionableSubjectTypeNames"),
 *   @OA\Property(property="subjectId", type="integer"),
 *   @OA\Property(property="dateStart", type="string", format="date-time"),
 *   @OA\Property(property="dateEnd", type="string", format="date-time"),
 *   @OA\Property(property="retry", type="boolean"),
 *   @OA\Property(property="enabled", type="boolean"),
 *   @OA\Property(property="mode", type="string", enum={"once", "minutely", "daily", "crontab"}),
 *   @OA\Property(property="config", type="array", @OA\Items(ref="#/components/schemas/ScheduleConfigEntry")),
 *   @OA\Property(property="subject", description="Only if requested by the `include` param.", ref="#/components/schemas/ActionableSubject"),
 *   @OA\Property(property="closestExecutions", description="Only if requested by the `include` param.", type="object",
 *     @OA\Property(property="past", type="array", @OA\Items(ref="#/components/schemas/ScheduleScheduledExecution")),
 *     @OA\Property(property="future", type="array", @OA\Items(ref="#/components/schemas/ScheduleScheduledExecution")),
 *  )
 * )
 */
class ScheduleController extends RestController {
    /** @var ScheduleRepository */
    private $scheduleRepository;
    /** @var ScheduleManager */
    private $scheduleManager;
    /** @var ActionableSubjectRepository */
    private $subjectRepository;

    public function __construct(
        ScheduleRepository $scheduleRepository,
        ActionableSubjectRepository $subjectRepository,
        ScheduleManager $scheduleManager
    ) {
        $this->scheduleRepository = $scheduleRepository;
        $this->scheduleManager = $scheduleManager;
        $this->subjectRepository = $subjectRepository;
    }

    protected function getDefaultAllowedSerializationGroups(Request $request): array {
        if (ApiVersions::V2_3()->isRequestedEqualOrGreaterThan($request)) {
            return ['subject', 'closestExecutions', 'subject' => 'schedule.subject'];
        } else {
            return ['channel', 'iodevice', 'location', 'closestExecutions'];
        }
    }

    /**
     * @OA\Get(
     *     path="/schedules", operationId="getSchedules", summary="Get Schedules", tags={"Schedules"},
     *     @OA\Parameter(
     *         description="List of extra fields to include in the response.",
     *         in="query", name="include", required=false, explode=false,
     *         @OA\Schema(type="array", @OA\Items(type="string", enum={"subject", "closestExecutions"})),
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Schedule"))),
     * )
     * @Rest\Get("/schedules")
     * @Security("is_granted('ROLE_SCHEDULES_R')")
     */
    public function getSchedulesAction(Request $request) {
        return $this->returnSchedules(ScheduleListQuery::create()->filterByUser($this->getUser()), $request);
    }

    /**
     * @Security("channel.belongsToUser(user) and is_granted('ROLE_CHANNELS_R')")
     * @Rest\Get("/channels/{channel}/schedules")
     */
    public function getChannelSchedulesAction(IODeviceChannel $channel, Request $request) {
        return $this->returnSchedules(ScheduleListQuery::create()->filterByChannel($channel), $request);
    }

    /**
     * @Security("channelGroup.belongsToUser(user) and is_granted('ROLE_CHANNELGROUPS_R')")
     * @Rest\Get("/channel-groups/{channelGroup}/schedules")
     */
    public function getChannelGroupSchedulesAction(IODeviceChannelGroup $channelGroup, Request $request) {
        return $this->returnSchedules(ScheduleListQuery::create()->filterByChannelGroup($channelGroup), $request);
    }

    /**
     * @Security("scene.belongsToUser(user) and is_granted('ROLE_SCENES_R')")
     * @Rest\Get("/scenes/{scene}/schedules")
     */
    public function getSceneSchedulesAction(Scene $scene, Request $request) {
        return $this->returnSchedules(ScheduleListQuery::create()->filterByScene($scene), $request);
    }

    private function returnSchedules(ScheduleListQuery $query, Request $request) {
        if (count($sort = explode('|', $request->get('sort', ''))) == 2) {
            $query->orderBy($sort[0], $sort[1]);
        }
        $schedules = $this->scheduleRepository->findByQuery($query);
        $view = $this->serializedView($schedules, $request);
        $view->setHeader('X-Total-Count', $this->getUser()->getSchedules()->count());
        return $view;
    }

    /**
     * @Security("schedule.belongsToUser(user) and is_granted('ROLE_SCHEDULES_R')")
     * @Rest\Get("/schedules/{schedule}")
     */
    public function getScheduleAction(Request $request, Schedule $schedule) {
        return $this->serializedView($schedule, $request, ['subject.relationsCount']);
    }

    /**
     * @Security("is_granted('ROLE_SCHEDULES_RW')")
     * @Rest\Post("/schedules")
     * @UnavailableInMaintenance
     */
    public function postScheduleAction(Request $request) {
        Assertion::false($this->getCurrentUser()->isLimitScheduleExceeded(), 'Schedule limit has been exceeded'); // i18n
        $data = $request->request->all();
        $schedule = $this->fillSchedule(new Schedule($this->getCurrentUser()), $data, $request);
        $this->getDoctrine()->getManager()->persist($schedule);
        $this->getDoctrine()->getManager()->flush();
        if ($schedule->isSubjectEnabled()) {
            $this->scheduleManager->enable($schedule);
        }
        return $this->serializedView($schedule, $request, ['subject.relationsCount'], Response::HTTP_CREATED);
    }

    /**
     * @Security("schedule.belongsToUser(user) and is_granted('ROLE_SCHEDULES_RW')")
     * @Rest\Put("/schedules/{schedule}")
     * @UnavailableInMaintenance
     */
    public function putScheduleAction(Request $request, Schedule $schedule) {
        $data = $request->request->all();
        if (!ApiVersions::V2_3()->isRequestedEqualOrGreaterThan($request)) {
            $data['subjectId'] = $data['channelId'] ?? null;
            $data['subjectType'] = 'channel';
        }
        $this->fillSchedule($schedule, $data, $request);
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

    /** @return \SuplaBundle\Entity\Main\Schedule */
    private function fillSchedule(Schedule $schedule, array $data, Request $request) {
        if (!ApiVersions::V2_3()->isRequestedEqualOrGreaterThan($request)) {
            $data['subjectId'] = $data['channelId'] ?? null;
            $data['subjectType'] = 'channel';
        }
        if (!ApiVersions::V2_4()->isRequestedEqualOrGreaterThan($request)) {
            if (isset($data['timeExpression']) && isset($data['actionId']) && !isset($data['config'])) {
                $data['config'] = [
                    [
                        'crontab' => $data['timeExpression'],
                        'action' => ['id' => $data['actionId'], 'param' => $data['actionParam'] ?? null],
                    ],
                ];
            }
        }
        Assert::that($data)
            ->notEmptyKey('subjectId')
            ->notEmptyKey('subjectType')
            ->notEmptyKey('mode')
            ->notEmptyKey('config');
        $subject = $this->subjectRepository->findForUser($this->getUser(), $data['subjectType'], $data['subjectId']);
        $data['subject'] = $subject;
        $schedule->fill($data);
        $this->scheduleManager->validateSchedule($schedule);
        return $schedule;
    }

    /**
     * @Security("is_granted('ROLE_SCHEDULES_RW')")
     * @Rest\Patch("/schedules")
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
     * @Security("schedule.belongsToUser(user) and is_granted('ROLE_SCHEDULES_RW')")
     * @Rest\Delete("/schedules/{schedule}")
     * @UnavailableInMaintenance
     */
    public function deleteScheduleAction(Schedule $schedule) {
        $this->scheduleManager->delete($schedule);
        return new Response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Post("/schedules/next-run-dates")
     * @Security("is_granted('ROLE_SCHEDULES_R')")
     * @deprecated
     */
    public function getNextRunDatesAction(Request $request) {
        // TODO uncomment in v2.4
        // Assertion::false(ApiVersions::V2_4()->isRequestedEqualOrGreaterThan($request), 'Endpoint not available in v2.4.');
        $data = $request->request->all();
        $temporarySchedule = new Schedule($this->getCurrentUser(), $data);
        $nextRunDates = $this->scheduleManager->getNextScheduleExecutions($temporarySchedule, '+7days', 3);
        return $this->view(array_map(function (ScheduledExecution $execution) {
            return $execution->getPlannedTimestamp()->format(DateTime::ATOM);
        }, $nextRunDates), Response::HTTP_OK);
    }

    /**
     * @Rest\Post("/schedules/next-schedule-executions")
     * @Security("is_granted('ROLE_SCHEDULES_R')")
     */
    public function getNextScheduleExecutionsAction(Request $request) {
        $data = $request->request->all();
        if (!ApiVersions::V2_4()->isRequestedEqualOrGreaterThan($request)) {
            if (isset($data['timeExpression']) && isset($data['actionId']) && !isset($data['config'])) {
                $data['config'] = [
                    [
                        'crontab' => $data['timeExpression'],
                        'action' => ['id' => $data['actionId'] ?? null, 'param' => $data['actionParam'] ?? null],
                    ],
                ];
            }
        }
        $temporarySchedule = new Schedule($this->getCurrentUser(), $data);
        if ($temporarySchedule->getConfig()) {
            $scheduleExecutions = $this->scheduleManager->getNextScheduleExecutions($temporarySchedule, '+6months', 3);
            return $this->view($scheduleExecutions, Response::HTTP_OK);
        } else {
            return $this->view([], Response::HTTP_OK);
        }
    }
}
