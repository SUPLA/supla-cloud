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

namespace SuplaBundle\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaApiBundle\Model\ApiVersions;
use SuplaApiBundle\Model\ChannelParamsUpdater\ChannelParamsUpdater;
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Model\Schedule\ScheduleManager;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IODeviceController extends RestController {
    use SuplaServerAware;
    use Transactional;

    /** @var ChannelParamsUpdater */
    private $channelParamsUpdater;
    /** @var ScheduleManager */
    private $scheduleManager;

    public function __construct(ChannelParamsUpdater $channelParamsUpdater, ScheduleManager $scheduleManager) {
        $this->channelParamsUpdater = $channelParamsUpdater;
        $this->scheduleManager = $scheduleManager;
    }

    /** @Security("has_role('ROLE_IODEVICES_R')") */
    public function getIodevicesAction(Request $request) {
        $result = [];
        $user = $this->getUser();
        if (ApiVersions::V2_2()->isRequestedEqualOrGreaterThan($request)) {
            $result = $user->getIODevices();
        } else {
            if ($user !== null) {
                foreach ($user->getIODevices() as $device) {
                    $channels = [];
                    foreach ($device->getChannels() as $channel) {
                        $channels[] = [
                            'id' => $channel->getId(),
                            'chnnel_number' => $channel->getChannelNumber(),
                            'caption' => $channel->getCaption(),
                            'type' => [
                                'name' => 'TYPE_' . $channel->getType()->getName(),
                                'id' => $channel->getType()->getId(),
                            ],
                            'function' => [
                                'name' => 'FNC_' . $channel->getFunction()->getName(),
                                'id' => $channel->getFunction()->getId(),
                            ],
                        ];
                    }
                    $result[] = [
                        'id' => $device->getId(),
                        'location_id' => $device->getLocation()->getId(),
                        'enabled' => $device->getEnabled(),
                        'name' => $device->getName(),
                        'comment' => $device->getComment(),
                        'registration' => [
                            'date' => $device->getRegDate()->getTimestamp(),
                            'ip_v4' => long2ip($device->getRegIpv4()),
                        ],
                        'last_connected' => [
                            'date' => $device->getLastConnected()->getTimestamp(),
                            'ip_v4' => long2ip($device->getLastIpv4()),
                        ],
                        'guid' => $device->getGUIDString(),
                        'software_version' => $device->getSoftwareVersion(),
                        'protocol_version' => $device->getProtocolVersion(),
                        'channels' => $channels,
                    ];
                }
            }
            $result = ['iodevices' => $result];
        }
        $view = $this->view($result, Response::HTTP_OK);
        $this->setSerializationGroups($view, $request, ['channels', 'location', 'originalLocation', 'connected', 'schedules']);
        return $view;
    }

    /**
     * @Security("ioDevice.belongsToUser(user) and has_role('ROLE_IODEVICES_R')")
     */
    public function getIodeviceAction(Request $request, IODevice $ioDevice) {
        if (ApiVersions::V2_2()->isRequestedEqualOrGreaterThan($request)) {
            $result = $ioDevice;
        } else {
            $enabled = false;
            $connected = false;

            if ($ioDevice->getEnabled()) {
                $enabled = true;
                $cids = $this->suplaServer->checkDevicesConnection($this->getUser()->getId(), [$ioDevice->getId()]);
                $connected = in_array($ioDevice->getId(), $cids);
            }

            $channels = [];

            foreach ($ioDevice->getChannels() as $channel) {
                $channels[] = [
                    'id' => $channel->getId(),
                    'chnnel_number' => $channel->getChannelNumber(),
                    'caption' => $channel->getCaption(),
                    'type' => [
                        'name' => 'TYPE_' . $channel->getType()->getName(),
                        'id' => $channel->getType()->getId(),
                    ],
                    'function' => [
                        'name' => 'FNC_' . $channel->getFunction()->getName(),
                        'id' => $channel->getFunction()->getId(),
                    ],
                ];
            }

            $result[] = [
                'id' => $ioDevice->getId(),
                'location_id' => $ioDevice->getLocation()->getId(),
                'enabled' => $enabled,
                'connected' => $connected,
                'name' => $ioDevice->getName(),
                'comment' => $ioDevice->getComment(),
                'registration' => [
                    'date' => $ioDevice->getRegDate()->getTimestamp(),
                    'ip_v4' => long2ip($ioDevice->getRegIpv4()),
                ],
                'last_connected' => [
                    'date' => $ioDevice->getLastConnected()->getTimestamp(),
                    'ip_v4' => long2ip($ioDevice->getLastIpv4()),
                ],
                'guid' => $ioDevice->getGUIDString(),
                'software_version' => $ioDevice->getSoftwareVersion(),
                'protocol_version' => $ioDevice->getProtocolVersion(),
                'channels' => $channels,
            ];
        }

        $view = $this->view($result, Response::HTTP_OK);
        $this->setSerializationGroups($view, $request, ['channels', 'location', 'originalLocation', 'connected', 'schedules', 'accessids']);
        return $view;
    }

    /**
     * @Security("ioDevice.belongsToUser(user) and has_role('ROLE_IODEVICES_RW')")
     */
    public function putIodeviceAction(Request $request, IODevice $ioDevice, IODevice $updatedDevice) {
        return $this->transactional(function (EntityManagerInterface $em) use ($request, $ioDevice, $updatedDevice) {
            $enabledChanged = $ioDevice->getEnabled() != $updatedDevice->getEnabled();
            if ($enabledChanged) {
                $schedules = $this->scheduleManager->findSchedulesForDevice($ioDevice);
                if (!$updatedDevice->getEnabled() && !($request->get('confirm', false))) {
                    $enabledSchedules = $this->scheduleManager->onlyEnabled($schedules);
                    if (count($enabledSchedules)) {
                        $view = $this->view($ioDevice, Response::HTTP_CONFLICT);
                        $this->setSerializationGroups($view, $request, ['schedules'], ['schedules']);
                        return $view;
                    }
                }
                $ioDevice->setEnabled($updatedDevice->getEnabled());
                if (!$ioDevice->getEnabled()) {
                    $this->get('schedule_manager')->disableSchedulesForDevice($ioDevice);
                }
            }
            $ioDevice->setLocation($updatedDevice->getLocation());
            $ioDevice->setComment($updatedDevice->getComment());
            $this->suplaServer->reconnect();
            $view = $this->view($ioDevice, Response::HTTP_OK);
            $this->setSerializationGroups($view, $request, ['schedules'], ['schedules']);
            return $view;
        });
    }

    /**
     * @Security("ioDevice.belongsToUser(user) and has_role('ROLE_IODEVICES_RW')")
     */
    public function deleteIodeviceAction(IODevice $ioDevice) {
        $this->transactional(function (EntityManagerInterface $em) use ($ioDevice) {
            foreach ($ioDevice->getChannels() as $channel) {
                // clears all paired channels that are possibly made with the one that is being deleted
                $this->channelParamsUpdater->updateChannelParams($channel, new IODeviceChannel());
                $channel->removeFromAllChannelGroups($em);
            }

            foreach ($ioDevice->getChannels() as $channel) {
                $em->remove($channel);
            }
            $em->remove($ioDevice);
        });
        $this->suplaServer->reconnect();
        return new Response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @Security("ioDevice.belongsToUser(user) and has_role('ROLE_CHANNELS_R')")
     */
    public function getIodeviceChannelsAction(Request $request, IODevice $ioDevice) {
        $channels = $ioDevice->getChannels();
        $view = $this->view($channels, Response::HTTP_OK);
        $this->setSerializationGroups($view, $request, ['iodevice', 'location']);
        return $view;
    }
}
