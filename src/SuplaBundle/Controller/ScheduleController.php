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

namespace SuplaBundle\Controller;

use Assert\Assertion;
use Assert\InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SuplaBundle\Entity\Schedule;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/schedules")
 */
class ScheduleController extends AbstractController {
    /**
     * @Route("", methods={"GET"}, name="_schedule_list")
     * @Template
     */
    public function scheduleListAction() {
        return [];
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
        return [
            'schedule' => $schedule,
            'closestExecutions' => $this->get('schedule_manager')->findClosestExecutions($schedule),
        ];
    }
}
