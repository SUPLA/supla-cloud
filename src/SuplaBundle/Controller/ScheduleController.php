<?php
/*
 src/SuplaBundle/Controller/ScheduleController.php

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

namespace SuplaBundle\Controller;

use Assert\Assert;
use Assert\Assertion;
use Assert\InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SuplaBundle\Entity\Schedule;
use SuplaBundle\Model\Schedule\ScheduleListQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/schedule")
 */
class ScheduleController extends AbstractController {
    /**
     * @Route("/", name="_schedule_list", methods={"GET"})
     * @Template
     */
    public function scheduleListAction(Request $request) {
        if ($this->expectsJsonResponse()) {
            $query = new ScheduleListQuery($this->getDoctrine());
            $sort = explode('|', $request->get('sort', ''));
            $schedules = $query->getUserSchedules($this->getUser(), $sort);
            return $this->jsonResponse(['data' => $schedules, 'flat']);
        } else {
            return [];
        }
    }

    /**
     * @Route("/new", name="_schedule_new")
     * @Template("@Supla/Schedule/scheduleForm.html.twig")
     */
    public function newScheduleAction() {
        return [];
    }

    /**
     * @Route("/edit/{schedule}", name="_schedule_edit")
     * @Template("@Supla/Schedule/scheduleForm.html.twig")
     * @Security("user == schedule.getUser()")
     */
    public function scheduleEditAction(Schedule $schedule) {
        return ['schedule' => $schedule];
    }

    /**
     * @Route("/create")
     * @Method("POST")
     */
    public function createScheduleAction(Request $request) {
        Assertion::false($this->getUser()->isLimitScheduleExceeded(), 'Schedule limit has been exceeded');
        $data = $request->request->all();
        $schedule = $this->fillSchedule(new Schedule($this->getUser()), $data);
        $this->getDoctrine()->getManager()->persist($schedule);
        $this->getDoctrine()->getManager()->flush();
        $this->get('schedule_manager')->generateScheduledExecutions($schedule);
        return new JsonResponse(['id' => $schedule->getId()]);
    }

    /**
     * @Route("/next-run-dates", name="_schedule_get_run_dates", requirements={"timeExpression"=".+"})
     */
    public function getNextRunDatesAction(Request $request) {
        Assertion::true($request->isXmlHttpRequest());
        $data = $request->request->all();
        $temporarySchedule = new Schedule($this->getUser(), $data);
        $nextRunDates = $this->get('schedule_manager')->getNextRunDates($temporarySchedule, '+7days', 3);
        return new JsonResponse([
            'nextRunDates' => array_map(function ($dateTime) {
                return $dateTime->format(\DateTime::ATOM);
            }, $nextRunDates),
        ]);
    }

    /**
     * @Route("/{schedule}")
     * @Method("PUT")
     */
    public function editScheduleAction(Schedule $schedule, Request $request) {
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
            return new JsonResponse(['id' => $schedule->getId()]);
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

    /**
     * @Route("/{schedule}", name="_schedule_details")
     * @Security("user == schedule.getUser()")
     * @Template()
     */
    public function scheduleDetailsAction(Schedule $schedule, Request $request) {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            if (isset($data['disable'])) {
                $this->get('schedule_manager')->disable($schedule);
            } elseif (isset($data['enable'])) {
                try {
                    $this->get('schedule_manager')->enable($schedule);
                    Assertion::true($schedule->getEnabled(), 'Schedule cannot be enabled');
                } catch (InvalidArgumentException $e) {
                    $this->get('session')->getFlashBag()->add('error', ['title' => 'Error', 'message' => $e->getMessage()]);
                }
            } elseif (isset($data['delete'])) {
                $this->get('schedule_manager')->delete($schedule);
                $this->get('session')->getFlashBag()->add('success', ['title' => 'Success', 'message' => 'Schedule has been deleted']);
                return $this->redirectToRoute("_schedule_list");
            }
            return $this->redirectToRoute("_schedule_details", ['schedule' => $schedule->getId()]);
        }
        $data = [
            'schedule' => $schedule,
            'closestExecutions' => $this->get('schedule_manager')->findClosestExecutions($schedule),
        ];
        if ($this->expectsJsonResponse()) {
            return $this->jsonResponse($data);
        } else {
            return $data;
        }
    }

    /**
     * @Route("", methods={"PATCH"})
     */
    public function schedulesEditAction(Request $request) {
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
        return new Response(null, 204);
    }
}
