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


use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
        return [
            'scheduleModes' => [
                'once' => 'Jednorazowy',
                'minutely' => 'Cykl minutowy',
                'hourly' => 'Cykl godzinny',
                'daily' => 'Codziennie',
            ],
            'userChannels' => $channels,
        ];
    }
}
