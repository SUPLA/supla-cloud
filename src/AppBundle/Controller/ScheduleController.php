<?php
/*
 src/AppBundle/Controller/ScheduleController.php

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

namespace AppBundle\Controller;


use AppBundle\Entity\IODeviceChannel;
use AppBundle\Entity\Schedule;
use AppBundle\Entity\User;
use AppBundle\Model\UserManager;
use Cron\CronExpression;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @Route("/schedule")
 */
class ScheduleController extends Controller
{
    /**
     * @Route("/", name="_schedule_list")
     * @Template
     */
    public function scheduleListAction()
    {
        return [];
    }

    /**
     * @Route("/new", name="_schedule_new")
     * @Template
     */
    public function newScheduleAction()
    {
        /** @var User $user */
        $user = $this->getUser();
        $channels = $this->getDoctrine()->getRepository('AppBundle:IODeviceChannel')->findBy(['user' => $user]);
        $ioDeviceManager = $this->get('iodevice_manager');
        $schedulableFunctions = $ioDeviceManager->getFunctionsThatCanBeScheduled();
        $schedulableChannels = array_filter($channels, function (IODeviceChannel $channel) use ($schedulableFunctions) {
            return in_array($channel->getFunction(), $schedulableFunctions);
        });
        $channelToFunctionsMap = [];
        foreach ($schedulableChannels as $channel) {
            $channelToFunctionsMap[$channel->getId()] = $ioDeviceManager->functionActionMap()[$channel->getFunction()];
        }
        return [
            'scheduleModes' => ['once', 'minutely', 'hourly', 'daily'],
            'userChannels' => $schedulableChannels,
            'actionStringMap' => $ioDeviceManager->actionStringMap(),
            'channelToFunctionsMap' => $channelToFunctionsMap,
        ];
    }

    /**
     * @Route("/create")
     * @Method("POST")
     */
    public function createScheduleAction(Request $request)
    {
        $data = $request->request->all();
        $channel = $this->get('iodevice_manager')->channelById($data['channel']);
        $schedule = new Schedule();
        /** @var User $user */
        $user = $this->getUser();
        $schedule->setUser($user);
        $schedule->setChannel($channel);
        $schedule->setAction($data['action']);
        $schedule->setActionParam(empty($data['actionParam']) ? null : $data['actionParam']);
        $schedule->setDateStart(\DateTime::createFromFormat(\DateTime::ATOM, $data['dateStart']));
        $schedule->setDateEnd($data['dateEnd'] ? \DateTime::createFromFormat(\DateTime::ATOM, $data['dateEnd']) : null);
        $schedule->setMode($data['mode']);
        $schedule->setCronExpression($data['cronExpression']);
        $errors = iterator_to_array($this->get('validator')->validate($schedule));
        if ($user->isLimitScheduleExceeded()) {
            $errors[] = 'Schedule limit has been exceeded';
        } else if (!CronExpression::isValidExpression($schedule->getCronExpression())) {
            $errors[] = 'Invalid cron expression';
        }
        if (count($errors) == 0) {
            $this->getDoctrine()->getManager()->persist($schedule);
            $this->getDoctrine()->getManager()->flush();
            $this->get('schedule_manager')->generateScheduledExecutions($schedule);
            return new JsonResponse(['id' => $schedule->getId()]);
        } else {
            return new JsonResponse(array_map(function ($error) {
                return is_string($error) ? $error : $error->getMessage();
            }, $errors), 400);
        }
    }

    /**
     * @Route("/next-run-dates", name="_schedule_get_run_dates", requirements={"cronExpression"=".+"})
     */
    public function getNextRunDatesAction(Request $request)
    {
        $data = $request->request->all();
        if (!$request->isXmlHttpRequest() || empty($data['cronExpression'])) {
            throw $this->createNotFoundException();
        }
        $temporarySchedule = new Schedule();
        $temporarySchedule->setUser($this->getUser());
        $temporarySchedule->setCronExpression($data['cronExpression']);
        $temporarySchedule->setDateStart(\DateTime::createFromFormat(\DateTime::ATOM, $data['dateStart']));
        $temporarySchedule->setDateEnd($data['dateEnd'] ? \DateTime::createFromFormat(\DateTime::ATOM, $data['dateEnd']) : null);
        $nextRunDates = $this->get('schedule_manager')->getNextRunDates($temporarySchedule, '+7days', 3);
        return new JsonResponse([
            'nextRunDates' => array_map(function ($dateTime) {
                return $dateTime->format(\DateTime::ATOM);
            }, $nextRunDates),
        ]);
    }

    /**
     * @Route("/user-timezone", name="_schedule_update_user_timezone")
     * @Method("PUT")
     */
    public function updateUserTimezoneAction(Request $request)
    {
        $data = $request->request->all();
        try {
            $timezone = new \DateTimeZone($data['timezone']);
            /** @var UserManager $userManager */
            $userManager = $this->get('user_manager');
            $userManager->updateTimeZone($this->getUser(), $timezone);
            return new JsonResponse(true);
        } catch (Exception $e) {
            throw new BadRequestHttpException();
        }
    }

    /**
     * @Route("/{schedule}", name="_schedule_details")
     * @Template
     * @Security("user == schedule.getUser()")
     */
    public function scheduleDetailsAction(Schedule $schedule, Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            if (isset($data['disable'])) {
                $this->get('schedule_manager')->disable($schedule);
            } else if (isset($data['enable'])) {
                $this->get('schedule_manager')->enable($schedule);
            } else if (isset($data['delete'])) {
                if (!$schedule->getEnabled()) {
                    $this->get('schedule_manager')->delete($schedule);
                    $this->get('session')->getFlashBag()->add('success', array('title' => 'Success', 'message' => 'Schedule has been deleted'));
                    return $this->redirectToRoute("_schedule_list");
                }
            }
            return $this->redirectToRoute("_schedule_details", ['schedule' => $schedule->getId()]);
        }
        return [
            'schedule' => $schedule,
            'closestExecutions' => $this->get('schedule_manager')->findClosestExecutions($schedule)
        ];
    }
}
