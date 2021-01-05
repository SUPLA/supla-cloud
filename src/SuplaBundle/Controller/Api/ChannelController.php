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

use Assert\Assertion;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SuplaBundle\Auth\Voter\AccessIdSecurityVoter;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\EventListener\UnavailableInMaintenance;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsUpdater;
use SuplaBundle\Model\ChannelStateGetter\ChannelStateGetter;
use SuplaBundle\Model\Schedule\ScheduleManager;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ChannelController extends RestController {
    use SuplaServerAware;
    use Transactional;

    /** @var ChannelParamsUpdater */
    private $channelParamsUpdater;
    /** @var ChannelStateGetter */
    private $channelStateGetter;
    /** @var ChannelActionExecutor */
    private $channelActionExecutor;
    /** @var ScheduleManager */
    private $scheduleManager;

    public function __construct(
        ChannelParamsUpdater $channelParamsUpdater,
        ChannelStateGetter $channelStateGetter,
        ChannelActionExecutor $channelActionExecutor,
        ScheduleManager $scheduleManager
    ) {
        $this->channelParamsUpdater = $channelParamsUpdater;
        $this->channelStateGetter = $channelStateGetter;
        $this->channelActionExecutor = $channelActionExecutor;
        $this->scheduleManager = $scheduleManager;
    }

    protected function getDefaultAllowedSerializationGroups(Request $request): array {
        $groups = [
            'iodevice', 'location', 'connected', 'state', 'supportedFunctions', 'relationsCount',
            'location' => 'channel.location',
            'iodevice' => 'channel.iodevice',
            'relationsCount' => 'channel.relationsCount',
        ];
        if (!strpos($request->get('_route'), 'channels_list')) {
            $groups[] = 'iodevice.location';
        }
        return $groups;
    }

    /**
     * @Rest\Get(name="channels_list")
     * @Security("has_role('ROLE_CHANNELS_R')")
     */
    public function getChannelsAction(Request $request) {
        $criteria = Criteria::create();
        if (($function = $request->get('function')) !== null) {
            $functionIds = EntityUtils::mapToIds(ChannelFunction::fromStrings(explode(',', $function)));
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
        $channels = $channels->filter(function (IODeviceChannel $channel) {
            return $this->isGranted(AccessIdSecurityVoter::PERMISSION_NAME, $channel);
        });
        $extraGroups = [];
        if (!ApiVersions::V2_4()->isRequestedEqualOrGreaterThan($request)) {
            $extraGroups = ['iodevice.location'];
        }
        return $this->serializedView($channels->getValues(), $request, $extraGroups);
    }

    /**
     * @Security("channel.belongsToUser(user) and has_role('ROLE_CHANNELS_R') and is_granted('accessIdContains', channel)")
     */
    public function getChannelAction(Request $request, IODeviceChannel $channel) {
        if (ApiVersions::V2_2()->isRequestedEqualOrGreaterThan($request)) {
            if (ApiVersions::V2_4()->isRequestedEqualOrGreaterThan($request)) {
                $extraGroups = ['location.relationsCount', 'channel.relationsCount'];
            } else {
                $extraGroups = ['iodevice.location'];
            }
            return $this->serializedView($channel, $request, $extraGroups);
        } else {
            $enabled = false;
            $connected = false;
            if ($channel->getIoDevice()->getEnabled()) {
                $enabled = true;
                $connected = $this->suplaServer->isDeviceConnected($channel->getIoDevice());
            }
            $result = array_merge(['connected' => $connected, 'enabled' => $enabled], $this->channelStateGetter->getState($channel));
            return $this->handleView($this->view($result, Response::HTTP_OK));
        }
    }

    /**
     * @Security("channel.belongsToUser(user) and has_role('ROLE_CHANNELS_RW') and is_granted('accessIdContains', channel)")
     * @UnavailableInMaintenance
     */
    public function putChannelAction(Request $request, IODeviceChannel $channel, IODeviceChannel $updatedChannel) {
        if (ApiVersions::V2_2()->isRequestedEqualOrGreaterThan($request)) {
            $functionHasBeenChanged = $channel->getFunction() != $updatedChannel->getFunction();
            if ($functionHasBeenChanged) {
                Assertion::inArray(
                    $updatedChannel->getFunction()->getId(),
                    array_merge([ChannelFunction::NONE], EntityUtils::mapToIds(ChannelFunction::forChannel($channel))),
                    'Invalid function for channel.' // i18n
                );
                $hasRelations = count($channel->getSchedules()) || count($channel->getChannelGroups()) || count($channel->getDirectLinks());
                if ($hasRelations && !$request->get('confirm')) {
                    return $this->view([
                        'schedules' => $channel->getSchedules(),
                        'groups' => $channel->getChannelGroups(),
                        'directLinks' => $channel->getDirectLinks(),
                    ], Response::HTTP_CONFLICT);
                }
                $cannotChangeMsg = 'Cannot change the channel function right now.'; // i18n
                Assertion::true($this->suplaServer->userAction('BEFORE-CHANNEL-FUNCTION-CHANGE', $channel->getId()), $cannotChangeMsg);
                $channel->setUserIcon(null);
                $channel->setAltIcon(0);
            }
            if ($updatedChannel->hasInheritedLocation()) {
                $channel->setLocation(null);
            } else {
                $channel->setLocation($updatedChannel->getLocation());
            }
            $channel->setCaption($updatedChannel->getCaption());
            $channel->setHidden($updatedChannel->getHidden());
            $this->channelParamsUpdater->updateChannelParams($channel, $updatedChannel);
            /** @var IODeviceChannel $channel */
            $channel = $this->transactional(function (EntityManagerInterface $em) use (
                $updatedChannel,
                $functionHasBeenChanged,
                $request,
                $channel
            ) {
                $em->persist($channel);
                if ($functionHasBeenChanged) {
                    $channel->setFunction($updatedChannel->getFunction());
                    $this->channelParamsUpdater->updateChannelParams($channel, new IODeviceChannel());
                    $this->channelParamsUpdater->updateChannelParams($channel, $updatedChannel);
                    foreach ($channel->getSchedules() as $schedule) {
                        $this->scheduleManager->delete($schedule);
                    }
                    foreach ($channel->getDirectLinks() as $directLink) {
                        $em->remove($directLink);
                    }
                    $channel->removeFromAllChannelGroups($em);
                }
                $channel->setAltIcon($updatedChannel->getAltIcon());
                $channel->setUserIcon($updatedChannel->getUserIcon());
                $em->persist($channel);
                return $channel;
            });
            $this->suplaServer->onDeviceSettingsChanged($channel->getIoDevice());
            $this->suplaServer->reconnect();
            return $this->getChannelAction($request, $channel->clearRelationsCount());
        } else {
            $data = json_decode($request->getContent(), true);
            $this->channelActionExecutor->executeAction($channel, ChannelFunctionAction::SET_RGBW_PARAMETERS(), $data);
            return $this->handleView($this->view(null, Response::HTTP_OK));
        }
    }

    /**
     * @Security("channel.belongsToUser(user) and has_role('ROLE_CHANNELS_EA') and is_granted('accessIdContains', channel)")
     */
    public function patchChannelAction(Request $request, IODeviceChannel $channel) {
        $params = json_decode($request->getContent(), true);
        Assertion::keyExists($params, 'action', 'Missing action.');
        $action = ChannelFunctionAction::fromString($params['action']);
        unset($params['action']);
        $this->channelActionExecutor->executeAction($channel, $action, $params);
        $status = ApiVersions::V2_2()->isRequestedEqualOrGreaterThan($request) ? Response::HTTP_NO_CONTENT : Response::HTTP_OK;
        $status = ApiVersions::V2_3()->isRequestedEqualOrGreaterThan($request) ? Response::HTTP_ACCEPTED : $status;
        return $this->handleView($this->view(null, $status));
    }

    /**
     * @Security("ioDevice.belongsToUser(user) and has_role('ROLE_CHANNELS_R') and is_granted('accessIdContains', ioDevice)")
     */
    public function getIodeviceChannelsAction(Request $request, IODevice $ioDevice) {
        return $this->serializedView($ioDevice->getChannels(), $request);
    }
}
