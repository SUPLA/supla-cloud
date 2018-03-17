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
use SuplaApiBundle\Model\ChannelParamsUpdater\ChannelParamsUpdater;
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Form\Type\ChangeLocationType;
use SuplaBundle\Form\Type\IODeviceChannelType;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/iodev")
 */
class IODeviceController extends AbstractController {
    use SuplaServerAware;

    /** @var ChannelParamsUpdater */
    private $channelParamsUpdater;

    public function __construct(ChannelParamsUpdater $channelParamsUpdater) {
        $this->channelParamsUpdater = $channelParamsUpdater;
    }

    private function userReconnect() {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $this->suplaServer->reconnect($user->getId());
    }

    private function getIODeviceById($id) {
        $iodev_man = $this->get('iodevice_manager');
        return $iodev_man->ioDeviceById($id);
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
            // clears all paired channels that are possibly made with the one that is being deleted
            $this->channelParamsUpdater->updateChannelParams($channel, new IODeviceChannel());
            $m->remove($channel);
        }

        $m->remove($iodev);
        $m->flush();

        $this->userReconnect();

        $this->get('session')->getFlashBag()->add('warning', ['title' => 'Information', 'message' => 'I/O Device has been deleted']);
        return $this->redirectToRoute("_iodev_list");
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
