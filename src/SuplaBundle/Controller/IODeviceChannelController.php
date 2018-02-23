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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SuplaBundle\Entity\IODeviceChannel;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @Route("/channels")
 */
class IODeviceChannelController extends AbstractController {
    /**
     * @Route("/{channel}", name="_iodevice_channel_details")
     * @Template
     */
    public function channelDetailsAction(IODeviceChannel $channel) {
        return ['channel' => $channel];
    }

    /**
     * @Route("/{channel}/csv", name="_iodev_channel_item_csv")
     * @Security("channel.belongsToUser(user)")
     */
    public function channelItemGetCSV(IODeviceChannel $channel) {
        $file = $this->get('iodevice_manager')->channelGetCSV($channel, "measurement_" . $channel->getId());
        if ($file !== false) {
            return new StreamedResponse(
                function () use ($file) {
                    readfile($file);
                    unlink($file);
                },
                200,
                [
                    'Content-Type' => 'application/zip',
                    'Content-Disposition' => 'attachment; filename="measurement_' . $channel->getId() . '.zip"',
                ]
            );
        }
        $this->get('session')->getFlashBag()->add('error', ['title' => 'Error', 'message' => 'Error creating file']);
        return $this->redirectToRoute("_iodevice_channel_details", ['channel' => $channel->getId()]);
    }
}
