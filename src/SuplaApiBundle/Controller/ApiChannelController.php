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

use Assert\Assertion;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaApiBundle\Model\ApiVersions;
use SuplaApiBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaApiBundle\Model\ChannelParamsUpdater\ChannelParamsUpdater;
use SuplaApiBundle\Model\ChannelStateGetter\ChannelStateGetter;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiChannelController extends RestController {
    use SuplaServerAware;
    use Transactional;

    /** @var ChannelParamsUpdater */
    private $channelParamsUpdater;
    /** @var ChannelStateGetter */
    private $channelStateGetter;
    /** @var ChannelActionExecutor */
    private $channelActionExecutor;

    public function __construct(
        ChannelParamsUpdater $channelParamsUpdater,
        ChannelStateGetter $channelStateGetter,
        ChannelActionExecutor $channelActionExecutor
    ) {
        $this->channelParamsUpdater = $channelParamsUpdater;
        $this->channelStateGetter = $channelStateGetter;
        $this->channelActionExecutor = $channelActionExecutor;
    }

    public function getChannelsAction(Request $request) {
        $criteria = Criteria::create();
        if (($function = $request->get('function')) !== null) {
            $functionIds = array_map(function ($fnc) {
                return ChannelFunction::isValidKey($fnc)
                    ? ChannelFunction::$fnc()->getValue()
                    : (new ChannelFunction((int)$fnc))->getValue();
            }, explode(',', $function));
            $criteria->andWhere(Criteria::expr()->in('function', $functionIds));
        }
        if (($io = $request->get('io')) !== null) {
            Assertion::inArray($io, ['input', 'output']);
            $criteria->andWhere(
                Criteria::expr()->in('type', $io == 'output'
                    ? ChannelType::outputTypes()
                    : ChannelType::inputTypes())
            );
        }
        if (($hasFunction = $request->get('hasFunction')) !== null) {
            if ($hasFunction) {
                $criteria->andWhere(Criteria::expr()->neq('function', ChannelFunction::NONE));
            } else {
                $criteria->andWhere(Criteria::expr()->eq('function', ChannelFunction::NONE));
            }
        }
        $channels = $this->getCurrentUser()->getChannels()->matching($criteria);
        $view = $this->view($channels, Response::HTTP_OK);
        $this->setSerializationGroups($view, $request, ['iodevice', 'location']);
        return $view;
    }

    /**
     * @Security("channel.belongsToUser(user)")
     */
    public function getChannelAction(Request $request, IODeviceChannel $channel) {
        if (ApiVersions::V2_2()->isRequestedEqualOrGreaterThan($request)) {
            $view = $this->view($channel, Response::HTTP_OK);
            $this->setSerializationGroups(
                $view,
                $request,
                ['iodevice', 'location', 'connected', 'state', 'supportedFunctions', 'measurementLogsCount']
            );
            return $view;
        } else {
            $enabled = false;
            $connected = false;
            $devid = $channel->getIoDevice()->getId();
            $userid = $this->getUser()->getId();
            if ($channel->getIoDevice()->getEnabled()) {
                $enabled = true;
                $cids = $this->suplaServer->checkDevicesConnection($userid, [$devid]);
                $connected = in_array($devid, $cids);
            }
            $result = array_merge(['connected' => $connected, 'enabled' => $enabled], $this->channelStateGetter->getState($channel));
            return $this->handleView($this->view($result, Response::HTTP_OK));
        }
    }

    /**
     * @Security("channel.belongsToUser(user)")
     */
    public function putChannelAction(Request $request, IODeviceChannel $channel, IODeviceChannel $updatedChannel) {
        if (ApiVersions::V2_2()->isRequestedEqualOrGreaterThan($request)) {
            $functionHasBeenChanged = $channel->getFunction() != $updatedChannel->getFunction();
            if ($functionHasBeenChanged) {
                if (!$request->get('confirm') && (count($channel->getSchedules()) || count($channel->getChannelGroups()))) {
                    return $this->view([
                        'schedules' => $channel->getSchedules(),
                        'groups' => $channel->getChannelGroups(),
                    ], Response::HTTP_CONFLICT);
                }
                $channel->setFunction($updatedChannel->getFunction());
            } else {
                $channel->setAltIcon($updatedChannel->getAltIcon());
            }
            if ($updatedChannel->hasInheritedLocation()) {
                $channel->setLocation(null);
            } else {
                $channel->setLocation($updatedChannel->getLocation());
            }
            $channel->setCaption($updatedChannel->getCaption());
            $channel->setHidden($updatedChannel->getHidden());
            $this->channelParamsUpdater->updateChannelParams($channel, $updatedChannel);
            return $this->transactional(function (EntityManagerInterface $em) use ($functionHasBeenChanged, $request, $channel) {
                $em->persist($channel);
                if ($functionHasBeenChanged) {
                    foreach ($channel->getSchedules() as $schedule) {
                        $this->get('schedule_manager')->delete($schedule);
                    }
                    $channel->removeFromAllChannelGroups($em);
                }
                $this->suplaServer->reconnect();
                return $this->getChannelAction($request, $channel);
            });
        } else {
            $data = json_decode($request->getContent(), true);
            $this->channelActionExecutor->executeAction($channel, ChannelFunctionAction::SET_RGBW_PARAMETERS(), $data);
            return $this->handleView($this->view(null, Response::HTTP_OK));
        }
    }

    /**
     * @Security("channel.belongsToUser(user)")
     */
    public function patchChannelAction(Request $request, IODeviceChannel $channel) {
        $params = json_decode($request->getContent(), true);
        Assertion::keyExists($params, 'action', 'Missing action.');
        $action = ChannelFunctionAction::fromString($params['action']);
        unset($params['action']);
        $this->channelActionExecutor->executeAction($channel, $action, $params);
        return $this->handleView($this->view(null, Response::HTTP_NO_CONTENT));
    }
}
