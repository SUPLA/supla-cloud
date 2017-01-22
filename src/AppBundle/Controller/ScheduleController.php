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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
        $user = $this->getUser();
        return [
            'a' => 'b'
        ];
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
            'scheduleModes' => [
                'once' => 'Jednorazowy',
                'minutely' => 'Cykl minutowy',
                'hourly' => 'Cykl godzinny',
                'daily' => 'Codziennie',
            ],
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
        return new JsonResponse();
    }

    /**
     * @Route("/next-run-dates/{cronExpression}", name="_schedule_get_run_dates", requirements={"cronExpression"=".+"})
     */
    public function getNextRunDatesAction($cronExpression)
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }
        $temporarySchedule = new Schedule();
        $temporarySchedule->setCronExpression($cronExpression);
        $nextRunDates = $temporarySchedule->getRunDatesUntil('+7days', 'now', 3);
        return new JsonResponse([
            'nextRunDates' => array_map(function ($dateTime) {
                return $dateTime->format(\DateTime::ATOM);
            }, $nextRunDates),
            'now' => (new \DateTime())->format(\DateTime::ATOM),
        ]);
    }
}
