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
use SuplaBundle\Entity\Schedule;
use SuplaBundle\Model\Schedule\ScheduleListQuery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiScheduleController extends RestController {
    /**
     * @apiIgnore
     * @api {get} /schedules List
     * @apiDescription Get list of schedules.
     * @apiGroup Schedules
     * @apiVersion 2.2.0
     * @apiParam {string} include Comma-separated list of what to fetch for every Schedule.
     * Available options: `channel`, `iodevice`, `location`, `closestExecutions`.
     * @apiParamExample {GET} GET param to fetch IODevice's channels and location
     * include=channel,closestExecutions
     */
    public function getSchedulesAction(Request $request) {
        $query = new ScheduleListQuery($this->getDoctrine());
        $sort = explode('|', $request->get('sort', ''));
        $schedules = $query->getUserSchedules($this->getUser(), $sort);
        if ($channelId = $request->get('channelId')) {
            $schedules = array_filter($schedules, function ($schedule) {
                return true;
            });
        }
        return $this->view($schedules, Response::HTTP_OK);
    }

    /**
     * @apiIgnore
     * @api {get} /schedules/{id} Details
     * @apiDescription Get details of schedule with `{id}` identifier.
     * @apiGroup Schedules
     * @apiVersion 2.2.0
     * @apiParam {string} include Comma-separated list of what to fetch for every Schedule.
     * Available options: `channel`, `iodevice`, `location`, `closestExecutions`.
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
        Assertion::false($this->getUser()->isLimitScheduleExceeded(), 'Schedule limit has been exceeded');
        $data = $request->request->all();
        $schedule = $this->fillSchedule(new Schedule($this->getUser()), $data);
        $this->getDoctrine()->getManager()->persist($schedule);
        $this->getDoctrine()->getManager()->flush();
        $this->get('schedule_manager')->generateScheduledExecutions($schedule);
        return $this->view($schedule, Response::HTTP_CREATED);
    }

    /**
     * @Security("schedule.belongsToUser(user)")
     */
    public function putScheduleAction(Request $request, Schedule $schedule) {
        $data = $request->request->all();
        $this->fillSchedule($schedule, $data);
        return $this->getDoctrine()->getManager()->transactional(function ($em) use ($schedule, $request) {
            $this->get('schedule_manager')->deleteScheduledExecutions($schedule);
            $em->persist($schedule);
            if ($schedule->getEnabled()) {
                $this->get('schedule_manager')->generateScheduledExecutions($schedule);
            } elseif ($request->get('enable')) {
                $this->get('schedule_manager')->enable($schedule);
            }
            return $this->view($schedule, Response::HTTP_OK);
        });
    }

    /** @return Schedule */
    private function fillSchedule(Schedule $schedule, array $data) {
        Assert::that($data)
            ->notEmptyKey('channel')
            ->notEmptyKey('action')
            ->notEmptyKey('scheduleMode')
            ->notEmptyKey('timeExpression');
        $channel = $this->get('iodevice_manager')->channelById($data['channel']);
        Assertion::notNull($channel);
        $data['channel'] = $channel;
        $schedule->fill($data);
        $errors = iterator_to_array($this->get('validator')->validate($schedule));
        Assertion::count($errors, 0, implode(', ', $errors));
        $nextRunDates = $this->get('schedule_manager')->getNextRunDates($schedule, '+5days', 1, true);
        Assertion::notEmpty($nextRunDates, 'Invalid time expression');
        return $schedule;
    }

    public function patchSchedulesAction(Request $request) {
        $data = $request->request->all();
        $this->getDoctrine()->getManager()->transactional(function () use ($data) {
            if (isset($data['enable'])) {
                foreach ($this->getUser()->getSchedules() as $schedule) {
                    if (in_array($schedule->getId(), $data['enable']) && !$schedule->getEnabled()) {
                        $this->get('schedule_manager')->enable($schedule);
                    }
                }
            }
        });
        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Post("/schedules/next-run-dates")
     */
    public function getNextRunDatesAction(Request $request) {
        Assertion::true($request->isXmlHttpRequest());
        $data = $request->request->all();
        $temporarySchedule = new Schedule($this->getUser(), $data);
        $nextRunDates = $this->get('schedule_manager')->getNextRunDates($temporarySchedule, '+7days', 3);
        return $this->view(array_map(function ($dateTime) {
            return $dateTime->format(\DateTime::ATOM);
        }, $nextRunDates), Response::HTTP_OK);
    }
}
