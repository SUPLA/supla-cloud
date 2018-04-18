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

namespace SuplaApiBundle\Controller;

use Assert\Assert;
use Assert\Assertion;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaApiBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\Schedule;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Repository\ScheduleListQuery;
use SuplaBundle\Repository\ScheduleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiScheduleController extends RestController {
    /** @var ScheduleRepository */
    private $scheduleRepository;
    /** @var ChannelActionExecutor */
    private $channelActionExecutor;

    public function __construct(ScheduleRepository $scheduleRepository, ChannelActionExecutor $channelActionExecutor) {
        $this->scheduleRepository = $scheduleRepository;
        $this->channelActionExecutor = $channelActionExecutor;
    }

    /**
     * @apiIgnore
     * @api {get} /schedules List
     * @apiDescription Get list of schedules.
     * @apiGroup Schedules
     * @apiVersion 2.2.0
     * @apiParam {string} include Comma-separated list of what to fetch for every Schedule.
     * Available options: `channel`, `iodevice`, `location`, `closestExecutions`, `function`, `type`.
     * @apiParamExample {GET} GET param to fetch IODevice's channels and location
     * include=channel,closestExecutions
     */
    public function getSchedulesAction(Request $request) {
        return $this->returnSchedules(ScheduleListQuery::create()->filterByUser($this->getUser()), $request);
    }

    /**
     * @apiIgnore
     * @api {get} /channels/{channelId}/schedules List schedules
     * @apiDescription Get list of schedules for given channel
     * @apiGroup Channels
     * @apiVersion 2.2.0
     * @apiParam {string} include Comma-separated list of what to fetch for every Schedule.
     * Available options: `channel`, `iodevice`, `location`, `closestExecutions`, `function`, `type`.
     * @apiParamExample {GET} GET param to fetch IODevice's channels and location
     * include=channel,closestExecutions
     */
    /**
     * @Security("channel.belongsToUser(user)")
     */
    public function getChannelSchedulesAction(IODeviceChannel $channel, Request $request) {
        return $this->returnSchedules(ScheduleListQuery::create()->filterByChannel($channel), $request);
    }

    private function returnSchedules(ScheduleListQuery $query, Request $request) {
        if (count($sort = explode('|', $request->get('sort', ''))) == 2) {
            $query->orderBy($sort[0], $sort[1]);
        }
        $schedules = $this->scheduleRepository->findByQuery($query);
        $view = $this->view($schedules, Response::HTTP_OK);
        $this->setSerializationGroups($view, $request, ['channel', 'iodevice', 'location', 'closestExecutions']);
        $view->setHeader('SUPLA-Total-Schedules', $this->getUser()->getSchedules()->count());
        return $view;
    }

    /**
     * @apiIgnore
     * @api {get} /schedules/{id} Details
     * @apiDescription Get details of schedule with `{id}` identifier.
     * @apiGroup Schedules
     * @apiVersion 2.2.0
     * @apiParam {string} include Comma-separated list of what to fetch for every Schedule.
     * Available options: `channel`, `iodevice`, `location`, `closestExecutions`, `function`, `type`.
     * @apiParamExample {GET} GET param to fetch IODevice's channels and location
     * include=channel,closestExecutions
     */
    /**
     * @Security("schedule.belongsToUser(user)")
     */
    public function getScheduleAction(Request $request, Schedule $schedule) {
        $view = $this->view($schedule, Response::HTTP_OK);
        $this->setSerializationGroups($view, $request, ['channel', 'iodevice', 'location', 'closestExecutions']);
        return $view;
    }

    public function postScheduleAction(Request $request) {
        Assertion::false($this->getCurrentUser()->isLimitScheduleExceeded(), 'Schedule limit has been exceeded');
        $data = $request->request->all();
        $schedule = $this->fillSchedule(new Schedule($this->getCurrentUser()), $data);
        $this->getDoctrine()->getManager()->persist($schedule);
        $this->getDoctrine()->getManager()->flush();
        if ($schedule->getChannel()->getIoDevice()->getEnabled()) {
            $this->get('schedule_manager')->enable($schedule);
        }
        return $this->view($schedule, Response::HTTP_CREATED);
    }

    /**
     * @Security("schedule.belongsToUser(user)")
     */
    public function putScheduleAction(Request $request, Schedule $schedule) {
        $data = $request->request->all();
        $this->fillSchedule($schedule, $data);
        return $this->getDoctrine()->getManager()->transactional(function ($em) use ($schedule, $request, $data) {
            $this->get('schedule_manager')->deleteScheduledExecutions($schedule);
            $em->persist($schedule);
            if (!$schedule->getEnabled() && ($request->get('enable') || ($data['enabled'] ?? false))) {
                $this->get('schedule_manager')->enable($schedule);
            } elseif ($schedule->getEnabled() && (!($data['enabled'] ?? true) || !$schedule->getChannel()->getIoDevice()->getEnabled())) {
                $this->get('schedule_manager')->disable($schedule);
            }
            if ($schedule->getEnabled()) {
                $this->get('schedule_manager')->generateScheduledExecutions($schedule);
            }
            return $this->view($schedule, Response::HTTP_OK);
        });
    }

    /** @return Schedule */
    private function fillSchedule(Schedule $schedule, array $data) {
        Assert::that($data)
            ->notEmptyKey('channelId')
            ->notEmptyKey('actionId')
            ->notEmptyKey('mode')
            ->notEmptyKey('timeExpression');
        $channel = $this->get('iodevice_manager')->channelById($data['channelId']);
        Assertion::notNull($channel);
        $data['channel'] = $channel;
        if ($data['actionParam']) {
            $data['actionParam'] = $this->channelActionExecutor->validateActionParams(
                $channel,
                new ChannelFunctionAction($data['actionId'] ?? ChannelFunctionAction::TURN_ON),
                $data['actionParam']
            );
        }
        $schedule->fill($data);
        $errors = iterator_to_array($this->get('validator')->validate($schedule));
        Assertion::count($errors, 0, implode(', ', $errors));
        $nextRunDates = $this->get('schedule_manager')->getNextRunDates($schedule, '+5days', 1, true);
        Assertion::notEmpty($nextRunDates, 'Schedule cannot be enabled');
        return $schedule;
    }

    public function patchSchedulesAction(Request $request) {
        $data = $request->request->all();
        $this->getDoctrine()->getManager()->transactional(function () use ($data) {
            if (isset($data['enable'])) {
                foreach ($this->getCurrentUser()->getSchedules() as $schedule) {
                    if (in_array($schedule->getId(), $data['enable']) && !$schedule->getEnabled()) {
                        $this->get('schedule_manager')->enable($schedule);
                    }
                }
            }
        });
        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Security("schedule.belongsToUser(user)")
     */
    public function deleteScheduleAction(Schedule $schedule) {
        $this->get('schedule_manager')->delete($schedule);
        return new Response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Post("/schedules/next-run-dates")
     */
    public function getNextRunDatesAction(Request $request) {
        Assertion::true($request->isXmlHttpRequest());
        $data = $request->request->all();
        $temporarySchedule = new Schedule($this->getCurrentUser(), $data);
        $nextRunDates = $this->get('schedule_manager')->getNextRunDates($temporarySchedule, '+7days', 3);
        return $this->view(array_map(function ($dateTime) {
            return $dateTime->format(\DateTime::ATOM);
        }, $nextRunDates), Response::HTTP_OK);
    }
}
