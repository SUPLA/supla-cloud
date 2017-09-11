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

use FOS\RestBundle\Controller\Annotations as Rest;
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Supla\SuplaServerReal;
use SuplaBundle\Supla\SuplaConst;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiIODeviceController extends RestController {

    protected function ioDeviceById($devid) {

        $devid = intval($devid, 0);
        $iodev_man = $this->container->get('iodevice_manager');

        $iodevice = $iodev_man->ioDeviceById($devid, $this->getParentUser());

        if (!($iodevice instanceof IODevice)) {
            throw new HttpException(Response::HTTP_NOT_FOUND);
        }

        return $iodevice;
    }

    protected function getIODevices() {

        $result = [];
        $parent = $this->getParentUser();

        if ($parent !== null) {
            $iodev_man = $this->container->get('iodevice_manager');

            foreach ($parent->getIODevices() as $device) {
                $channels = [];

                foreach ($iodev_man->getChannels($device) as $channel) {
                    $channels[] = [
                        'id' => $channel->getId(),
                        'chnnel_number' => $channel->getChannelNumber(),
                        'caption' => $channel->getCaption(),
                        'type' => ['name' => SuplaConst::typeStr[$channel->getType()],
                            'id' => $channel->getType()],
                        'function' => ['name' => SuplaConst::fncStr[$channel->getFunction()],
                            'id' => $channel->getFunction()],
                    ];
                }

                $result[] = [
                    'id' => $device->getId(),
                    'location_id' => $device->getLocation()->getId(),
                    'enabled' => $device->getEnabled(),
                    'name' => $device->getName(),
                    'comment' => $device->getComment(),
                    'registration' => ['date' => $device->getRegDate()->getTimestamp(),
                        'ip_v4' => long2ip($device->getRegIpv4())],

                    'last_connected' => ['date' => $device->getLastConnected()->getTimestamp(),
                        'ip_v4' => long2ip($device->getLastIpv4())],
                    'guid' => $device->getGUIDString(),
                    'software_version' => $device->getSoftwareVersion(),
                    'protocol_version' => $device->getProtocolVersion(),
                    'channels' => $channels,
                ];
            }
        }

        return ['iodevices' => $result];
    }

    /**
     * @Rest\Get("/iodevices")
     */
    public function getIOdevicesAction(Request $request) {
        return $this->handleView($this->view($this->getIODevices(), Response::HTTP_OK));
    }

    /**
     * @Rest\Get("/iodevices/{devid}")
     */
    public function getIOdeviceAction(Request $request, $devid) {
        $iodevice = $this->ioDeviceById($devid);

        $enabled = false;
        $connected = false;

        $iodev_man = $this->container->get('iodevice_manager');
        
        if ($iodevice->getEnabled()) {
            $enabled = true;
            $cids = (new SuplaServerReal())->checkDevicesConnection($this->getParentUser()->getId(), [$devid]);
            $connected = in_array($devid, $cids);
        }


        $channels = [];
        
        foreach ($iodev_man->getChannels($iodevice) as $channel) {
            $channels[] = [
                    'id' => $channel->getId(),
                    'chnnel_number' => $channel->getChannelNumber(),
                    'caption' => $channel->getCaption(),
                    'type' => ['name' => SuplaConst::typeStr[$channel->getType()],
                            'id' => $channel->getType()],
                    'function' => ['name' => SuplaConst::fncStr[$channel->getFunction()],
                            'id' => $channel->getFunction()],
            ];
        }
        
        $result[] = [
                'id' => $iodevice->getId(),
                'location_id' => $iodevice->getLocation()->getId(),
                'enabled' => $enabled,
                'connected' => $connected,
                'name' => $iodevice->getName(),
                'comment' => $iodevice->getComment(),
                'registration' => ['date' => $iodevice->getRegDate()->getTimestamp(),
                        'ip_v4' => long2ip($iodevice->getRegIpv4())],
                
                'last_connected' => ['date' => $iodevice->getLastConnected()->getTimestamp(),
                        'ip_v4' => long2ip($iodevice->getLastIpv4())],
                'guid' => $iodevice->getGUIDString(),
                'software_version' => $iodevice->getSoftwareVersion(),
                'protocol_version' => $iodevice->getProtocolVersion(),
                'channels' => $channels,
        ];


        return $this->handleView($this->view($result, Response::HTTP_OK));
    }
}
