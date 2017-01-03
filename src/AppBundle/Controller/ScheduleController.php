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


use AppBundle\Supla\SuplaConst;
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
        return [
            'scheduleModes' => [
                'once' => 'Jednorazowy',
                'minutely' => 'Cykl minutowy',
                'hourly' => 'Cykl godzinny',
                'daily' => 'Codziennie',
            ],
            'locations' => [
                [
                    'name' => 'Salon',
                    'id' => '455',
                    'devices' => [
                        [
                            'name' => 'SONOFF-DS18B20',
                            'id' => 123,
                            'channels' => [
                                [
                                    'name' => 'Włącznik światła',
                                    'function' => SuplaConst::FNC_LIGHTSWITCH,
                                    'caption' => 'Lampka nocna',
                                    'actions' => ['Włącz', 'Wyłącz']
                                ]
                            ],
                        ],
                        [
                            'name' => 'RGB-SWITCH',
                            'id' => '333',
                            'channels' => [
                                [
                                    'name' => 'Ściemniacz',
                                    'function' => SuplaConst::FNC_DIMMERANDRGBLIGHTING,
                                    'caption' => 'Podświetlenie kanapy',
                                    'actions' => ['Ściemnij', 'Rozjaśnij']
                                ],
                                [
                                    'name' => 'Barwa',
                                    'function' => SuplaConst::FNC_RGBLIGHTING,
                                    'caption' => 'Podświetlenie kanapy',
                                    'actions' => ['Zmień na zielony', 'Zmień na żółty', 'Zmień na losowy']
                                ]
                            ]
                        ]
                    ],
                ],
                [
                    'name' => 'Na zewnątrz',
                    'id' => 567,
                    'devices' => [
                        [
                            'name' => 'SUPLA-GATE-MODULE',
                            'id' => '455',
                            'channels' => [
                                [
                                    'name' => 'Otwieranie furtki',
                                    'function' => SuplaConst::FNC_CONTROLLINGTHEGATEWAYLOCK,
                                    'caption' => 'Furtka w ogrodzeniu',
                                    'actions' => ['Otwórz', 'Zamknij']
                                ],
                                [
                                    'name' => 'Otwieranie bramy garażowej',
                                    'function' => SuplaConst::FNC_CONTROLLINGTHEGATE,
                                    'caption' => 'Garaż na zewnątrz',
                                    'actions' => ['Otwórz', 'Zamknij']
                                ]
                            ]
                        ]
                    ]

                ]
            ],
        ];
    }
}
