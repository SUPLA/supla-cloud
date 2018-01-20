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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Form\Type\ChangeLocationType;
use SuplaBundle\Form\Type\IODeviceChannelType;
use SuplaBundle\Supla\SuplaConst;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @Route("/iodev")
 */
class IODeviceController extends AbstractController {
    use SuplaServerAware;

    private function userReconnect() {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $this->suplaServer->reconnect($user->getId());
    }

    private function getIODeviceById($id) {
        $iodev_man = $this->get('iodevice_manager');
        return $iodev_man->ioDeviceById($id);
    }

    private function getChannelById($id) {
        $iodev_man = $this->get('iodevice_manager');
        return $iodev_man->channelById($id);
    }

    /**
     * @Route("", name="_iodev_list")
     * @Template
     */
    public function listAction() {
        return [];
    }

    /**
     * @Route("/{id}/view", name="_iodev_item")
     */
    public function itemAction($id) {

        $iodev = $this->getIODeviceById($id);

        if ($iodev === null) {
            return $this->redirectToRoute("_iodev_list");
        }

        $dev_man = $this->get('iodevice_manager');

        $loc = $iodev->getLocation();

        $loc_name = 'Id ' . $loc->getId();

        if (strlen($loc->getCaption()) > 0) {
            $loc_name .= " [" . $loc->getCaption() . "]";
        }

        $aid_enabled = false;

        for ($a = 0; $a < $iodev->getLocation()->getAccessIds()->count(); $a++) {
            $aid = $iodev->getLocation()->getAccessIds()->get($a);
            if ($aid->getEnabled()) {
                $aid_enabled = true;
                break;
            }
        }

        return $this->render(
            'SuplaBundle:IODevice:iodevice.html.twig',
            ['iodevice' => $iodev,
                'location_name' => $loc_name,
                'channels' => $dev_man->channelsToArray($dev_man->getChannels($iodev)),
                'aid_enabled' => $aid_enabled,
            ]
        );
    }

    /**
     * @Route("/{id}/delete", name="_iodev_item_delete")
     */
    public function itemDeleteAction($id) {
        $iodev = $this->getIODeviceById($id);

        if ($iodev === null) {
            return $this->redirectToRoute("_iodev_list");
        }

        $dev_man = $this->get('iodevice_manager');
        $m = $this->get('doctrine')->getManager();

        $channels = $dev_man->getChannels($iodev);

        foreach ($channels as $channel) {
            switch ($channel->getType()) {
                case SuplaConst::TYPE_SENSORNO:
                case SuplaConst::TYPE_SENSORNC:
                    if ($channel->getParam1() != 0) {
                        $related_channel = $this->getChannelById($channel->getParam1());

                        if ($related_channel != null
                            && $related_channel->getFunction() != SuplaConst::FNC_NONE
                            && $related_channel->getParam2() == $channel->getId()
                        ) {
                            $related_channel->setParam2(0);
                        }
                    }
                    break;
                case SuplaConst::TYPE_RELAY:
                case SuplaConst::TYPE_RELAYHFD4:
                case SuplaConst::TYPE_RELAYG5LA1A:
                case SuplaConst::TYPE_RELAY2XG5LA1A:
                    if ($channel->getParam2() != 0) {
                        $sensor = $this->getChannelById($channel->getParam2());
                        if ($sensor !== null) {
                            $sensor->setParam1(0);
                        }
                    }
            }

            $m->remove($channel);
        }

        $m->remove($iodev);
        $m->flush();

        $this->userReconnect();

        $this->get('session')->getFlashBag()->add('warning', ['title' => 'Information', 'message' => 'I/O Device has been deleted']);
        return $this->redirectToRoute("_iodev_list");
    }

    /**
     * @Route("/{devid}/{id}/edit", name="_iodev_channel_item_edit")
     */
    public function channelItemEditAction(Request $request, $devid, $id) {

        $channel = $this->getChannelById($id);

        if ($channel === null || $channel->getIoDevice()->getId() != $devid) {
            return $this->redirectToRoute("_iodev_list");
        }

        $dev_man = $this->get('iodevice_manager');

        $form = $this->createForm(
            IODeviceChannelType::class,
            $channel,
            ['cancel_url' => $this->generateUrl('_iodev_item', ['id' => $devid])]
        );

        $old_function = $channel->getFunction()->getId();
        $old_param1 = $channel->getParam1();
        $old_param2 = $channel->getParam2();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($channel->getType()->getId()) {
                case SuplaConst::TYPE_SENSORNO:
                case SuplaConst::TYPE_SENSORNC:
                    if ($channel->getFunction()->getId() == SuplaConst::FNC_NONE
                        || $old_param1 != $channel->getParam1()
                    ) {
                        if ($old_param1 != 0) {
                            $related_channel = $this->getChannelById($old_param1);

                            if ($related_channel != null
                                && $related_channel->getFunction() != SuplaConst::FNC_NONE
                                && $related_channel->getParam2() == $channel->getId()
                            ) {
                                $related_channel->setParam2(0);
                            }
                        }
                    };

                    if (($channel->getFunction()->getId() == SuplaConst::FNC_OPENINGSENSOR_GATEWAY
                            || $channel->getFunction()->getId() == SuplaConst::FNC_OPENINGSENSOR_GATE
                            || $channel->getFunction()->getId() == SuplaConst::FNC_OPENINGSENSOR_GARAGEDOOR
                            || $channel->getFunction()->getId() == SuplaConst::FNC_OPENINGSENSOR_DOOR
                            || $channel->getFunction()->getId() == SuplaConst::FNC_OPENINGSENSOR_ROLLERSHUTTER)
                        && $old_param1 != $channel->getParam1()
                        && $channel->getParam1() != 0
                    ) {
                        $related_channel = $this->getChannelById($channel->getParam1());

                        if ($related_channel != null
                            && $related_channel->getFunction()->getValue() != SuplaConst::FNC_NONE
                        ) {
                            $related_channel->setParam2($channel->getId());
                        }
                    }

                    break;

                case SuplaConst::TYPE_RELAY:
                case SuplaConst::TYPE_RELAYHFD4:
                case SuplaConst::TYPE_RELAYG5LA1A:
                case SuplaConst::TYPE_RELAY2XG5LA1A:
                    if ($channel->getFunction()->getId() == SuplaConst::FNC_NONE
                        || $old_param2 != $channel->getParam2()
                    ) {
                        if ($old_param2 != 0) {
                            $related_sensor = $this->getChannelById($old_param2);

                            if ($related_sensor !== null) {
                                $related_sensor->setParam1(0);
                            }
                        }
                    };

                    if ($channel->getFunction()->getId() != SuplaConst::FNC_NONE
                        && $old_param2 != $channel->getParam2()
                        && $channel->getParam2() != 0
                    ) {
                        $related_sensor = $this->getChannelById($channel->getParam2());
                        $related_sensor->setParam1($channel->getId());
                    }

                    break;
            }

            if ($channel->getFunction()->getId() == SuplaConst::FNC_STAIRCASETIMER) {
                if ($channel->getParam1() < 0
                    || $channel->getParam1() > 360000 // 60 min.
                ) {
                    $channel->setParam1(0);
                }
            } elseif ($channel->getFunction()->getId() == SuplaConst::FNC_CONTROLLINGTHEROLLERSHUTTER) {
                if ($channel->getParam1() < 0
                    || $channel->getParam1() > 3000 // 5 min.
                ) {
                    $channel->setParam1(0);
                }

                if ($channel->getParam3() < 0
                    || $channel->getParam3() > 3000 // 5 min.
                ) {
                    $channel->setParam3(0);
                }
            }

            if ($old_function != $channel->getFunction()->getId()) {
                foreach ($channel->getSchedules() as $schedule) {
                    $this->get('schedule_manager')->delete($schedule);
                }
            }

            if ($dev_man->channelFunctionAltIconMax($channel->getFunction()->getId()) < $channel->getAltIcon()) {
                $channel->setAltIcon(0);
            }

            $this->get('doctrine')->getManager()->flush();
            $this->get('session')->getFlashBag()->add('success', ['title' => 'Success', 'message' => 'Data saved!']);

            $this->userReconnect();

            return $this->redirectToRoute("_iodev_item", ['id' => $devid]);
        }

        $channelType = $channel->getType();

        return $this->render(
            'SuplaBundle:IODevice:channeledit.html.twig',
            ['channel' => $channel,
                'channel_type' => $dev_man->channelTypeToString($channel->getType()),
                'channel_function' => $channel->getFunction(),
                'channel_function_name' => $dev_man->channelFunctionToString($channel->getFunction()),
                'alticon_max' => $dev_man->channelFunctionAltIconMax($channel->getFunction()),
                'form' => $form->createView(),
                'show_sensorstate' => in_array($channelType->getId(), [ChannelType::SENSORNO, ChannelType::SENSORNC]) ? true : false,
                'show_temperature' => $channelType->getId() == ChannelType::THERMOMETERDS18B20 ? true : false,
                'show_temphumidity' => in_array($channelType->getId(), [
                    ChannelType::DHT11, ChannelType::DHT21, ChannelType::DHT22, ChannelType::AM2301, ChannelType::AM2302]) ? true : false,
                'show_distance' => $channelType->getId() == ChannelType::DISTANCESENSOR ? true : false,
            ]
        );
    }

    /**
     * @Route("/{devid}/{id}/csv", name="_iodev_channel_item_csv")
     */
    public function channelItemGetCSV(Request $request, $devid, $id) {

        $channel = $this->getChannelById($id);

        if ($channel === null || $channel->getIoDevice()->getId() != $devid) {
            return $this->redirectToRoute("_iodev_list");
        }

        $iodev_man = $this->get('iodevice_manager');
        $file = $iodev_man->channelGetCSV($channel, "measurement_" . intval($id));

        if ($file !== false) {
            return new StreamedResponse(
                function () use ($file) {
                    readfile($file);
                    unlink($file);
                },
                200,
                ['Content-Type' => 'application/zip',
                    'Content-Disposition' => 'attachment; filename="measurement_' . intval($id) . '.zip"',
                ]
            );
        }

        $this->get('session')->getFlashBag()->add('error', ['title' => 'Error', 'message' => 'Error creating file']);

        return $this->redirectToRoute("_iodev_channel_item_edit", ['devid' => $devid, 'id' => $id]);
    }

    /**
     * @Route("/{id}/change_location", name="_iodev_change_loc")
     */
    public function changeLocationAction(Request $request, $id) {
        $iodev = $this->getIODeviceById($id);

        if ($iodev != null) {
            $form = $this->createForm(ChangeLocationType::class, null, []);
            $form->handleRequest($request);

            if ($form->isSubmitted()
                && $form->isValid()
                && ($new_id = intval(@$request->request->get('selected_id'))) != $iodev->getLocation()->getId()
            ) {
                $loc_man = $this->get('location_manager');
                $new_location = $loc_man->locationById($new_id);

                if ($new_location !== null) {
                    $iodev->setLocation($new_location);

                    $this->get('doctrine')->getManager()->flush();
                    $this->get('session')->getFlashBag()->add('success', ['title' => 'Success', 'message' => 'Location has been changed']);

                    $this->userReconnect();
                }
            }
        }

        return $this->redirectToRoute("_iodev_item", ['id' => $id]);
    }

    private function ajaxItemEdit(IODevice $iodev, $message, $value) {
        $result = AjaxController
            ::itemEdit($this->get('validator'), $this->get('translator'), $this->get('doctrine'), $iodev, $message, $value);
        $this->userReconnect();
        return $result;
    }

    /**
     * @Route("/{id}/ajax/setcomment", name="_iodev_ajax_setcomment")
     */
    public function ajaxSetCaption(Request $request, $id) {
        $iodev = $this->getIODeviceById($id);

        if ($iodev !== null) {
            $data = json_decode($request->getContent());
            $iodev->setComment($data->value);
        }

        return $this->ajaxItemEdit(
            $iodev,
            'Comment has been modified',
            null
        );
    }

    /**
     * @Route("/ajax/getfuncparams/{channel_id}/{function}", name="_iodev_ajax_getfuncparams")
     */
    public function ajaxGetfuncparamsAction($channel_id, $function) {

        $dev_man = $this->get('iodevice_manager');
        $html = $dev_man->channelFunctionParamsHtmlTemplate($channel_id, $function);

        return AjaxController::jsonResponse($html !== null, ['html' => $html]);
    }

    /**
     * @Route("/{id}/ajax/change_loc", name="_iodev_ajax_change_loc")
     */
    public function ajaxChangeLocGetList(Request $request, $id) {
        $html = null;

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $iodev = $this->getIODeviceById($id);

        if ($user !== null && $iodev !== null) {
            $form = $this->createForm(
                ChangeLocationType::class,
                null,
                ['action' => $this->generateUrl('_iodev_change_loc', ['id' => $id]),
                ]
            );

            $html = $this->get('templating')->render(
                'SuplaBundle:IODevice:changelocation.html.twig',
                ['form' => $form->createView(),
                    'locations' => $user->getLocations(),
                    'selected_id' => $iodev->getLocation() !== null ? $iodev->getLocation()->getId() : 0,
                ]
            );
        }

        return AjaxController::jsonResponse($html !== null, ['html' => $html]);
    }
}
